<?php

namespace App\Http\Controllers;

use App\Models\IndiceEstadoAnimico;
use App\Models\Paciente;
use App\Services\IEAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IEAController extends Controller
{
    protected $ieaService;

    public function __construct(IEAService $ieaService)
    {
        $this->ieaService = $ieaService;
    }

    public function calcularDesdeTexto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'texto' => 'required|string|min:50',
            'fuente_datos' => 'required|in:texto_directo,audio,otro'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $paciente = Paciente::findOrFail($request->paciente_id);
        $this->authorize('update', $paciente);

        try {
            $iea = $this->ieaService->calcularIEDesdeTexto(
                $paciente,
                $request->texto,
                $request->fuente_datos
            );

            return response()->json([
                'message' => 'Índice de Estado Anímico calculado exitosamente',
                'iea' => $iea
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al calcular el IEA: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerHistorial($pacienteId, Request $request)
    {
        $paciente = Paciente::findOrFail($pacienteId);
        $this->authorize('view', $paciente);

        $query = IndiceEstadoAnimico::where('paciente_id', $pacienteId)
            ->with('manuscrito')
            ->orderBy('fecha_calculo', 'desc');

        // Filtros
        if ($request->has('desde')) {
            $query->where('fecha_calculo', '>=', $request->desde);
        }

        if ($request->has('hasta')) {
            $query->where('fecha_calculo', '<=', $request->hasta);
        }

        if ($request->has('categoria')) {
            $query->where('categoria_emotional', $request->categoria);
        }

        $historial = $request->has('paginar') ? 
            $query->paginate(30) : 
            $query->get();

        // Estadísticas adicionales
        $estadisticas = $this->calcularEstadisticasIEA($pacienteId, $request);

        return response()->json([
            'historial' => $historial,
            'estadisticas' => $estadisticas
        ]);
    }

    public function tendencias($pacienteId, Request $request)
    {
        $paciente = Paciente::findOrFail($pacienteId);
        $this->authorize('view', $paciente);

        $periodo = $request->get('periodo', '7dias'); // 7dias, 30dias, 90dias

        $tendencias = $this->ieaService->calcularTendencias($pacienteId, $periodo);

        return response()->json([
            'tendencias' => $tendencias,
            'periodo' => $periodo
        ]);
    }

    public function alertasAutomaticas($pacienteId)
    {
        $paciente = Paciente::findOrFail($pacienteId);
        $this->authorize('view', $paciente);

        $alertas = $this->ieaService->evaluarAlertasAutomaticas($paciente);

        return response()->json([
            'alertas' => $alertas,
            'paciente' => $paciente->only(['id', 'nombre_completo'])
        ]);
    }

    public function recalcularIEA($ieaId)
    {
        $iea = IndiceEstadoAnimico::with('paciente')->findOrFail($ieaId);
        $this->authorize('update', $iea->paciente);

        try {
            $nuevoIEA = $this->ieaService->recalcularIEA($iea->id);

            return response()->json([
                'message' => 'IEA recalculado exitosamente',
                'iea' => $nuevoIEA
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al recalcular IEA: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calcularEstadisticasIEA($pacienteId, Request $request)
    {
        $query = IndiceEstadoAnimico::where('paciente_id', $pacienteId);

        if ($request->has('desde')) {
            $query->where('fecha_calculo', '>=', $request->desde);
        }

        if ($request->has('hasta')) {
            $query->where('fecha_calculo', '<=', $request->hasta);
        }

        return [
            'promedio' => $query->avg('valor_numerico'),
            'maximo' => $query->max('valor_numerico'),
            'minimo' => $query->min('valor_numerico'),
            'total_registros' => $query->count(),
            'distribucion_categorias' => $query->selectRaw('categoria_emotional, COUNT(*) as count')
                ->groupBy('categoria_emotional')
                ->get()
                ->pluck('count', 'categoria_emotional')
        ];
    }
}