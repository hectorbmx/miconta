<?php

namespace App\Console\Commands;

use App\Models\SatCfdi;
use App\Services\Sat\SatDescargaMasivaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ReprocessSatCfdisCommand extends Command
{
    protected $signature = 'sat:reprocess-cfdis
        {--customer= : Reprocesar solo CFDI de un customer_id}
        {--limit= : Limitar la cantidad de CFDI a reprocesar}
        {--only-missing : Reprocesar solo CFDI sin totales de impuestos guardados}';

    protected $description = 'Reprocesa XMLs SAT guardados para llenar impuestos de CFDI y conceptos';

    public function handle(SatDescargaMasivaService $satService): int
    {
        $query = SatCfdi::query()
            ->whereNotNull('xml_path')
            ->where('xml_path', '<>', '')
            ->orderBy('id');

        if ($customerId = $this->option('customer')) {
            $query->where('customer_id', $customerId);
        }

        if ($this->option('only-missing')) {
            $query->whereNull('total_impuestos_trasladados')
                ->whereNull('total_impuestos_retenidos');
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $cfdis = $query->get();

        if ($cfdis->isEmpty()) {
            $this->info('No hay CFDI para reprocesar.');

            return self::SUCCESS;
        }

        $processed = 0;
        $missingXml = 0;
        $failed = 0;

        $this->info("Reprocesando {$cfdis->count()} CFDI...");
        $bar = $this->output->createProgressBar($cfdis->count());
        $bar->start();

        foreach ($cfdis as $cfdi) {
            if (! Storage::exists($cfdi->xml_path)) {
                $missingXml++;
                $bar->advance();
                continue;
            }

            if ($satService->reprocessStoredCfdi($cfdi)) {
                $processed++;
            } else {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metrica', 'Total'],
            [
                ['CFDI seleccionados', $cfdis->count()],
                ['Reprocesados', $processed],
                ['XML no encontrado', $missingXml],
                ['Errores', $failed],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
