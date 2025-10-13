<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\SintomaEspecialidad;
use App\Models\ConfiguracionProfesionalSintoma;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;

class ProfesionalConfiguracionController extends Controller
{
    public function mostrarConfiguracionSintomas()
    {
        try {
            $profesional = Profesional::where('usuario_id', Auth::id())->firstOrFail();

            Log::info("Verificando profesional ID: {$profesional->id_profesional}, estado: {$profesional->estado_verificacion}");

            if (!$profesional->estaAprobado()) {
                Log::warning("Profesional no aprobado, redirigiendo al dashboard");
                return redirect()->route('dashboard')
                    ->with('error', 'Debes ser aprobado para configurar síntomas.');
            }

            // Obtener especialidad del profesional - CORREGIDO
            $especialidad = Especialidad::where('nombre', $profesional->especialidad_principal)->first();

            if (!$especialidad) {
                Log::warning("No se encontró especialidad para: {$profesional->especialidad_principal}");
                return redirect()->route('dashboard')
                    ->with('error', 'No tienes una especialidad asignada o no está configurada en el sistema.');
            }

            Log::info("Especialidad encontrada: {$especialidad->nombre}");

            // Obtener síntomas de la especialidad
            $sintomasEspecialidad = SintomaEspecialidad::with('palabraClave')
                ->where('especialidad_id', $especialidad->id_especialidad)
                ->where('activo', true)
                ->get();

            Log::info("Síntomas encontrados: " . $sintomasEspecialidad->count());

            // Obtener configuraciones actuales del profesional
            $configuracionesActuales = ConfiguracionProfesionalSintoma::with('sintoma')
                ->where('profesional_id', $profesional->id_profesional)
                ->where('activo', true)
                ->get()
                ->keyBy('sintoma_id');

            Log::info("Configuraciones actuales: " . $configuracionesActuales->count());

            return view('profesional.configuracion-sintomas', compact(
                'profesional',
                'especialidad',
                'sintomasEspecialidad',
                'configuracionesActuales'
            ));
        } catch (\Exception $e) {
            Log::error('Error en mostrarConfiguracionSintomas: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Error al cargar la configuración de síntomas: ' . $e->getMessage());
        }
    }

    public function actualizarConfiguracionSintomas(Request $request)
    {
        Log::info('=== INICIO actualizarConfiguracionSintomas ===');
        Log::info('Datos recibidos:', $request->all());

        try {
            $profesional = Profesional::where('usuario_id', Auth::id())->firstOrFail();

            // Validación corregida - más flexible
            $validated = $request->validate([
                'sintomas' => 'required|array|min:1',
                'sintomas.*.sintoma_id' => 'required|exists:sintomas_especialidad,id_sintoma',
                'sintomas.*.periodo_activo' => 'required|in:diario,semanal,quincenal,mensual,personalizado',
                'sintomas.*.fecha_inicio' => 'nullable|date',
                'sintomas.*.fecha_fin' => 'nullable|date|after:sintomas.*.fecha_inicio',
                'sintomas.*.max_pacientes' => 'nullable|integer|min:1|max:50',
                'sintomas.*.prioridad' => 'required|in:baja,media,alta,urgente'
            ]);

            Log::info('Validación pasada, procesando datos...');

            DB::beginTransaction();

            // Desactivar configuraciones anteriores
            ConfiguracionProfesionalSintoma::where('profesional_id', $profesional->id_profesional)
                ->update(['activo' => false]);

            Log::info('Configuraciones anteriores desactivadas');

            // Crear nuevas configuraciones
            foreach ($request->sintomas as $sintomaConfig) {
                $fechaInicio = $sintomaConfig['periodo_activo'] === 'personalizado' && !empty($sintomaConfig['fecha_inicio'])
                    ? $sintomaConfig['fecha_inicio']
                    : now();

                $fechaFin = $sintomaConfig['periodo_activo'] === 'personalizado' && !empty($sintomaConfig['fecha_fin'])
                    ? $sintomaConfig['fecha_fin']
                    : $this->calcularFechaFin($sintomaConfig['periodo_activo']);

                ConfiguracionProfesionalSintoma::create([
                    'profesional_id' => $profesional->id_profesional,
                    'sintoma_id' => $sintomaConfig['sintoma_id'],
                    'periodo_activo' => $sintomaConfig['periodo_activo'],
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'max_pacientes' => $sintomaConfig['max_pacientes'] ?? 10,
                    'prioridad' => $sintomaConfig['prioridad'],
                    'activo' => true
                ]);

                Log::info("Configuración creada para síntoma ID: {$sintomaConfig['sintoma_id']}");
            }

            DB::commit();

            Log::info('=== Configuración guardada exitosamente ===');

            return response()->json([
                'success' => true,
                'message' => 'Configuración de síntomas actualizada exitosamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación: ' . json_encode($e->errors()));

            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', array_flatten($e->errors())),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar configuración: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calcularFechaFin($periodo)
    {
        return match ($periodo) {
            'diario' => now()->addDay(),
            'semanal' => now()->addWeek(),
            'quincenal' => now()->addWeeks(2),
            'mensual' => now()->addMonth(),
            default => now()->addWeek()
        };
    }

    public function activarDisponibilidad(Request $request)
    {
        try {
            $profesional = Profesional::where('usuario_id', Auth::id())->firstOrFail();

            Log::info('Datos recibidos para disponibilidad:', $request->all());

            // Validación más flexible
            $request->validate([
                'disponible' => 'required'
            ]);

            // Manejar diferentes formatos de entrada
            $disponible = $request->disponible;

            if (is_string($disponible)) {
                $disponible = $disponible === 'true' || $disponible === '1' || $disponible === 'on';
            } elseif (is_numeric($disponible)) {
                $disponible = (bool)$disponible;
            } else {
                $disponible = (bool)$disponible;
            }

            Log::info("Valor convertido de disponibilidad: " . ($disponible ? 'true' : 'false'));

            $profesional->update([
                'disponibilidad_inmediata' => $disponible,
                'fecha_activacion_disponibilidad' => $disponible ? now() : null
            ]);

            Log::info("Disponibilidad actualizada exitosamente para profesional {$profesional->id_profesional}");

            return response()->json([
                'success' => true,
                'message' => $disponible
                    ? '¡Ahora estás disponible para recibir pacientes!'
                    : 'Has desactivado tu disponibilidad',
                'disponible' => $disponible,
                'nuevo_estado' => $disponible ? 'disponible' : 'no_disponible'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Error de validación en disponibilidad: ' . json_encode($e->errors()));

            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->errors()['disponible'] ?? ['Campo disponible requerido'])
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar disponibilidad: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar disponibilidad: ' . $e->getMessage()
            ], 500);
        }
    }
}
