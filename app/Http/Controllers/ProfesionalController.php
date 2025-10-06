<?php

namespace App\Http\Controllers;

use App\Models\Profesional;
use App\Models\Paciente;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfesionalController extends Controller
{
    public function dashboard()
    {
        $profesional = Profesional::with(['usuario', 'clinicas', 'pacientes'])
            ->where('usuario_id', Auth::id())
            ->firstOrFail();

        // Estadísticas con manejo de errores
        $pacientesActivos = $profesional->pacientes()->wherePivot('estado', 'activo')->count();

        try {
            $citasHoy = Cita::where('profesional_id', $profesional->id)
                ->whereDate('fecha_cita', today())
                ->count();
        } catch (\Exception $e) {
            $citasHoy = 0; // Tabla de citas no existe todavía
        }

        // Calcular compatibilidad promedio
        $compatibilidadPromedio = $profesional->pacientes()->avg('puntuacion_compatibilidad') ?? 0;

        // Nuevos pacientes últimos 7 días
        $nuevosPacientes = $profesional->pacientes()
            ->wherePivot('fecha_asignacion', '>=', now()->subDays(7))
            ->count();

        // Pacientes recientes con información de triaje
        $pacientesRecientes = $profesional->pacientes()
            ->with(['usuario'])
            ->orderBy('fecha_asignacion', 'desc')
            ->limit(5)
            ->get();

        // Clínica principal
        $clinicaPrincipal = $profesional->clinicas()->first();

        // Estadísticas de matching
        $coincidenciasMes = $profesional->pacientes()
            ->wherePivot('fecha_asignacion', '>=', now()->startOfMonth())
            ->count();

        $tasaAceptacion = $this->calcularTasaAceptacion($profesional);

        return view('dashboard.profesional', compact(
            'profesional',
            'pacientesActivos',
            'citasHoy',
            'compatibilidadPromedio',
            'nuevosPacientes',
            'pacientesRecientes',
            'clinicaPrincipal',
            'coincidenciasMes',
            'tasaAceptacion'
        ));
    }

    public function actualizarPalabrasClave(Request $request)
    {
        // LOG TEMPORAL PARA DEBUG
        Log::info('Datos recibidos en actualizarPalabrasClave:', [
            'all_data' => $request->all(),
            'json_data' => $request->getContent(),
            'headers' => $request->headers->all()
        ]);
        
        $profesional = Profesional::where('usuario_id', Auth::id())->firstOrFail();

        if (!$profesional->estaAprobado()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado: debes ser aprobado por el administrador.'
            ], 403);
        }

        // CORREGIR: Cambiar 'palabras_clave' por 'palabras_clave_especialidad'
        $request->validate([
            'palabras_clave_especialidad' => 'required|array|min:3',
            'palabras_clave_especialidad.*' => 'string|max:50'
        ]);

        try {
            $palabrasClave = $request->palabras_clave_especialidad;
            $sintomasAtiende = $this->generarSintomasDesdePalabrasClave($palabrasClave);

            $profesional->update([
                'palabras_clave_especialidad' => $palabrasClave,
                'sintomas_atiende' => $sintomasAtiende
            ]);

            Log::info("Palabras clave actualizadas para profesional {$profesional->id}", [
                'palabras_clave' => $palabrasClave,
                'sintomas_atiende' => $sintomasAtiende,
                'especialidad' => $profesional->especialidad_principal
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Palabras clave actualizadas exitosamente. El sistema ahora puede hacer matching con pacientes.',
                'palabras_clave' => $palabrasClave,
                'sintomas_atiende' => $sintomasAtiende,
                'total_palabras' => count($palabrasClave)
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar palabras clave: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las palabras clave: ' . $e->getMessage()
            ], 500);
        }
    }
    private function generarSintomasDesdePalabrasClave(array $palabrasClave)
    {
        $mapeoSintomas = [
            'familia' => ['problemas familiares', 'conflictos parentales', 'relaciones familiares'],
            'padres' => ['problemas con padres', 'relación parental'],
            'hijos' => ['problemas con hijos', 'crianza', 'educación'],
            'pareja' => ['problemas de pareja', 'relaciones amorosas', 'conflictos de pareja'],
            'matrimonio' => ['problemas matrimoniales', 'crisis matrimonial'],
            'divorcio' => ['proceso de divorcio', 'separación', 'duelo divorcio'],
            'trabajo' => ['estrés laboral', 'problemas laborales', 'ambiente laboral'],
            'empleo' => ['problemas de empleo', 'desempleo', 'cambio laboral'],
            'jefe' => ['conflictos con superiores', 'problemas con jefes'],
            'compañeros' => ['conflictos con compañeros', 'relaciones laborales'],
            'ansiedad' => ['trastorno de ansiedad', 'crisis de ansiedad', 'ansiedad generalizada'],
            'depresión' => ['trastorno depresivo', 'episodio depresivo', 'depresión mayor'],
            'trauma' => ['trauma psicológico', 'estrés postraumático', 'eventos traumáticos'],
            'estrés' => ['estrés crónico', 'manejo del estrés', 'síntomas de estrés']
        ];

        $sintomas = [];
        foreach ($palabrasClave as $palabra) {
            if (isset($mapeoSintomas[$palabra])) {
                $sintomas = array_merge($sintomas, $mapeoSintomas[$palabra]);
            } else {
                // Si no hay mapeo específico, usar la palabra como síntoma general
                $sintomas[] = $palabra;
            }
        }

        return array_unique($sintomas);
    }

    private function calcularTasaAceptacion(Profesional $profesional)
    {
        $totalAsignaciones = $profesional->pacientes()->count();
        $aceptadas = $profesional->pacientes()->wherePivot('estado', 'activo')->count();

        return $totalAsignaciones > 0 ? round(($aceptadas / $totalAsignaciones) * 100, 1) : 0;
    }
}
