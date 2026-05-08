<?php

namespace App\Services\Sat;

use App\Models\Customer;
use App\Models\SatCfdi;
use App\Models\SatCfdiConcepto;
use App\Models\SatCfdiPago;
use App\Models\SatDownloadRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpCfdi\Credentials\Credential;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\Fiel;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\FielRequestBuilder\FielRequestBuilder;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\WebClient\GuzzleWebClient;
use PhpCfdi\SatWsDescargaMasiva\Service;
use PhpCfdi\SatWsDescargaMasiva\PackageReader\CfdiPackageReader;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;

class SatDescargaMasivaService
{
    /**
     * Construye el Service del SAT usando la FIEL del customer
     */
 private function buildService(Customer $customer): Service
{
    $cerPath  = Storage::path($customer->certificate_path);
    $keyPath  = Storage::path($customer->private_key_path);
    $password = $customer->fiel_password;

    Log::info('SAT buildService', [
        'cer_exists' => file_exists($cerPath),
        'key_exists' => file_exists($keyPath),
        'password_length' => strlen($password),
    ]);

    $fiel = Fiel::create(
        file_get_contents($cerPath),
        file_get_contents($keyPath),
        $password
    );

    if (! $fiel->isValid()) {
        throw new \Exception('FIEL inválida o vencida');
    }

    $webClient  = new GuzzleWebClient();
    $builder    = new FielRequestBuilder($fiel);

    return new Service($builder, $webClient);
}  

    /**
     * Paso 1 — Enviar solicitud al SAT
     */
    public function query(SatDownloadRequest $downloadRequest): void
{
    try {
        $customer = $downloadRequest->customer;
        $service  = $this->buildService($customer);

        $downloadRequest->update(['estado' => 'querying']);

        $downloadType = $downloadRequest->tipo_descarga === 'emitidas'
            ? DownloadType::issued()
            : DownloadType::received();

        $requestType = $downloadRequest->tipo_solicitud === 'metadata'
            ? RequestType::metadata()
            : RequestType::xml();

        $parameters = QueryParameters::create()
            ->withPeriod(DateTimePeriod::createFromValues(
                $downloadRequest->fecha_inicio->format('Y-m-d\TH:i:s'),
                $downloadRequest->fecha_fin->format('Y-m-d\TH:i:s')
            ))
            ->withDownloadType($downloadType)
            ->withRequestType($requestType)
            ->withDocumentStatus(DocumentStatus::active());

        $errors = $parameters->validate();

        if ([] !== $errors) {
            throw new \Exception('Errores de consulta SAT: ' . implode(' | ', $errors));
        }

        $result = $service->query($parameters);

        if (! $result->getStatus()->isAccepted()) {
            $downloadRequest->update([
                'estado'        => 'failed',
                'error_message' => 'SAT no aceptó la solicitud: ' . $result->getStatus()->getMessage(),
            ]);
            return;
        }

        $downloadRequest->update([
            'request_id_sat' => $result->getRequestId(),
            'estado'         => 'verifying',
        ]);

    } catch (\Throwable $e) {
        Log::error('SatDescargaMasiva::query error', [
            'download_request_id' => $downloadRequest->id,
            'tipo_descarga'       => $downloadRequest->tipo_descarga,
            'tipo_solicitud'      => $downloadRequest->tipo_solicitud,
            'fecha_inicio'        => $downloadRequest->fecha_inicio?->format('Y-m-d H:i:s'),
            'fecha_fin'           => $downloadRequest->fecha_fin?->format('Y-m-d H:i:s'),
            'error'               => $e->getMessage(),
        ]);

        $downloadRequest->update([
            'estado'        => 'failed',
            'error_message' => $e->getMessage(),
        ]);
    }
}
    /**
     * Paso 2 — Verificar si el SAT ya tiene listos los paquetes
     */
    public function verify(SatDownloadRequest $downloadRequest): bool
{
    try {
        $customer = $downloadRequest->customer;
        $service  = $this->buildService($customer);

        $result = $service->verify($downloadRequest->request_id_sat);

        if (! $result->getStatus()->isAccepted()) {
            $downloadRequest->update([
                'estado'        => 'failed',
                'error_message' => 'Error en verificación: ' . $result->getStatus()->getMessage(),
            ]);
            return false;
        }

        if (! $result->getCodeRequest()->isAccepted()) {
            $downloadRequest->update([
                'estado'        => 'failed',
                'error_message' => 'Solicitud rechazada: ' . $result->getCodeRequest()->getMessage(),
            ]);
            return false;
        }

        $statusRequest = $result->getStatusRequest();

        if ($statusRequest->isExpired() || $statusRequest->isFailure() || $statusRequest->isRejected()) {
            $downloadRequest->update([
                'estado'        => 'failed',
                'error_message' => 'La solicitud no se puede completar: ' . $statusRequest->getMessage(),
            ]);
            return false;
        }

        if ($statusRequest->isInProgress() || $statusRequest->isAccepted()) {
            // Aún no está lista, mantener en verifying
            return false;
        }

        if ($statusRequest->isFinished()) {
            $downloadRequest->update([
                'packages_ids' => $result->getPackagesIds(),
                'total_xml'    => $result->getNumberCfdis(),
                'estado'       => 'downloading',
            ]);
            return true;
        }

        return false;

    } catch (\Throwable $e) {
        Log::error('SatDescargaMasiva::verify error', ['error' => $e->getMessage()]);
        $downloadRequest->update([
            'estado'        => 'failed',
            'error_message' => $e->getMessage(),
        ]);
        return false;
    }
}

    /**
     * Paso 3 — Descargar paquetes y procesar XMLs
     */
    public function download(SatDownloadRequest $downloadRequest): void
    {
        try {
            $customer    = $downloadRequest->customer;
            $service     = $this->buildService($customer);
            $packagesIds = $downloadRequest->packages_ids ?? [];

            foreach ($packagesIds as $packageId) {
                $result = $service->download($packageId);

                if (! $result->getStatus()->isAccepted()) {
                    Log::warning('Paquete no descargado', [
                        'package_id' => $packageId,
                        'mensaje'    => $result->getStatus()->getMessage(),
                    ]);
                    continue;
                }

                // Guardar ZIP en storage
                $zipPath = "tenants/{$customer->tenant_id}/customers/{$customer->id}/cfdis/{$packageId}.zip";
                Storage::put($zipPath, $result->getPackageContent());

                // Procesar XMLs dentro del ZIP
                $this->processPackage($downloadRequest, $packageId, $result->getPackageContent());
            }

            $downloadRequest->update([
                'estado'       => 'completed',
                'completed_at' => now(),
            ]);

        } catch (\Throwable $e) {
            Log::error('SatDescargaMasiva::download error', ['error' => $e->getMessage()]);
            $downloadRequest->update([
                'estado'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Procesa el contenido de un paquete ZIP y persiste cada CFDI
     */

private function processPackage(
    SatDownloadRequest $downloadRequest,
    string $packageId,
    string $zipContent
): void {

    $customer = $downloadRequest->customer;

    $tmpFile = tempnam(sys_get_temp_dir(), 'sat_') . '.zip';

    file_put_contents($tmpFile, $zipContent);

    try {

        $reader = \PhpCfdi\SatWsDescargaMasiva\PackageReader\CfdiPackageReader::createFromFile($tmpFile);
        Log::info('CFDIS encontrados en ZIP', [
    'count' => count(iterator_to_array($reader->cfdis())),
]);

        foreach ($reader->cfdis() as $uuid => $xmlContent) {

        Log::info('Procesando CFDI', [
    'uuid' => $uuid,
]);
            try {
                

                $xml = new \SimpleXMLElement($xmlContent);

                $comprobanteAttrs = $xml->attributes();

                $namespaces = $xml->getNamespaces(true);

                $cfdiNs = $namespaces['cfdi'] ?? null;

                $emisorNode = null;
                $receptorNode = null;

                if ($cfdiNs) {

                    $children = $xml->children($cfdiNs);

                    $emisorNode = $children->Emisor ?? null;
                    $receptorNode = $children->Receptor ?? null;

                } else {

                    $emisorNode = $xml->Emisor ?? null;
                    $receptorNode = $xml->Receptor ?? null;
                }

                $emisorAttrs = $emisorNode ? $emisorNode->attributes() : null;
                $receptorAttrs = $receptorNode ? $receptorNode->attributes() : null;

                // Guardar XML
                $xmlPath = "tenants/{$customer->tenant_id}/customers/{$customer->id}/cfdis/{$uuid}.xml";

                Storage::put($xmlPath, $xmlContent);

                // CFDI
                $cfdi = SatCfdi::updateOrCreate(
                    [
                        'uuid' => $uuid,
                    ],
                    [
                        'sat_download_request_id' => $downloadRequest->id,
                        'customer_id' => $customer->id,

                        'serie' => $this->xmlAttr($comprobanteAttrs, ['Serie', 'serie']),
                        'folio' => $this->xmlAttr($comprobanteAttrs, ['Folio', 'folio']),

                        'rfc_emisor' => $this->xmlAttr($emisorAttrs, ['Rfc', 'RFC', 'rfc']),
                        'razon_social_emisor' => $this->xmlAttr($emisorAttrs, ['Nombre', 'nombre']),
                        'regimen_fiscal_emisor' => $this->xmlAttr($emisorAttrs, ['RegimenFiscal', 'regimenfiscal']),

                        'rfc_receptor' => $this->xmlAttr($receptorAttrs, ['Rfc', 'RFC', 'rfc']),
                        'razon_social_receptor' => $this->xmlAttr($receptorAttrs, ['Nombre', 'nombre']),
                        'regimen_fiscal_receptor' => $this->xmlAttr($receptorAttrs, ['RegimenFiscalReceptor', 'regimenfiscalreceptor']),
                        'uso_cfdi' => $this->xmlAttr($receptorAttrs, ['UsoCFDI', 'Usocfdi', 'usocfdi']),
                        'domicilio_fiscal_receptor' => $this->xmlAttr($receptorAttrs, ['DomicilioFiscalReceptor', 'domiciliofiscalreceptor']),

                        'fecha_emision' => $this->xmlAttr($comprobanteAttrs, ['Fecha', 'fecha']),
                        'tipo_comprobante' => $this->xmlAttr($comprobanteAttrs, ['TipoDeComprobante', 'tipodecomprobante']),
                        'tipo_descarga' => $downloadRequest->tipo_descarga,

                        'metodo_pago' => $this->xmlAttr($comprobanteAttrs, ['MetodoPago', 'metodopago']),
                        'forma_pago' => $this->xmlAttr($comprobanteAttrs, ['FormaPago', 'formapago']),

                        'moneda' => $this->xmlAttr($comprobanteAttrs, ['Moneda', 'moneda']),
                        'tipo_cambio' => $this->xmlAttr($comprobanteAttrs, ['TipoCambio', 'tipocambio']),

                        'subtotal' => $this->xmlAttr($comprobanteAttrs, ['SubTotal', 'Subtotal', 'subTotal']),
                        'descuento' => $this->xmlAttr($comprobanteAttrs, ['Descuento', 'descuento']),
                        'total' => $this->xmlAttr($comprobanteAttrs, ['Total', 'total']),

                        'estado_sat' => 'vigente',

                        'xml_path' => $xmlPath,
                        'package_id' => $packageId,
                    ]
                );

                $this->processConceptos($cfdi, $xml);

            } catch (\Throwable $e) {

                Log::error('Error persistiendo CFDI XML', [
    'uuid' => $uuid,
    'error' => $e->getMessage(),
    'line' => $e->getLine(),
    'file' => $e->getFile(),
    'trace' => $e->getTraceAsString(),
]);
            }
        }

    } catch (\Throwable $e) {

        Log::error('Error leyendo paquete SAT', [
            'package_id' => $packageId,
            'error' => $e->getMessage(),
        ]);

    } finally {

    if (isset($reader)) {
        unset($reader);
    }

    gc_collect_cycles();

    usleep(300000);

    if (file_exists($tmpFile)) {

        try {

            //unlink($tmpFile);

        } catch (\Throwable $e) {

            Log::warning('No se pudo eliminar tmp SAT ZIP', [
                'tmpFile' => $tmpFile,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
}



    /**
     * Parsea un XML y persiste el CFDI + conceptos + pagos
     */
    private function processCfdiXml(
    SatDownloadRequest $downloadRequest,
    string $packageId,
    string $filename,
    string $xmlContent
): void {
    try {

        $customer = $downloadRequest->customer;

        $xml = new \SimpleXMLElement($xmlContent);

        $xml->registerXPathNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');
        $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
        $xml->registerXPathNamespace('pago20', 'http://www.sat.gob.mx/Pagos20');

        // =========================
        // CFDI ATTRS
        // =========================
        $comprobanteAttrs = $xml->attributes();

        $emisor   = $xml->{'Emisor'};
        $receptor = $xml->{'Receptor'};

        // =========================
        // UUID DEL TIMBRE
        // =========================
        $tfd = $xml->xpath('//*[local-name()="TimbreFiscalDigital"]');

        $uuid = null;

        if (! empty($tfd)) {
            $tfdAttrs = $tfd[0]->attributes();
            $uuid = (string) ($tfdAttrs['UUID'] ?? null);
        }

        if (! $uuid) {

            Log::warning('CFDI sin UUID', [
                'filename' => $filename,
            ]);

            return;
        }

        // =========================
        // GUARDAR XML
        // =========================
        $xmlPath = "tenants/{$customer->tenant_id}/customers/{$customer->id}/cfdis/{$uuid}.xml";

        Storage::put($xmlPath, $xmlContent);

        // =========================
        // CREAR / ACTUALIZAR CFDI
        // =========================
        $cfdi = SatCfdi::updateOrCreate(
            [
                'uuid' => $uuid,
            ],
            [
                'sat_download_request_id'   => $downloadRequest->id,
                'customer_id'               => $customer->id,

                'serie'                     => (string) ($comprobanteAttrs['Serie'] ?? null),
                'folio'                     => (string) ($comprobanteAttrs['Folio'] ?? null),

                'rfc_emisor'                => (string) ($emisor->attributes()['Rfc'] ?? null),
                'razon_social_emisor'       => (string) ($emisor->attributes()['Nombre'] ?? null),
                'regimen_fiscal_emisor'     => (string) ($emisor->attributes()['RegimenFiscal'] ?? null),

                'rfc_receptor'              => (string) ($receptor->attributes()['Rfc'] ?? null),
                'razon_social_receptor'     => (string) ($receptor->attributes()['Nombre'] ?? null),
                'regimen_fiscal_receptor'   => (string) ($receptor->attributes()['RegimenFiscalReceptor'] ?? null),
                'uso_cfdi'                  => (string) ($receptor->attributes()['UsoCFDI'] ?? null),
                'domicilio_fiscal_receptor' => (string) ($receptor->attributes()['DomicilioFiscalReceptor'] ?? null),

                'fecha_emision'             => (string) ($comprobanteAttrs['Fecha'] ?? null),
                'tipo_comprobante'          => (string) ($comprobanteAttrs['TipoDeComprobante'] ?? null),

                'tipo_descarga'             => $downloadRequest->tipo_descarga,

                'metodo_pago'               => (string) ($comprobanteAttrs['MetodoPago'] ?? null),
                'forma_pago'                => (string) ($comprobanteAttrs['FormaPago'] ?? null),
                'condiciones_pago'          => (string) ($comprobanteAttrs['CondicionesDePago'] ?? null),

                'moneda'                    => (string) ($comprobanteAttrs['Moneda'] ?? 'MXN'),
                'tipo_cambio'               => (string) ($comprobanteAttrs['TipoCambio'] ?? null),

                'subtotal'                  => (string) ($comprobanteAttrs['SubTotal'] ?? null),
                'descuento'                 => (string) ($comprobanteAttrs['Descuento'] ?? null),
                'total'                     => (string) ($comprobanteAttrs['Total'] ?? null),

                'estado_sat'                => 'vigente',

                'xml_path'                  => $xmlPath,
                'package_id'                => $packageId,
            ]
        );

        // =========================
        // CONCEPTOS
        // =========================
        $this->processConceptos($cfdi, $xml);

        // =========================
        // PAGOS
        // =========================
        $this->processPagos($cfdi, $xml);

        Log::info('CFDI persistido correctamente', [
            'uuid' => $uuid,
        ]);

    } catch (\Throwable $e) {

        Log::error('Error procesando CFDI XML', [
            'file'  => $filename,
            'error' => $e->getMessage(),
            'line'  => $e->getLine(),
        ]);
    }
}

    /**
     * Persiste los conceptos del CFDI
     */
private function processConceptos(SatCfdi $cfdi, \SimpleXMLElement $xml): void
{
    $conceptoNodes = $xml->xpath('//*[local-name()="Conceptos"]/*[local-name()="Concepto"]');

    if (empty($conceptoNodes)) {

        Log::warning('CFDI sin conceptos', [
            'cfdi_id' => $cfdi->id,
            'uuid' => $cfdi->uuid,
        ]);

        return;
    }

    foreach ($conceptoNodes as $conceptoNode) {

        try {

            $attrs = $conceptoNode->attributes();

            $cfdi->conceptos()->create([

                'clave_prod_serv' => $this->xmlAttr($attrs, ['ClaveProdServ', 'claveprodserv']),

                'no_identificacion' => $this->xmlAttr($attrs, ['NoIdentificacion', 'noidentificacion']),

                'cantidad' => $this->xmlAttr($attrs, ['Cantidad', 'cantidad']),

                'clave_unidad' => $this->xmlAttr($attrs, ['ClaveUnidad', 'claveunidad']),

                'unidad' => $this->xmlAttr($attrs, ['Unidad', 'unidad']),

                'descripcion' => $this->xmlAttr($attrs, ['Descripcion', 'descripcion']),

                'valor_unitario' => $this->xmlAttr($attrs, ['ValorUnitario', 'valorunitario']),

                'importe' => $this->xmlAttr($attrs, ['Importe', 'importe']),

                'descuento' => $this->xmlAttr($attrs, ['Descuento', 'descuento']),

                'objeto_impuesto' => $this->xmlAttr($attrs, ['ObjetoImp', 'objetoimp']),
            ]);

        } catch (\Throwable $e) {

            Log::error('Error persistiendo concepto CFDI', [
                'cfdi_id' => $cfdi->id,
                'uuid' => $cfdi->uuid,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
        }
    }
}

    /**
     * Persiste el complemento de pagos (Pago 2.0)
     */
    private function processPagos(SatCfdi $cfdi, \SimpleXMLElement $xml): void
    {
        $xml->registerXPathNamespace('pago20', 'http://www.sat.gob.mx/Pagos20');
        $pagos = $xml->xpath('//pago20:Pago');

        if (empty($pagos)) return;

        $cfdi->update(['tiene_complemento_pago' => true]);

        foreach ($pagos as $pago) {
            $attrs = $pago->attributes();
            $docRel = $pago->{'DoctoRelacionado'} ?? null;
            $docAttrs = $docRel ? $docRel->attributes() : null;

            $cfdi->pagos()->create([
                'cfdi_uuid'          => $cfdi->uuid,
                'fecha_pago'         => (string) ($attrs['FechaPago'] ?? null),
                'forma_pago_p'       => (string) ($attrs['FormaDePagoP'] ?? null),
                'moneda_p'           => (string) ($attrs['MonedaP'] ?? 'MXN'),
                'tipo_cambio_p'      => (string) ($attrs['TipoCambioP'] ?? null),
                'monto'              => (string) ($attrs['Monto'] ?? 0),
                'num_operacion'      => (string) ($attrs['NumOperacion'] ?? null),
                'rfc_emisor_cta_ord' => (string) ($attrs['RfcEmisorCtaOrd'] ?? null),
                'nom_banco_ord_ext'  => (string) ($attrs['NomBancoOrdExt'] ?? null),
                'cta_ordenante'      => (string) ($attrs['CtaOrdenante'] ?? null),
                'rfc_emisor_cta_ben' => (string) ($attrs['RfcEmisorCtaBen'] ?? null),
                'cta_beneficiario'   => (string) ($attrs['CtaBeneficiario'] ?? null),
                'id_documento'       => $docAttrs ? (string) ($docAttrs['IdDocumento'] ?? null) : null,
                'serie_dr'           => $docAttrs ? (string) ($docAttrs['Serie'] ?? null) : null,
                'folio_dr'           => $docAttrs ? (string) ($docAttrs['Folio'] ?? null) : null,
                'moneda_dr'          => $docAttrs ? (string) ($docAttrs['MonedaDR'] ?? null) : null,
                'tipo_cambio_dr'     => $docAttrs ? (string) ($docAttrs['TipoCambioDR'] ?? null) : null,
                'num_parcialidad'    => $docAttrs ? (string) ($docAttrs['NumParcialidad'] ?? null) : null,
                'imp_saldo_ant'      => $docAttrs ? (string) ($docAttrs['ImpSaldoAnt'] ?? null) : null,
                'imp_pagado'         => $docAttrs ? (string) ($docAttrs['ImpPagado'] ?? null) : null,
                'imp_saldo_insoluto' => $docAttrs ? (string) ($docAttrs['ImpSaldoInsoluto'] ?? null) : null,
            ]);
        }
    }

    /**
     * Extrae impuestos de un concepto como array
     */
    private function extractImpuestosConcepto(\SimpleXMLElement $concepto): ?array
    {
        $impuestos = $concepto->{'Impuestos'} ?? null;
        if (! $impuestos) return null;

        $result = [];

        foreach ($impuestos->{'Traslados'}->{'Traslado'} ?? [] as $traslado) {
            $result['traslados'][] = [
                'base'       => (string) $traslado->attributes()['Base'],
                'impuesto'   => (string) $traslado->attributes()['Impuesto'],
                'tipo_factor'=> (string) $traslado->attributes()['TipoFactor'],
                'tasa'       => (string) $traslado->attributes()['TasaOCuota'],
                'importe'    => (string) $traslado->attributes()['Importe'],
            ];
        }

        foreach ($impuestos->{'Retenciones'}->{'Retencion'} ?? [] as $retencion) {
            $result['retenciones'][] = [
                'base'       => (string) $retencion->attributes()['Base'],
                'impuesto'   => (string) $retencion->attributes()['Impuesto'],
                'tipo_factor'=> (string) $retencion->attributes()['TipoFactor'],
                'tasa'       => (string) $retencion->attributes()['TasaOCuota'],
                'importe'    => (string) $retencion->attributes()['Importe'],
            ];
        }

        return $result ?: null;
    }
    private function xmlAttr($attributes, array $keys): ?string
{
    if (! $attributes) {
        return null;
    }

    foreach ($keys as $key) {

        if (isset($attributes[$key])) {

            $value = trim((string) $attributes[$key]);

            return $value === ''
                ? null
                : $value;
        }
    }

    return null;
}
}