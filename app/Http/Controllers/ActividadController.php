<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Paciente;
use App\Models\ValidacionActividad;
use App\Models\Auditoria; // Agregar esta línea
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ActividadController extends Controller // Asegurar que extiende Controller
{
    public function index(Request $request)
    {
        $query = Actividad::with(['categoria', 'pacientes']);

        // Filtros
        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->has('dificultad')) {
            $query->where('dificultad', $request->dificultad);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('busqueda')) {
            $busqueda = $request->busqueda;
            $query->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$busqueda}%");
        }

        $actividades = $query->orderBy('nombre')->paginate(20);

        return response()->json([
            'actividades' => $actividades
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'tipo' => 'required|in:ejercicio,lectura,reflexion,practica,otro',
            'duracion_estimada' => 'required|integer|min:1',
            'frecuencia_recomendada' => 'required|string',
            'dificultad' => 'required|in:principiante,intermedio,avanzado',
            'categoria_id' => 'required|exists:categorias,id',
            'puntos_asignados' => 'required|integer|min:0',
            'objetivos_especificos' => 'required|array',
            'instrucciones_paso_paso' => 'required|array',
            'recursos_necesarios' => 'nullable|array',
            'materiales_adjuntos' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Solo profesionales y administradores pueden crear actividades
        if (!$request->user()->esProfesional() && !$request->user()->esAdministrador()) {
            return response()->json([
                'message' => 'No autorizado para crear actividades'
            ], 403);
        }

        $actividad = Actividad::create($request->all());

        return response()->json([
            'message' => 'Actividad creada exitosamente',
            'actividad' => $actividad
        ], 201);
    }

    public function asignarPaciente(Request $request, $actividadId)
    {
        $actividad = Actividad::findOrFail($actividadId);
        
        // Verificar permisos manualmente en lugar de usar authorize
        $usuario = $request->user();
        if (!$usuario->esProfesional() && !$usuario->esAdministrador()) {
            return response()->json([
                'message' => 'No autorizado para asignar actividades'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_vencimiento' => 'required|date|after:today',
            'comentarios' => 'nullable|string',
            'requiere_validacion' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $paciente = Paciente::findOrFail($request->paciente_id);

        DB::beginTransaction();

        try {
            // Asignar actividad al paciente
            $actividad->pacientes()->attach($paciente->id, [
                'fecha_asignacion' => now(),
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'estado' => 'asignada',
                'comentarios' => $request->comentarios,
                'requiere_validacion' => $request->requiere_validacion ?? false
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Actividad asignada exitosamente al paciente',
                'asignacion' => [
                    'actividad' => $actividad->nombre,
                    'paciente' => $paciente->usuario->nombre . ' ' . $paciente->usuario->apellido,
                    'fecha_vencimiento' => $request->fecha_vencimiento
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al asignar actividad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function marcarCompletada(Request $request, $actividadId)
    {
        $actividad = Actividad::findOrFail($actividadId);

        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'comentarios' => 'nullable|string',
            'evidencia' => 'nullable|array',
            'dificultad_encontrada' => 'nullable|integer|min:1|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $paciente = Paciente::findOrFail($request->paciente_id);
        
        // Verificar que el paciente está marcando su propia actividad
        if ($request->user()->id !== $paciente->usuario_id) {
            return response()->json([
                'message' => 'No autorizado para marcar esta actividad como completada'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Actualizar estado de la actividad
            $actividad->pacientes()->updateExistingPivot($paciente->id, [
                'estado' => 'completada',
                'fecha_completado' => now(),
                'comentarios' => $request->comentarios,
                'evidencia' => $request->evidencia,
                'dificultad_encontrada' => $request->dificultad_encontrada,
                'puntos_obtenidos' => $actividad->puntos_asignados
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Actividad marcada como completada exitosamente',
                'actividad' => $actividad->only(['id', 'nombre', 'puntos_asignados'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al marcar actividad como completada: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validarActividad(Request $request, $validacionId)
    {
        $validacion = ValidacionActividad::with(['actividad', 'testigo', 'actividad.pacientes'])
            ->findOrFail($validacionId);

        // Verificar permisos manualmente
        $usuario = $request->user();
        if (!$usuario->esProfesional() && !$usuario->esAdministrador()) {
            return response()->json([
                'message' => 'No autorizado para validar actividades'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:confirmada,rechazada',
            'comentario_testigo' => 'required_if:estado,rechazada|string',
            'confianza_validacion' => 'required|in:alto,medio,bajo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validacion->update([
                'estado' => $request->estado,
                'comentario_testigo' => $request->comentario_testigo,
                'confianza_validacion' => $request->confianza_validacion,
                'fecha_validacion' => now()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Validación de actividad procesada exitosamente',
                'validacion' => $validacion
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al procesar validación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function estadisticasPaciente($pacienteId, Request $request)
    {
        $paciente = Paciente::findOrFail($pacienteId);
        
        // Verificar permisos
        $usuario = $request->user();
        if (!$usuario->esProfesional() && !$usuario->esAdministrador() && $usuario->id !== $paciente->usuario_id) {
            return response()->json([
                'message' => 'No autorizado para ver estas estadísticas'
            ], 403);
        }

        $estadisticas = [
            'total_asignadas' => $paciente->actividades()->count(),
            'total_completadas' => $paciente->actividades()
                ->wherePivot('estado', 'completada')
                ->count(),
            'total_pendientes' => $paciente->actividades()
                ->wherePivot('estado', 'asignada')
                ->count(),
            'tasa_completitud' => $this->calcularTasaCompletitud($paciente),
            'puntos_totales' => $paciente->actividades()
                ->wherePivot('estado', 'completada')
                ->sum('puntos_obtenidos'),
            'actividades_por_dificultad' => $paciente->actividades()
                ->selectRaw('dificultad, COUNT(*) as count')
                ->groupBy('dificultad')
                ->get()
                ->pluck('count', 'dificultad'),
            'proxima_actividad_vencer' => $paciente->actividades()
                ->wherePivot('estado', 'asignada')
                ->wherePivot('fecha_vencimiento', '>=', now())
                ->orderBy('fecha_vencimiento', 'asc')
                ->first()
        ];

        return response()->json([
            'estadisticas' => $estadisticas,
            'paciente' => [
                'id' => $paciente->id,
                'nombre_completo' => $paciente->usuario->nombre . ' ' . $paciente->usuario->apellido
            ]
        ]);
    }

    private function calcularTasaCompletitud(Paciente $paciente)
    {
        $totalAsignadas = $paciente->actividades()->count();
        $totalCompletadas = $paciente->actividades()
            ->wherePivot('estado', 'completada')
            ->count();

        return $totalAsignadas > 0 ? ($totalCompletadas / $totalAsignadas) * 100 : 0;
    }
}