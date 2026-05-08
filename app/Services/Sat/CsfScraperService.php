<?php

namespace App\Services\Sat;

use GuzzleHttp\Client;
use PhpCfdi\CsfScraper\Scraper;
use PhpCfdi\Rfc\Rfc;

class CsfScraperService
{
    protected Scraper $scraper;

    public function __construct()
    {
        $this->scraper = Scraper::create();
    }

    /**
     * Obtiene datos fiscales desde el SAT por RFC e ID CIF
     */
    public function obtenerPorRfcYCif(string $rfc, string $idCif): array
    {
        $rfcObj = Rfc::parse($rfc);
        $person = $this->scraper->obtainFromRfcAndCif(rfc: $rfcObj, idCIF: $idCif);

        return $person->toArray();
    }

    /**
     * Obtiene datos fiscales parseando un PDF local
     */
    public function obtenerDesdePdf(string $pdfPath): array
    {
        $person = $this->scraper->obtainFromPdfPath($pdfPath);

        return $person->toArray();
    }
}