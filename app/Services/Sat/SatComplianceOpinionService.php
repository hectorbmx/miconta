<?php

namespace App\Services\Sat;

use App\Models\Customer;
use App\Models\SatComplianceOpinionRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Storage;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\BoxFacturaAIResolver;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\ConfigsReader;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Processor;
use PhpCfdi\OpinionCumplimientoSatScraper\Scraper;
use Throwable;

class SatComplianceOpinionService
{
    public function download(Customer $customer): SatComplianceOpinionRequest
    {
        $request = SatComplianceOpinionRequest::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'rfc' => $customer->rfc,
            'estado' => 'downloading',
        ]);

        try {
            if (! $customer->rfc || ! $customer->ciec_password) {
                throw new \RuntimeException('El cliente no tiene RFC o contraseña CIEC configurada.');
            }

            $scraper = new Scraper(
                $this->makeHttpClient(),
                $this->makeCaptchaSolver(),
                $customer->rfc,
                $customer->ciec_password
            );

            $pdfContent = $scraper->download();
            $path = "clientes/{$customer->id}/opinion-cumplimiento/opinion-32d-{$customer->rfc}-" . now()->format('Ymd_His') . '.pdf';

            Storage::disk('local')->put($path, (string) $pdfContent);

            $request->update([
                'estado' => 'completed',
                'pdf_path' => $path,
                'downloaded_at' => now(),
                'error_message' => null,
            ]);

            return $request;
        } catch (Throwable $e) {
            $request->update([
                'estado' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return $request;
        }
    }

    private function makeHttpClient(): Client
    {
        return new Client([
            'cookies' => new CookieJar(),
            'curl' => [
                CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
            ],
            RequestOptions::VERIFY => false,
        ]);
    }

    private function makeCaptchaSolver(): BoxFacturaAIResolver
    {
        $settings = (new ConfigsReader())
            ->settingsFromFile(storage_path('sat-captcha-ai-model/configs.yaml'));

        return new BoxFacturaAIResolver(Processor::createFromSettings($settings));
    }
}
