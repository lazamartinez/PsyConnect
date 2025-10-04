<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Paciente;
use App\Models\Auditoria; // Agregar esta línea
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AlertaController extends Controller
{
    public function ayudaInmediata(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paciente_id' => 'required|exists:pacientes,id',
            'nivel_urgencia' => 'required|in:bajo,medio,alto,critico',
            'ubicacion_actual' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $paciente = Paciente::with(['usuario', 'profesionales'])->findOrFail($request->paciente_id);
        
        // Verificar que el paciente está solicitando ayuda para sí mismo
        if ($request->user()->id !== $paciente->usuario_id) {
            return response()->json([
                'message' => 'No autorizado para solicitar ayuda para este paciente'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Crear alerta de ayuda inmediata
            $alerta = Alerta::create([
                'paciente_id' => $paciente->id,
                'tipo' => 'crisis',
                'nivel_urgencia' => $request->nivel_urgencia,
                'descripcion' => 'Solicitud de ayuda inmediata activada por el paciente',
                'fecha_generacion' => now(),
                'estado' => 'pendiente',
                'prioridad' => 10, // Máxima prioridad
                'canal_notificacion' => 'todos',
                'receptor_principal' => $paciente->profesionales->first()->id ?? null,
                'protocolo_activado' => 'ayuda_inmediata',
                'seguimiento_requerido' => true,
                'evidencia_generacion' => [
                    'tipo_activacion' => 'manual',
                    'ubicacion' => $request->ubicacion_actual,
                    'timestamp' => now()->toISOString()
                ]
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Ayuda inmediata activada. Los profesionales han sido notificados.',
                'alerta' => $alerta,
                'protocolo_activado' => true
            ], 202);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al activar ayuda inmediata: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Alerta::with(['paciente.usuario', 'profesional.usuario']);

        // Filtros para profesionales (solo ven sus alertas)
        if ($request->user()->esProfesional()) {
            $profesionalId = $request->user()->profesional->id;
            $query->where('receptor_principal', $profesionalId)
                  ->orWhereJsonContains('receptores_secundarios', $profesionalId);
        }

        // Filtros para pacientes (solo ven sus propias alertas)
        if ($request->user()->esPaciente()) {
            $pacienteId = $request->user()->paciente->id;
            $query->where('paciente_id', $pacienteId);
        }

        // Filtros adicionales
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('nivel_urgencia')) {
            $query->where('nivel_urgencia', $request->nivel_urgencia);
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('fecha_desde')) {
            $query->where('fecha_generacion', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->where('fecha_generacion', '<=', $request->fecha_hasta);
        }

        $alertas = $query->orderBy('fecha_generacion', 'desc')
                        ->paginate(20);

        return response()->json([
            'alertas' => $alertas
        ]);
    }

    public function show($id, Request $request)
    {
        $alerta = Alerta::with([
            'paciente.usuario',
            'profesional.usuario',
            'indiceEstadoAnimico',
            'notificaciones'
        ])->findOrFail($id);

        // Verificar permisos manualmente
        $usuario = $request->user();
        if ($usuario->esPaciente() && $alerta->paciente_id !== $usuario->paciente->id) {
            return response()->json([
                'message' => 'No autorizado para ver esta alerta'
            ], 403);
        }

        if ($usuario->esProfesional() && $alerta->receptor_principal !== $usuario->profesional->id) {
            return response()->json([
                'message' => 'No autorizado para ver esta alerta'
            ], 403);
        }

        return response()->json([
            'alerta' => $alerta
        ]);
    }

    public function actualizarEstado(Request $request, $id)
    {
        $alerta = Alerta::findOrFail($id);
        
        // Verificar permisos
        $usuario = $request->user();
        if (!$usuario->esProfesional() && !$usuario->esAdministrador()) {
            return response()->json([
                'message' => 'No autorizado para actualizar alertas'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,atendida,resuelta,descartada',
            'acciones_tomadas' => 'required|array',
            'comentarios' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $alerta->update([
                'estado' => $request->estado,
                'acciones_tomadas' => array_merge(
                    $alerta->acciones_tomadas ?? [],
                    $request->acciones_tomadas
                ),
                'fecha_resolucion' => $request->estado === 'resuelta' ? now() : null
            ]);

            // Registrar en auditoría
            Auditoria::create([
                'usuario_id' => $request->user()->id,
                'tipo_accion' => 'actualizar',
                'entidad_afectada' => 'Alerta',
                'id_entidad_afectada' => $alerta->id,
                'detalles_accion' => "Estado cambiado a: {$request->estado}",
                'cambios_realizados' => $request->all()
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Estado de alerta actualizado exitosamente',
                'alerta' => $alerta->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar alerta: ' . $e->getMessage()
            ], 500);
        }
    }

    // ... resto del código
}