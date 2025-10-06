<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Clinica;
use App\Models\Profesional;
use App\Models\Paciente;
use App\Models\TriajeInicial;
use App\Models\ConfiguracionMatching;
use App\Models\Cita;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        if ($usuario->esPaciente()) {
            return $this->dashboardPaciente($usuario);
        } elseif ($usuario->esProfesional()) {
            return $this->dashboardProfesional($usuario);
        } else {
            return $this->dashboardAdministrador($usuario);
        }
    }

    private function dashboardPaciente(Usuario $usuario)
    {
        $paciente = $usuario->paciente;

        $manuscritosRecientes = $paciente->manuscritos()
            ->with('indiceEstadoAnimico')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $ieaReciente = $paciente->indicesEstadoAnimico()
            ->orderBy('fecha_calculo', 'desc')
            ->first();

        // Obtener matches pendientes
        $matchesPendientes = $paciente->profesionales()
            ->wherePivot('estado', 'pendiente')
            ->with('usuario')
            ->get();

        // ✅ CORRECCIÓN: Obtener clínicas activas
        $clinicasActivas = \App\Models\Clinica::where('estado', 'activa')->get();

        return view('dashboard.paciente', compact(
            'paciente',
            'manuscritosRecientes',
            'ieaReciente',
            'matchesPendientes',
            'clinicasActivas' // Ahora está definida
        ));
    }

    private function dashboardProfesional(Usuario $usuario)
    {

        $profesional = Profesional::with(['clinicas'])->where('usuario_id', $usuario->id_usuario)->first();

        if (!$profesional) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Perfil profesional no encontrado.');
        }

        // Si está pendiente, mostrar vista especial
        if ($profesional->estado_verificacion === 'pendiente') {
            $clinica = $profesional->clinicas->first();
            return view('dashboard.profesional-pendiente', compact('profesional', 'clinica'));
        }

        // Si está rechazado, mostrar mensaje
        if ($profesional->estado_verificacion === 'rechazado') {
            return view('dashboard.profesional-rechazado', compact('profesional'));
        }

        // PROFESIONAL APROBADO - Dashboard completo
        $pacientesAsignados = $profesional->pacientes()->count();
        $matchesPendientes = $profesional->matchesPendientes()->count();
        $compatibilidadPromedio = $profesional->compatibilidadPromedio();

        // Estadísticas mensuales
        $estadisticasMensuales = [
            'nuevos_pacientes' => $profesional->nuevosPacientesEsteMes(),
            'sesiones_completadas' => $profesional->sesionesCompletadasEsteMes(),
            'ingresos' => $profesional->ingresosEsteMes()
        ];
        $profesional = $usuario->profesional;

        // OBTENER PALABRAS CLAVE FILTRADAS POR ESPECIALIDAD DEL ADMIN
        $palabrasClaveSistema = \App\Models\PalabraClave::where('estado', true)
            ->where('especialidad_recomendada', $profesional->especialidad_principal)
            ->orderBy('categoria')
            ->orderBy('palabra')
            ->get()
            ->groupBy('categoria');

        // Estadísticas con manejo de errores
        $pacientesActivos = $profesional->pacientes()
            ->wherePivot('estado', 'activo')
            ->count();

        // CORREGIR: Inicializar $citasHoy con valor por defecto
        $citasHoy = 0;
        try {
            // Verificar si la tabla de citas existe antes de hacer la consulta
            if (\Illuminate\Support\Facades\Schema::hasTable('citas')) {
                $citasHoy = \App\Models\Cita::where('profesional_id', $profesional->id)
                    ->whereDate('fecha_cita', today())
                    ->count();
            }
        } catch (\Exception $e) {
            // Si hay error, mantener el valor por defecto 0
            $citasHoy = 0;
        }

        // Calcular compatibilidad promedio
        $compatibilidadPromedio = $profesional->pacientes()
            ->avg('puntuacion_compatibilidad') ?? 0;

        // Nuevos pacientes últimos 7 días
        $nuevosPacientes = $profesional->pacientes()
            ->wherePivot('fecha_asignacion', '>=', now()->subDays(7))
            ->count();

        // Pacientes recientes con información de triaje
        $pacientesRecientes = $profesional->pacientes()
            ->with(['usuario'])
            ->orderBy('fecha_asignacion', 'desc')
            ->take(5)
            ->get();

        // Clínica principal
        $clinicaPrincipal = $profesional->clinicas()->first();

        // Estadísticas de matching
        $coincidenciasMes = $profesional->pacientes()
            ->wherePivot('fecha_asignacion', '>=', now()->startOfMonth())
            ->count();

        $tasaAceptacion = $this->calcularTasaAceptacion($profesional);

        // Obtener pacientes pendientes
        $pacientesPendientes = $profesional->pacientes()
            ->wherePivot('estado', 'pendiente')
            ->with('usuario')
            ->get();

        return view('dashboard.profesional', compact(
            'profesional',
            'pacientesActivos',
            'citasHoy',
            'compatibilidadPromedio',
            'nuevosPacientes',
            'pacientesRecientes',
            'clinicaPrincipal',
            'coincidenciasMes',
            'tasaAceptacion',
            'pacientesPendientes',
            'palabrasClaveSistema' // NUEVO: pasar palabras clave filtradas
        ));
    }

    private function dashboardAdministrador(Usuario $usuario)
    {
        // Contadores para el dashboard
        $totalClinicas = Clinica::count();
        $totalProfesionales = Profesional::count();
        $totalPacientes = Paciente::count();
        $triajesPendientes = TriajeInicial::where('estado_triaje', 'pendiente')->count();

        // Nuevos contadores para solicitudes
        $solicitudesPendientes = Profesional::where('estado_verificacion', 'pendiente')->count();
        $solicitudesRecientes = Profesional::with('usuario')
            ->where('estado_verificacion', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Lista de clínicas
        $clinicas = Clinica::all();

        // Cálculo de efectividad y tiempo promedio de asignación
        $efectividadMatching = 0;
        $tiempoPromedioAsignacion = 0;

        $configMatching = ConfiguracionMatching::where('tipo_configuracion', 'pesos_matching')
            ->where('estado', 1)
            ->orderByDesc('version')
            ->first();

        if ($configMatching) {
            $efectividadMatching = $configMatching->efectividad ?? 0;
            $tiempoPromedioAsignacion = $configMatching->tiempo_promedio ?? 0;
        }

        return view('dashboard.administrador', compact(
            'usuario',
            'totalClinicas',
            'totalProfesionales',
            'totalPacientes',
            'triajesPendientes',
            'solicitudesPendientes',
            'solicitudesRecientes',
            'clinicas',
            'efectividadMatching',
            'tiempoPromedioAsignacion'
        ));
    }
    private function calcularTasaAceptacion(Profesional $profesional)
    {
        $totalAsignaciones = $profesional->pacientes()->count();
        $aceptadas = $profesional->pacientes()->wherePivot('estado', 'activo')->count();
        return $totalAsignaciones > 0 ? round(($aceptadas / $totalAsignaciones) * 100, 1) : 0;
    }
}
