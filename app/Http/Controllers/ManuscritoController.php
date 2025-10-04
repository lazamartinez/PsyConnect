<?php

namespace App\Http\Controllers;

use App\Models\Manuscrito;
use App\Models\IndiceEstadoAnimico;
use App\Services\OCRService;
use App\Services\AnalisisEmocionalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ManuscritoController extends Controller
{
    protected OCRService $ocrService;
    protected AnalisisEmocionalService $analisisService;

    public function __construct(OCRService $ocrService, AnalisisEmocionalService $analisisService)
    {
        $this->ocrService = $ocrService;
        $this->analisisService = $analisisService;
    }

    public function index()
    {
        $paciente = Auth::user()->paciente;
        $manuscritos = $paciente->manuscritos()
            ->with('indiceEstadoAnimico')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manuscritos.index', compact('manuscritos'));
    }

    public function create()
    {
        return view('manuscritos.create');
    }

    public function store(Request $request)
    {
        Log::info('=== INICIANDO UPLOAD DE MANUSCRITO ===');

        $request->validate([
            'imagen_manuscrito' => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        try {
            $paciente = Auth::user()->paciente;
            $rutaImagen = $request->file('imagen_manuscrito')->store('manuscritos', 'public');

            $manuscrito = Manuscrito::create([
                'paciente_id' => $paciente->id_paciente,
                'imagen_original' => $rutaImagen,
                'fecha_captura' => now(),
                'estado_procesamiento' => 'procesando',
            ]);

            // Procesar OCR
            $resultadoOCR = $this->ocrService->procesarImagen($rutaImagen);

            // Analizar texto y calcular IEA
            $analisisEmocional = $this->analisisService->analizarTextoYCalcularIEA(
                $resultadoOCR['texto'],
                $paciente->id_paciente,
                $manuscrito->id_manuscrito
            );

            // Crear registro IndiceEstadoAnimico con floats
            $iea = IndiceEstadoAnimico::create([
                'paciente_id' => $paciente->id_paciente,
                'manuscrito_id' => $manuscrito->id_manuscrito,
                'valor_numerico' => floatval($analisisEmocional['valor_numerico']),
                'categoria_emotional' => $analisisEmocional['categoria'] ?? 'neutral',
                'confiabilidad_analisis' => floatval($analisisEmocional['confiabilidad'] ?? 0),
                'emocion_principal' => $analisisEmocional['emocion_principal'] ?? 'neutral',
                'intensidad_principal' => floatval($analisisEmocional['intensidad_principal'] ?? 0),
                'resumen_analisis' => $analisisEmocional['resumen'] ?? 'Análisis no disponible',
                'fecha_calculo' => now(),
            ]);

            $manuscrito->update([
                'texto_digitalizado' => $resultadoOCR['texto'],
                'confianza_ocr' => $resultadoOCR['confianza'],
                'estado_procesamiento' => 'procesado',
                'fecha_procesamiento' => now(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Manuscrito procesado exitosamente con OCR real',
                    'manuscrito' => $manuscrito,
                    'iea' => $iea,
                    'ocr_info' => [
                        'texto_extracto' => substr($resultadoOCR['texto'], 0, 100) . '...',
                        'confianza' => $resultadoOCR['confianza'],
                        'longitud' => $resultadoOCR['longitud_texto'],
                        'procesado_con' => $resultadoOCR['procesado_con']
                    ],
                    'redirect_url' => route('manuscritos.show', $manuscrito)
                ]);
            }

            return redirect()->route('manuscritos.show', $manuscrito)
                ->with('exito', 'Manuscrito procesado exitosamente. IEA calculado: ' . $iea->valor_numerico)
                ->with('ocr_info', $resultadoOCR);

        } catch (\Exception $e) {
            Log::error('ERROR CRÍTICO procesando manuscrito: ' . $e->getMessage());
            if (isset($rutaImagen) && Storage::disk('public')->exists($rutaImagen)) {
                Storage::disk('public')->delete($rutaImagen);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar el manuscrito: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Error al procesar el manuscrito: ' . $e->getMessage());
        }
    }

    public function show(Manuscrito $manuscrito)
    {
        if ($manuscrito->paciente_id !== Auth::user()->paciente->id_paciente) {
            abort(403);
        }

        $manuscrito->load('indiceEstadoAnimico');
        return view('manuscritos.show', compact('manuscrito'));
    }

    public function destroy(Manuscrito $manuscrito)
    {
        if ($manuscrito->paciente_id !== Auth::user()->paciente->id_paciente) {
            abort(403);
        }

        try {
            Storage::disk('public')->delete($manuscrito->imagen_original);
            if ($manuscrito->imagen_procesada) {
                Storage::disk('public')->delete($manuscrito->imagen_procesada);
            }
            $manuscrito->delete();

            return redirect()->route('manuscritos.index')
                ->with('exito', 'Manuscrito eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el manuscrito: ' . $e->getMessage());
        }
    }
}
