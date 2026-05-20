<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpCfdi\CsfSatScraper\Scraper;
use PhpCfdi\CsfSatScraper\HttpClientFactory;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\BoxFacturaAIResolver;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\ConfigsReader;
use PhpCfdi\ImageCaptchaResolver\BoxFacturaAI\Processor;
use GuzzleHttp\RequestOptions;

class TestCsfDownloadCommand extends Command
{
    protected $signature = 'sat:test-csf-download {rfc} {password}';

    protected $description = 'Prueba descarga de Constancia de Situación Fiscal SAT';

    public function handle(): int
    {
        $rfc = $this->argument('rfc');
        $password = $this->argument('password');

        $this->info('Creando cliente HTTP...');

        $client = HttpClientFactory::create([
            'curl' => [
                CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
            ],
            RequestOptions::VERIFY => false,
        ]);

        // $captchaSolver = new ConsoleResolver();

        // $captchaSolver = BoxFacturaAIResolver::create();
        // $captchaSolver = new BoxFacturaAIResolver();
        $configFile = storage_path('sat-captcha-ai-model/configs.yaml');

            $settings = (new ConfigsReader())
                ->settingsFromFile($configFile);

            $processor = Processor::createFromSettings($settings);

            $captchaSolver = new BoxFacturaAIResolver($processor);
        $this->info('Creando scraper CSF...');

        $scraper = Scraper::create(
            $client,
            $captchaSolver,
            $rfc,
            $password
        );

        $this->info('Descargando constancia...');

        $pdfContent = $scraper->download();

        $path = storage_path('app/private/csf-test-' . $rfc . '.pdf');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, (string) $pdfContent);

        $this->info('PDF guardado en:');
        $this->line($path);

        return self::SUCCESS;
    }
}