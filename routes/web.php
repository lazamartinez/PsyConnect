<?php

use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ManuscritoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClinicaController;
use App\Http\Controllers\ProfesionalController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\SolicitudProfesionalController;
use App\Http\Controllers\Admin\GestionProfesionalesController;
use App\Http\Controllers\Admin\GestionPalabrasClaveController;
use App\Http\Controllers\Admin\ConfiguracionAvanzadaController;
use App\Http\Controllers\Admin\GestionEspecialidadesController;
use App\Http\Controllers\TriajeController;
use App\Http\Controllers\MatchingController;
use App\Http\Controllers\ConfiguracionProfesionalController;
use App\Http\Middleware\PacienteMiddleware;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Página de bienvenida
Route::get('/', function () {
    return view('welcome');
});

// Autenticación
Route::get('/registro', [RegistroController::class, 'mostrarFormularioRegistro'])->name('registro');
Route::post('/registro', [RegistroController::class, 'registrar']);

Route::get('/login', [LoginController::class, 'mostrarFormularioLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas públicas para obtener clínicas activas
Route::get('/clinicas/activas', [ClinicaController::class, 'clinicasActivas']);

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manuscritos
    Route::resource('manuscritos', ManuscritoController::class);

    // Triaje solo para pacientes
    Route::middleware([PacienteMiddleware::class])->group(function () {
        // Route::get('/triaje', [TriajeController::class, 'mostrarFormularioTriaje'])->name('triaje.mostrar');
        Route::post('/triaje/procesar-con-matching', [MatchingController::class, 'procesarTriajeYMatching'])->name('triaje.procesar.matching');
        Route::post('/triaje', [TriajeController::class, 'procesarTriaje'])->name('triaje.procesar');
        Route::post('/triaje/procesar-con-matching', [MatchingController::class, 'procesarTriajeYMatching'])->name('triaje.procesar.matching');
        Route::post('/matching/aceptar/{profesional}', [MatchingController::class, 'aceptarMatch'])->name('matching.aceptar');
        Route::post('/matching/rechazar/{profesional}', [MatchingController::class, 'rechazarMatch'])->name('matching.rechazar');
        Route::get('/matching/pendientes', [MatchingController::class, 'obtenerMatchesPendientes'])->name('matching.pendientes');
        Route::post('/manuscritos/{manuscrito}/triaje', [TriajeController::class, 'integrarConManuscrito'])->name('manuscritos.triaje');
    });

    // Rutas para administradores
    Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
        // Gestión de clínicas
        Route::get('/clinicas', [ClinicaController::class, 'index'])->name('admin.clinicas');
        Route::post('/clinicas', [ClinicaController::class, 'store']);
        Route::put('/clinicas/{id}/estado', [ClinicaController::class, 'updateEstado']);
        Route::post('/clinicas/{clinicaId}/profesionales', [ClinicaController::class, 'asignarProfesional']);

        // Gestión de profesionales
        Route::get('/profesionales/solicitudes', [GestionProfesionalesController::class, 'index'])->name('admin.profesionales.solicitudes');
        Route::get('/profesionales/{id}', [GestionProfesionalesController::class, 'show'])->name('admin.profesionales.show');
        Route::post('/profesionales/{id}/aprobar', [GestionProfesionalesController::class, 'aprobar'])->name('admin.profesionales.aprobar');
        Route::post('/profesionales/{id}/rechazar', [GestionProfesionalesController::class, 'rechazar'])->name('admin.profesionales.rechazar');

        // Gestión de palabras clave - RUTAS CORREGIDAS
        Route::get('/palabras-clave', [GestionPalabrasClaveController::class, 'index'])->name('admin.palabras-clave.index');
        Route::post('/palabras-clave', [GestionPalabrasClaveController::class, 'store'])->name('admin.palabras-clave.store');
        Route::get('/palabras-clave/{id}', [GestionPalabrasClaveController::class, 'show'])->name('admin.palabras-clave.show');
        Route::put('/palabras-clave/{id}', [GestionPalabrasClaveController::class, 'update'])->name('admin.palabras-clave.update');
        Route::delete('/palabras-clave/{id}', [GestionPalabrasClaveController::class, 'destroy'])->name('admin.palabras-clave.destroy');
        Route::post('/palabras-clave/{id}/estado', [GestionPalabrasClaveController::class, 'cambiarEstado'])->name('admin.palabras-clave.estado');

        // Configuración del sistema de matching
        Route::get('/configuracion/matching', [ConfiguracionController::class, 'configuracionMatching'])->name('admin.configuracion.matching');
        Route::post('/configuracion/matching', [ConfiguracionController::class, 'guardarConfiguracionMatching'])->name('admin.configuracion.matching.guardar');
        Route::post('/configuracion/reglas-especialidad', [ConfiguracionController::class, 'guardarReglasEspecialidad'])->name('admin.configuracion.reglas-especialidad');

        // Configuración avanzada del sistema - RUTAS CORREGIDAS
        Route::get('/configuracion-avanzada', [ConfiguracionAvanzadaController::class, 'index'])->name('admin.configuracion-avanzada');
        Route::post('/configuracion/guardar', [ConfiguracionAvanzadaController::class, 'guardarConfiguracionCompleta'])->name('admin.configuracion.guardar');
        Route::get('/configuracion/{clinicaId}/{tipo}', [ConfiguracionAvanzadaController::class, 'obtenerConfiguracionClinica'])->name('admin.configuracion.obtener');

        // Reportes
        Route::get('/reportes/matching', [ReporteController::class, 'reporteMatching'])->name('admin.reportes.matching');
        Route::get('/reportes/triaje', [ReporteController::class, 'reporteTriaje'])->name('admin.reportes.triaje');

        // Solicitudes de profesionales
        Route::get('/solicitudes', [App\Http\Controllers\Admin\GestionProfesionalesController::class, 'index'])->name('admin.solicitudes.index');
        Route::get('/solicitudes/{id}', [App\Http\Controllers\Admin\GestionProfesionalesController::class, 'show'])->name('admin.solicitudes.show');
        Route::post('/solicitudes/{id}/aprobar', [App\Http\Controllers\Admin\GestionProfesionalesController::class, 'aprobar'])->name('admin.solicitudes.aprobar');
        Route::post('/solicitudes/{id}/rechazar', [App\Http\Controllers\Admin\GestionProfesionalesController::class, 'rechazar'])->name('admin.solicitudes.rechazar');

        Route::post('/sistema/reparar', function () {
            try {
                Artisan::call('sistema:reparar-matching');
                $output = Artisan::output();

                return response()->json([
                    'success' => true,
                    'message' => 'Sistema reparado exitosamente',
                    'output' => $output
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al reparar el sistema: ' . $e->getMessage()
                ], 500);
            }
        })->name('admin.sistema.reparar');

        // Gestión de Especialidades
        Route::get('/especialidades', [GestionEspecialidadesController::class, 'index'])->name('admin.especialidades.index');
        Route::post('/especialidades', [GestionEspecialidadesController::class, 'store'])->name('admin.especialidades.store');
        Route::put('/especialidades/{id}', [GestionEspecialidadesController::class, 'update'])->name('admin.especialidades.update');
        Route::delete('/especialidades/{id}', [GestionEspecialidadesController::class, 'destroy'])->name('admin.especialidades.destroy');
        Route::post('/especialidades/{id}/estado', [GestionEspecialidadesController::class, 'cambiarEstado'])->name('admin.especialidades.estado');
        Route::get('/especialidades/por-rol/{rol}', [GestionEspecialidadesController::class, 'obtenerPorRol'])->name('admin.especialidades.por-rol');

        // Palabras Clave por Especialidad
        Route::get('/palabras-clave/por-especialidad/{especialidadId}', [GestionPalabrasClaveController::class, 'obtenerPorEspecialidad'])->name('admin.palabras-clave.por-especialidad');
    });

    // Rutas para profesionales
    Route::prefix('profesional')->middleware(['auth', 'profesional'])->group(function () {
        // Mostrar configuración del profesional
        Route::get('/configuracion', [ConfiguracionProfesionalController::class, 'mostrarConfiguracion'])
            ->name('profesional.configuracion');

        // Actualizar palabras clave (POST)
        Route::post('/palabras-clave', [ProfesionalController::class, 'actualizarPalabrasClave'])
            ->name('profesional.actualizar-palabras-clave');

        // NUEVA RUTA: Actualizar disponibilidad
        Route::post('/disponibilidad', [ProfesionalController::class, 'actualizarDisponibilidad'])
            ->name('profesional.disponibilidad');

        // Perfil y compatibilidad
        Route::get('/perfil', [ProfesionalController::class, 'editarPerfil'])->name('profesional.perfil.editar');
        Route::put('/perfil', [ProfesionalController::class, 'actualizarPerfil'])->name('profesional.perfil.actualizar');

        Route::get('/compatibilidad/{pacienteId}', [ProfesionalController::class, 'calcularCompatibilidad'])
            ->name('profesional.compatibilidad');
    });

    // Opcional: si alguien intenta hacer GET a /profesional/palabras-clave, redirige al modal/configuración
    Route::get('/profesional/palabras-clave', function () {
        return redirect()->route('profesional.configuracion');
    });
});

// Matching mejorado
Route::post('/triaje/procesar-avanzado', [MatchingController::class, 'procesarTriajeYMatching'])->name('triaje.procesar.avanzado');

Route::get('/test-matching-debug', function () {
    $paciente = App\Models\Paciente::first();
    $descripcion = "Tengo problemas de ansiedad y también con mi familia. Mi pareja y yo estamos pasando por una crisis y no sé cómo manejar la situación. Además he estado muy deprimido";

    $matchingService = new App\Services\MatchingService();
    $resultado = $matchingService->procesarTriajeCompleto($paciente, $descripcion);

    return response()->json($resultado);
});

Route::get('/reparar-sistema-completo', function () {

    // 1. Normalizar especialidades
    $profesionales = App\Models\Profesional::all();
    $actualizados = 0;

    foreach ($profesionales as $prof) {
        $especialidadNormalizada = match ($prof->especialidad_principal) {
            'Psicología Clínica' => 'psicologo',
            'Psiquiatría General' => 'psiquiatra',
            'Nutrición' => 'nutricionista',
            default => $prof->especialidad_principal
        };

        if ($prof->especialidad_principal !== $especialidadNormalizada) {
            $prof->update(['especialidad_principal' => $especialidadNormalizada]);
            $actualizados++;
        }
    }

    // 2. Verificar configuración de niveles de urgencia
    $configTriaje = App\Models\ConfiguracionMatching::where('tipo_configuracion', 'reglas_triaje')
        ->where('estado', true)
        ->first();

    if (!$configTriaje) {
        App\Models\ConfiguracionMatching::create([
            'nombre_configuracion' => 'Reglas Triaje Reparadas',
            'tipo_configuracion' => 'reglas_triaje',
            'configuracion_json' => [
                'longitud_minima_descripcion' => 30,
                'revision_manual_activada' => false,
                'niveles_urgencia' => [
                    'critico' => ['min_palabras' => 1, 'min_puntaje' => 2.0],
                    'alto' => ['min_palabras' => 2, 'min_puntaje' => 1.0],
                    'medio' => ['min_palabras' => 1, 'min_puntaje' => 0.5],
                    'bajo' => ['min_palabras' => 0, 'min_puntaje' => 0]
                ]
            ],
            'estado' => true,
            'version' => now()->timestamp
        ]);
    }

    // 3. Verificar palabras clave básicas
    $palabrasBasicas = [
        ['palabra' => 'ansiedad', 'categoria' => 'ansiedad', 'nivel_alerta' => 'medio', 'peso_urgencia' => 0.8, 'especialidad_recomendada' => 'psicologo'],
        ['palabra' => 'depresión', 'categoria' => 'depresion', 'nivel_alerta' => 'medio', 'peso_urgencia' => 0.8, 'especialidad_recomendada' => 'psicologo'],
        ['palabra' => 'familia', 'categoria' => 'familia', 'nivel_alerta' => 'bajo', 'peso_urgencia' => 0.6, 'especialidad_recomendada' => 'psicologo'],
        ['palabra' => 'pareja', 'categoria' => 'pareja', 'nivel_alerta' => 'bajo', 'peso_urgencia' => 0.6, 'especialidad_recomendada' => 'psicologo'],
        ['palabra' => 'crisis', 'categoria' => 'ansiedad', 'nivel_alerta' => 'alto', 'peso_urgencia' => 0.9, 'especialidad_recomendada' => 'psicologo'],
        ['palabra' => 'suicidio', 'categoria' => 'suicida', 'nivel_alerta' => 'critico', 'peso_urgencia' => 1.0, 'especialidad_recomendada' => 'psiquiatra'],
    ];

    $palabrasCreadas = 0;
    foreach ($palabrasBasicas as $palabraData) {
        $existe = App\Models\PalabraClave::where('palabra', $palabraData['palabra'])->first();
        if (!$existe) {
            App\Models\PalabraClave::create(array_merge($palabraData, ['estado' => true, 'creado_por' => 1]));
            $palabrasCreadas++;
        }
    }

    return response()->json([
        'profesionales_actualizados' => $actualizados,
        'configuracion_triaje' => $configTriaje ? 'Existente' : 'Creada nueva',
        'palabras_creadas' => $palabrasCreadas,
        'profesionales_aprobados' => App\Models\Profesional::where('estado_verificacion', 'aprobado')->count(),
        'especialidades' => App\Models\Profesional::pluck('especialidad_principal')->toArray()
    ]);
});
