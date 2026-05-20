<?php

namespace App\Services\Sat;

use App\Models\Customer;
use App\Models\SatCsfRequest;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Storage;
use PhpCfdi\CsfSatScraper\HttpClientFactory;
use PhpCfdi\CsfSatScraper\Scraper;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\BoxFacturaAIResolver;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\ConfigsReader;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Processor;
use Throwable;

class SatCsfService
{
    public function download(Customer $customer): SatCsfRequest
    {
        $request = SatCsfRequest::create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'rfc' => $customer->rfc,
            'estado' => 'downloading',
        ]);

        try {
            if (! $customer->rfc || ! $customer->ciec_password) {
                throw new \RuntimeException('El cliente no tiene RFC o contraseña CIEC configurada.');
            }

            $client = HttpClientFactory::create([
                'curl' => [
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
                RequestOptions::VERIFY => false,
            ]);

            $captchaSolver = $this->makeCaptchaSolver();

            $scraper = Scraper::create(
                $client,
                $captchaSolver,
                $customer->rfc,
                $customer->ciec_password
            );

            $pdfContent = $scraper->download();

            $path = "clientes/{$customer->id}/csf/csf-{$customer->rfc}-" . now()->format('Ymd_His') . ".pdf";

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

    private function makeCaptchaSolver(): BoxFacturaAIResolver
    {
        $configFile = storage_path('sat-captcha-ai-model/configs.yaml');

        $settings = (new ConfigsReader())
            ->settingsFromFile($configFile);

        $processor = Processor::createFromSettings($settings);

        return new BoxFacturaAIResolver($processor);
    }
}