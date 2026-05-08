<?php


namespace App\Http\Controllers\Client\Sat;


use App\Http\Controllers\Controller;
use App\Services\Sat\CsfScraperService;
use Illuminate\Http\Request;

class CsfController extends Controller
{
    public function __construct(protected CsfScraperService $csfService) {}

    /**
     * Consulta por RFC + ID CIF
     */
    public function consultarPorRfc(Request $request)
    {
        $request->validate([
            'rfc'    => 'required|string',
            'id_cif' => 'required|string',
        ]);

        try {
            $datos = $this->csfService->obtenerPorRfcYCif(
                $request->rfc,
                $request->id_cif
            );

            return response()->json(['success' => true, 'data' => $datos]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Parseo de PDF subido
     */
    public function consultarDesdePdf(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:5120',
        ]);

        try {
            $path = $request->file('pdf')->store('csf_temp', 'local');
            $fullPath = storage_path("app/{$path}");

            $datos = $this->csfService->obtenerDesdePdf($fullPath);

            // Eliminar el PDF temporal
            \Storage::disk('local')->delete($path);

            return response()->json(['success' => true, 'data' => $datos]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}