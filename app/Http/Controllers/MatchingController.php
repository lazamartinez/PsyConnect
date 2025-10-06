<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Profesional;
use App\Services\MatchingService;
use App\Services\TriajeMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatchingController extends Controller
{
    protected $matchingService;
    protected $triajeService;

    public function __construct(MatchingService $matchingService, TriajeMatchingService $triajeService)
    {
        $this->matchingService = $matchingService;
        $this->triajeService = $triajeService;
    }

    public function procesarTriajeYMatching(Request $request)
    {
        Log::info("🎯 ========== INICIANDO PROCESO DE MATCHING ==========");

        $request->validate([
            'descripcion_sintomatologia' => 'required|string|min:50|max:1000',
            'clinica_id' => 'nullable|exists:clinicas,id_clinica'
        ]);

        $paciente = Auth::user()->paciente;
        $clinicaId = $request->clinica_id;

        Log::info("📋 Datos recibidos:", [
            'paciente_id' => $paciente->id,
            'clinica_id' => $clinicaId,
            'descripcion_longitud' => strlen($request->descripcion_sintomatologia),
            'descripcion_preview' => substr($request->descripcion_sintomatologia, 0, 100) . '...'
        ]);

        try {
            Log::info("🔄 Creando servicio de matching...");
            $this->matchingService = new MatchingService($clinicaId);

            Log::info("🔍 Procesando triaje completo...");
            $resultado = $this->matchingService->procesarTriajeCompleto(
                $paciente,
                $request->descripcion_sintomatologia
            );

            Log::info("✅ RESULTADO MATCHING:", [
                'match_encontrado' => $resultado['match_encontrado'] ?? false,
                'profesional_id' => $resultado['profesional']->id ?? 'null',
                'puntaje' => $resultado['puntaje_compatibilidad'] ?? 0,
                'especialidad' => $resultado['especialidad_recomendada'] ?? 'null'
            ]);

            return response()->json([
                'success' => true,
                'match_encontrado' => $resultado['match_encontrado'] ?? false,
                'profesional' => $resultado['profesional'] ?? null,
                'puntaje_compatibilidad' => $resultado['puntaje_compatibilidad'] ?? 0,
                'especialidad_recomendada' => $resultado['especialidad_recomendada'] ?? 'psicologo',
                'analisis_sintomas' => $resultado['analisis_sintomas'] ?? [],
                'configuracion_utilizada' => $resultado['triaje']->configuracion_utilizada ?? [],
                'redirect_url' => route('dashboard')
            ]);
        } catch (\Exception $e) {
            Log::error('❌ ERROR CRÍTICO en matching: ' . $e->getMessage());
            Log::error('📝 Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'match_encontrado' => false,
                'profesional' => null,
                'puntaje_compatibilidad' => 0,
                'analisis_sintomas' => []
            ], 500);
        }
    }

    private function guardarResultadoMatching(Paciente $paciente, array $resultadoMatching, string $descripcion)
    {
        if ($resultadoMatching['profesional_optimo']) {
            // Crear relación paciente-profesional
            $paciente->profesionales()->attach($resultadoMatching['profesional_optimo']->id, [
                'fecha_asignacion' => now(),
                'puntuacion_compatibilidad' => $resultadoMatching['puntaje'],
                'estado' => 'pendiente', // El paciente debe aceptar
                'motivo_asignacion' => 'Matching automático - Compatibilidad: ' . $resultadoMatching['puntaje'] . '%'
            ]);

            // Guardar detalles del matching
            if (DB::getSchemaBuilder()->hasTable('matching_logs')) {
                DB::table('matching_logs')->insert([
                    'paciente_id' => $paciente->id,
                    'profesional_id' => $resultadoMatching['profesional_optimo']->id,
                    'puntuacion_compatibilidad' => $resultadoMatching['puntaje'],
                    'descripcion_paciente' => $descripcion,
                    'resultados_comparacion' => json_encode($resultadoMatching['todos_los_resultados']),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    private function notificarProfesional(Profesional $profesional, Paciente $paciente, float $puntaje)
    {
        Log::info("Notificando al profesional {$profesional->id} sobre nuevo paciente potencial. Compatibilidad: {$puntaje}%");
    }

    private function notificarAceptacion(Profesional $profesional, Paciente $paciente)
    {
        Log::info("Paciente {$paciente->id} aceptó match con profesional {$profesional->id}");
    }

    public function aceptarMatch(Request $request, $profesionalId)
    {
        $paciente = Auth::user()->paciente;

        try {
            // Actualizar el estado del match a "activo"
            $paciente->profesionales()->updateExistingPivot($profesionalId, [
                'estado' => 'activo',
                'fecha_aceptacion' => now()
            ]);

            // Notificar al profesional
            $profesional = Profesional::find($profesionalId);
            $this->notificarAceptacion($profesional, $paciente);

            return response()->json([
                'success' => true,
                'message' => '¡Match aceptado exitosamente! Ahora estás conectado con el profesional.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar el match: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rechazarMatch(Request $request, $profesionalId)
    {
        $paciente = Auth::user()->paciente;

        try {
            // Eliminar la relación o marcarla como rechazada
            $paciente->profesionales()->updateExistingPivot($profesionalId, [
                'estado' => 'rechazado',
                'fecha_rechazo' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Match rechazado.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar el match: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerMatchesPendientes()
    {
        $user = Auth::user();

        if ($user->esPaciente()) {
            $paciente = $user->paciente;
            $matches = $paciente->profesionales()
                ->wherePivot('estado', 'pendiente')
                ->with('usuario')
                ->get();

            return response()->json(['matches' => $matches]);
        }

        if ($user->esProfesional()) {
            $profesional = $user->profesional;
            $matches = $profesional->pacientes()
                ->wherePivot('estado', 'pendiente')
                ->with('usuario')
                ->get();

            return response()->json(['matches' => $matches]);
        }

        return response()->json(['matches' => []]);
    }
}
