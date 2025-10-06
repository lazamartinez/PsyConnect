<?php

namespace App\Http\Controllers;

use App\Services\MatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatchingVisualController extends Controller
{
    protected $matchingService;

    public function __construct(MatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    public function mostrarResultadoMatching(Request $request)
    {
        $paciente = Auth::user()->paciente;
        $triajeId = $request->triaje_id;

        // Obtener resultado del matching
        $resultado = $this->obtenerResultadoMatching($triajeId);

        return view('matching.resultado-visual', [
            'resultado' => $resultado,
            'paciente' => $paciente
        ]);
    }

    public function procesarTriajeVisual(Request $request)
    {
        $request->validate([
            'descripcion_sintomatologia' => 'required|string|min:50|max:2000'
        ]);

        $paciente = Auth::user()->paciente;

        try {
            // Procesar triaje
            $resultado = $this->matchingService->procesarTriajeCompleto(
                $paciente,
                $request->descripcion_sintomatologia
            );

            return response()->json([
                'success' => true,
                'match_encontrado' => $resultado['match_encontrado'],
                'resultado' => $resultado,
                'vista' => view('matching.partials.resultado-matching', compact('resultado'))->render()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el proceso: ' . $e->getMessage()
            ], 500);
        }
    }
}