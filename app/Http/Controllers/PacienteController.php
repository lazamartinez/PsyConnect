<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\IndiceEstadoAnimico;
use App\Models\Actividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Paciente::with(['usuario', 'tratamientos', 'profesionales']);

        // Filtros para profesionales
        if ($request->user()->esProfesional()) {
            $profesionalId = $request->user()->profesional->id;
            $query->whereHas('profesionales', function($q) use ($profesionalId) {
                $q->where('profesional_id', $profesionalId);
            });
        }

        // Filtros opcionales
        if ($request->has('estado_tratamiento')) {
            $query->where('estado_tratamiento', $request->estado_tratamiento);
        }

        if ($request->has('busqueda')) {
            $busqueda = $request->busqueda;
            $query->whereHas('usuario', function($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('apellido', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%");
            });
        }

        $pacientes = $query->paginate(15);

        return response()->json([
            'pacientes' => $pacientes
        ]);
    }

    public function show($id)
    {
        $paciente = Paciente::with([
            'usuario',
            'tratamientos',
            'profesionales.usuario',
            'historialClinico',
            'indicesEstadoAnimico' => function($query) {
                $query->orderBy('fecha_calculo', 'desc')->limit(30);
            },
            'actividades' => function($query) {
                $query->wherePivot('estado', '!=', 'completada')
                      ->orderBy('fecha_vencimiento', 'asc');
            }
        ])->findOrFail($id);

        // Verificar permisos (solo profesionales asignados o el propio paciente)
        $this->authorize('view', $paciente);

        return response()->json([
            'paciente' => $paciente
        ]);
    }

    public function estadisticas($id)
    {
        $paciente = Paciente::findOrFail($id);
        $this->authorize('view', $paciente);

        $estadisticas = [
            'iea_promedio_30dias' => IndiceEstadoAnimico::where('paciente_id', $id)
                ->where('fecha_calculo', '>=', now()->subDays(30))
                ->avg('valor_numerico'),
            
            'total_actividades_asignadas' => $paciente->actividades()
                ->wherePivot('estado', 'asignada')
                ->count(),
            
            'total_actividades_completadas' => $paciente->actividades()
                ->wherePivot('estado', 'completada')
                ->count(),
            
            'tasa_completitud' => $this->calcularTasaCompletitud($paciente),
            
            'alertas_recientes' => $paciente->alertas()
                ->where('fecha_generacion', '>=', now()->subDays(7))
                ->count(),
            
            'ultimo_iea' => $paciente->indicesEstadoAnimico()
                ->latest('fecha_calculo')
                ->first()
        ];

        return response()->json([
            'estadisticas' => $estadisticas
        ]);
    }

    public function historialIEA($id, Request $request)
    {
        $paciente = Paciente::findOrFail($id);
        $this->authorize('view', $paciente);

        $query = IndiceEstadoAnimico::where('paciente_id', $id)
            ->orderBy('fecha_calculo', 'desc');

        // Filtros de fecha
        if ($request->has('desde')) {
            $query->where('fecha_calculo', '>=', $request->desde);
        }

        if ($request->has('hasta')) {
            $query->where('fecha_calculo', '<=', $request->hasta);
        }

        if ($request->has('limite')) {
            $query->limit($request->limite);
        }

        $historial = $query->get();

        return response()->json([
            'historial' => $historial
        ]);
    }

    public function actividadesPendientes($id)
    {
        $paciente = Paciente::findOrFail($id);
        $this->authorize('view', $paciente);

        $actividades = $paciente->actividades()
            ->wherePivot('estado', 'asignada')
            ->wherePivot('fecha_vencimiento', '>=', now())
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        return response()->json([
            'actividades' => $actividades
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