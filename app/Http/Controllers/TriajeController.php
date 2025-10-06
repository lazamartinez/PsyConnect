<?php

namespace App\Http\Controllers;

use App\Models\Manuscrito;
use App\Models\Paciente;
use App\Models\TriajeInicial;
use App\Services\TriajeMatchingService;
use App\Services\MatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TriajeController extends Controller
{
    protected $triajeService;
    protected $matchingService;

    public function __construct(TriajeMatchingService $triajeService, MatchingService $matchingService)
    {
        $this->triajeService = $triajeService;
        $this->matchingService = $matchingService;
    }

    public function mostrarFormularioTriaje()
    {
        $paciente = Auth::user()->paciente;
        return view('paciente.triaje-inicial', compact('paciente'));
    }

    public function procesarTriaje(Request $request)
    {
        $request->validate([
            'descripcion_sintomatologia' => 'required|string|min:50|max:1000'
        ]);

        $paciente = Auth::user()->paciente;

        try {
            // Procesar triaje y matching
            $resultado = $this->matchingService->procesarTriajeCompleto(
                $paciente, 
                $request->descripcion_sintomatologia
            );

            if ($resultado['match_encontrado']) {
                return redirect()->route('dashboard')
                    ->with('exito', '¡Triaje completado! Se encontró un profesional compatible: ' . 
                        $resultado['profesional']->especialidad_principal . 
                        ' con ' . $resultado['puntaje_compatibilidad'] . '% de compatibilidad')
                    ->with('triaje_info', $resultado);
            } else {
                return redirect()->route('dashboard')
                    ->with('info', 'Triaje completado. Estamos buscando el profesional más adecuado para ti.')
                    ->with('triaje_info', $resultado);
            }

        } catch (\Exception $e) {
            Log::error('Error en triaje: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error en el triaje: ' . $e->getMessage());
        }
    }

    public function integrarConManuscrito(Request $request, $manuscritoId)
    {
        // Integrar triaje con el análisis del manuscrito
        $manuscrito = Manuscrito::where('paciente_id', Auth::user()->paciente->id)
            ->findOrFail($manuscritoId);

        if ($manuscrito->texto_digitalizado) {
            $resultado = $this->matchingService->procesarTriajeCompleto(
                Auth::user()->paciente,
                $manuscrito->texto_digitalizado
            );

            return response()->json([
                'success' => true,
                'triaje' => $resultado,
                'redirect' => route('manuscritos.show', $manuscrito)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'El manuscrito no tiene texto digitalizado'
        ], 400);
    }
}