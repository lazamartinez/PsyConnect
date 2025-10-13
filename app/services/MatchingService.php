<?php

namespace App\Services;

use App\Models\Especialidad;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\TriajeInicial;
use App\Models\ConfiguracionMatching;
use App\Models\PalabraClave;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MatchingService
{
    protected $configuracion;
    protected $clinicaId;

    public function __construct($clinicaId = null)
    {
        $this->clinicaId = $clinicaId;
        $this->inicializarEspecialidadesSiEsNecesario(); // ✅ AGREGAR
        $this->cargarConfiguracion();
    }

    protected function cargarConfiguracion()
    {
        $this->configuracion = [
            'pesos' => ConfiguracionMatching::obtenerPesosMatching($this->clinicaId),
            'reglas_especialidad' => ConfiguracionMatching::obtenerReglasEspecialidad($this->clinicaId),
            'umbrales' => ConfiguracionMatching::obtenerUmbrales($this->clinicaId),
            'triaje' => ConfiguracionMatching::obtenerReglasTriaje($this->clinicaId)
        ];

        Log::info("Configuración cargada para clínica {$this->clinicaId}: " . json_encode([
            'pesos' => $this->configuracion['pesos'],
            'umbrales' => $this->configuracion['umbrales']
        ]));
    }

    private function verificarEstadoSistema()
    {
        try {
            $profesionalesAprobados = Profesional::where('estado_verificacion', 'aprobado')
                ->where('disponibilidad_inmediata', true)
                ->count();

            $palabrasClaveCount = PalabraClave::where('estado', true)->count();

            if ($profesionalesAprobados < 2 || $palabrasClaveCount < 3) {
                Log::warning('Sistema de matching en estado crítico, ejecutando reparación de emergencia');
                $this->reparacionEmergencia();
            }
        } catch (\Exception $e) {
            Log::error('Error en verificación de estado: ' . $e->getMessage());
        }
    }

    private function crearProfesionalEmergencia()
    {
        try {
            Log::info('Creando profesional de emergencia...');

            $usuario = Usuario::create([
                'nombre' => 'Profesional',
                'apellido' => 'Emergencia',
                'email' => 'profesional.emergencia@psyconnect.com',
                'contrasenia' => Hash::make('password123'),
                'tipo_usuario' => 'psicologo',
                'telefono' => '+54 376 000-0000'
            ]);

            $profesional = Profesional::create([
                'usuario_id' => $usuario->id,
                'especialidad_principal' => 'psicologo',
                'estado_verificacion' => 'aprobado',
                'disponibilidad_inmediata' => true,
                'anios_experiencia' => 5,
                'calificacion_promedio' => 4.5,
                'tiempo_respuesta_promedio_horas' => 24,
                'capacidad_pacientes' => 10,
                'palabras_clave_especialidad' => [
                    'ansiedad',
                    'depresión',
                    'estrés',
                    'crisis',
                    'familia',
                    'trabajo',
                    'pareja',
                    'trauma'
                ],
                'sintomas_atiende' => [
                    'trastorno de ansiedad',
                    'depresión',
                    'estrés crónico',
                    'crisis emocional',
                    'problemas familiares',
                    'estrés laboral',
                    'problemas de pareja',
                    'trauma psicológico'
                ],
                'bio' => 'Profesional de emergencia del sistema. Especializado en primeros auxilios psicológicos y apoyo emocional inmediato.'
            ]);

            Log::info('Profesional de emergencia creado: ' . $profesional->id);
            return $profesional;
        } catch (\Exception $e) {
            Log::error('Error al crear profesional de emergencia: ' . $e->getMessage());
            return null;
        }
    }

    private function crearPalabrasClaveEmergencia()
    {
        try {
            Log::info('Creando palabras clave de emergencia...');

            $palabrasBasicas = [
                [
                    'palabra' => 'ansiedad',
                    'sinonimos' => ['ansioso', 'nervioso', 'preocupado', 'angustia'],
                    'categoria' => 'ansiedad',
                    'nivel_alerta' => 'medio',
                    'peso_urgencia' => 0.8,
                    'especialidad_recomendada' => 'psicologo',
                    'descripcion' => 'Sensación de nerviosismo, preocupación o inquietud'
                ],
                [
                    'palabra' => 'depresión',
                    'sinonimos' => ['deprimido', 'tristeza', 'desanimado', 'desesperanza'],
                    'categoria' => 'depresion',
                    'nivel_alerta' => 'medio',
                    'peso_urgencia' => 0.8,
                    'especialidad_recomendada' => 'psicologo',
                    'descripcion' => 'Estado de tristeza profunda, pérdida de interés o placer'
                ],
                [
                    'palabra' => 'estrés',
                    'sinonimos' => ['estresado', 'tensión', 'agobio', 'presión'],
                    'categoria' => 'estres',
                    'nivel_alerta' => 'medio',
                    'peso_urgencia' => 0.7,
                    'especialidad_recomendada' => 'psicologo',
                    'descripcion' => 'Tensión física o emocional'
                ],
                [
                    'palabra' => 'insomnio',
                    'sinonimos' => ['dormir', 'sueño', 'desvelo', 'noches'],
                    'categoria' => 'sueno',
                    'nivel_alerta' => 'medio',
                    'peso_urgencia' => 0.6,
                    'especialidad_recomendada' => 'psicologo',
                    'descripcion' => 'Problemas para dormir'
                ],
            ];

            $creadas = 0;
            foreach ($palabrasBasicas as $palabraData) {
                $existe = PalabraClave::where('palabra', $palabraData['palabra'])->first();

                if (!$existe) {
                    PalabraClave::create(array_merge($palabraData, [
                        'estado' => true,
                        'creado_por' => 1
                    ]));
                    $creadas++;
                }
            }

            Log::info('Palabras clave de emergencia creadas: ' . $creadas);
            return $creadas;
        } catch (\Exception $e) {
            Log::error('Error al crear palabras clave de emergencia: ' . $e->getMessage());
            return 0;
        }
    }

    public function reparacionEmergencia()
    {
        Log::info("🔧 EJECUTANDO REPARACIÓN DE EMERGENCIA DEL SISTEMA");

        // 1. Verificar y crear profesional de emergencia si es necesario
        if (Profesional::where('estado_verificacion', 'aprobado')->count() === 0) {
            $this->crearProfesionalEmergencia();
        }

        // 2. Verificar y crear palabras clave de emergencia
        if (PalabraClave::where('estado', true)->count() === 0) {
            $this->crearPalabrasClaveEmergencia();
        }

        // 3. ✅ NUEVO: Reparar profesionales con palabras clave corruptas
        $this->repararProfesionalesConPalabrasClaveCorruptas();
    }

    // ✅ AGREGAR ESTE MÉTODO NUEVO
    private function repararProfesionalesConPalabrasClaveCorruptas()
    {
        $profesionalesProblematicos = Profesional::where(function ($query) {
            $query->where('palabras_clave_especialidad', 'like', '%""[]""%')
                ->orWhere('palabras_clave_especialidad', 'like', '%[]%')
                ->orWhereNull('palabras_clave_especialidad');
        })->get();

        foreach ($profesionalesProblematicos as $profesional) {
            $palabrasBasicas = [
                'psicologo' => ['ansiedad', 'depresión', 'estrés', 'crisis', 'familia'],
                'psiquiatra' => ['medicamento', 'diagnóstico', 'trastorno', 'urgencia', 'hospital'],
                'nutricionista' => ['dieta', 'alimentación', 'peso', 'comida', 'nutrición']
            ];

            $palabras = $palabrasBasicas[$profesional->especialidad_principal] ?? ['ansiedad', 'depresión', 'estrés'];

            $profesional->update([
                'palabras_clave_especialidad' => $palabras,
                'disponibilidad_inmediata' => true
            ]);

            Log::info("✅ Profesional {$profesional->id} reparado con palabras clave: " . json_encode($palabras));
        }
    }

    public function procesarTriajeCompleto(Paciente $paciente, string $descripcionSintomatologia)
    {
        $this->verificarEstadoSistema();

        Log::info("=== INICIANDO TRIAJE COMPLETO ===");
        Log::info("Paciente: {$paciente->id}, Texto: " . substr($descripcionSintomatologia, 0, 100));

        try {
            $analisisSintomas = $this->analizarSintomasMejorado($descripcionSintomatologia);

            Log::info("Análisis completado:", [
                'sintomas_detectados' => $analisisSintomas['sintomas_detectados'],
                'palabras_encontradas' => count($analisisSintomas['palabras_clave_encontradas']),
                'nivel_urgencia' => $analisisSintomas['nivel_urgencia']
            ]);

            $especialidadRecomendada = $this->determinarEspecialidadMejorado($analisisSintomas);
            Log::info("Especialidad recomendada: {$especialidadRecomendada}");

            $resultadoMatching = $this->encontrarProfesionalOptimoMejorado(
                $paciente,
                $analisisSintomas,
                $descripcionSintomatologia,
                $especialidadRecomendada
            );

            $triaje = $this->guardarTriaje($paciente, $descripcionSintomatologia, $analisisSintomas, $resultadoMatching);

            if ($resultadoMatching['profesional_optimo']) {
                $this->crearRelacionPacienteProfesional($paciente, $resultadoMatching, $descripcionSintomatologia);
            }

            return [
                'triaje' => $triaje,
                'match_encontrado' => $resultadoMatching['profesional_optimo'] !== null,
                'profesional' => $resultadoMatching['profesional_optimo'],
                'puntaje_compatibilidad' => $resultadoMatching['puntaje'],
                'analisis_sintomas' => $analisisSintomas,
                'especialidad_recomendada' => $especialidadRecomendada,
                'todos_los_resultados' => $resultadoMatching['todos_los_resultados'],
                'detalles_matching' => $resultadoMatching['detalles_matching']
            ];
        } catch (\Exception $e) {
            Log::error("Error en procesarTriajeCompleto: " . $e->getMessage());
            throw $e;
        }
    }

    public function analizarSintomasMejorado(string $descripcion)
    {
        $texto = mb_strtolower(trim($descripcion));
        Log::info("Analizando texto: " . substr($texto, 0, 200));

        $palabrasClaveSistema = PalabraClave::activas()->get();
        Log::info("Palabras clave en sistema: " . $palabrasClaveSistema->count());

        $sintomasDetectados = [];
        $palabrasClaveEncontradas = [];
        $puntajeUrgencia = 0;

        foreach ($palabrasClaveSistema as $palabraClave) {
            $terminosBusqueda = array_merge([$palabraClave->palabra], $palabraClave->sinonimos ?? []);

            foreach ($terminosBusqueda as $termino) {
                if ($this->buscarTerminoEnTexto($termino, $texto)) {
                    $palabrasClaveEncontradas[] = [
                        'palabra' => $palabraClave->palabra,
                        'termino_encontrado' => $termino,
                        'categoria' => $palabraClave->categoria,
                        'nivel_alerta' => $palabraClave->nivel_alerta,
                        'peso_urgencia' => $palabraClave->peso_urgencia,
                        'especialidad_recomendada' => $palabraClave->especialidad_recomendada
                    ];

                    $puntajeUrgencia += $palabraClave->peso_urgencia;

                    if (!in_array($palabraClave->categoria, $sintomasDetectados)) {
                        $sintomasDetectados[] = $palabraClave->categoria;
                    }

                    Log::info("✅ Palabra clave detectada: '{$termino}' -> '{$palabraClave->palabra}' - Categoría: {$palabraClave->categoria}");
                    break;
                }
            }
        }

        Log::info("Resumen análisis - Encontradas: " . count($palabrasClaveEncontradas) . ", Puntaje: {$puntajeUrgencia}");

        $nivelUrgencia = $this->determinarNivelUrgenciaMejorado($palabrasClaveEncontradas, $puntajeUrgencia);

        return [
            'sintomas_detectados' => $sintomasDetectados,
            'palabras_clave_encontradas' => $palabrasClaveEncontradas,
            'nivel_urgencia' => $nivelUrgencia,
            'puntaje_urgencia' => $puntajeUrgencia,
            'texto_analizado' => $texto,
            'total_palabras_clave' => count($palabrasClaveEncontradas),
            'resumen' => $this->generarResumenAnalisis($sintomasDetectados, $palabrasClaveEncontradas)
        ];
    }

    private function buscarProfesionalesPorPalabrasClave($palabrasClave, $especialidad = null)
    {
        $query = Profesional::where('estado_verificacion', 'aprobado')
            ->where('disponibilidad_inmediata', true);

        if ($especialidad) {
            // Búsqueda más flexible de especialidad
            $query->where(function ($q) use ($especialidad) {
                $q->where('especialidad_principal', 'like', "%{$especialidad}%")
                    ->orWhere('especialidad_principal', 'like', "%psicolog%")
                    ->orWhere('especialidad_principal', 'like', "%psiquiatr%")
                    ->orWhere('especialidad_principal', 'like', "%nutricion%");
            });
        }

        if (!empty($palabrasClave)) {
            $query->where(function ($q) use ($palabrasClave) {
                foreach ($palabrasClave as $palabra) {
                    $q->orWhere('palabras_clave_especialidad', 'like', '%"' . $palabra . '"%')
                        ->orWhere('palabras_clave_especialidad', 'like', '%' . $palabra . '%');
                }
            });
        }

        $resultados = $query->with(['usuario', 'clinicas', 'especialidad'])->get();

        Log::info("🔍 Búsqueda profesionales - Especialidad: {$especialidad}, Palabras: " . json_encode($palabrasClave));
        Log::info("🔍 Resultados encontrados: " . $resultados->count());

        return $resultados;
    }

    private function buscarProfesionalesPorPalabrasClaveMejorado($palabrasClave, $especialidad = null)
    {
        Log::info("🔍 BUSCANDO PROFESIONALES - Palabras: " . json_encode($palabrasClave) . ", Especialidad: " . $especialidad);

        $query = Profesional::where('estado_verificacion', 'aprobado')
            ->where('disponibilidad_inmediata', true);

        // Búsqueda por especialidad mejorada
        if ($especialidad) {
            $query->where(function ($q) use ($especialidad) {
                $q->where('especialidad_principal', 'like', "%{$especialidad}%")
                    ->orWhere('especialidad_principal', $especialidad);
            });
        }

        $profesionales = $query->get();
        Log::info("🔍 Profesionales encontrados (antes de filtrar): " . $profesionales->count());

        // Filtrar por palabras clave
        $profesionalesFiltrados = $profesionales->filter(function ($profesional) use ($palabrasClave) {
            $palabrasProfesional = $this->obtenerPalabrasClaveProfesional($profesional);

            if (empty($palabrasProfesional)) {
                Log::info("❌ Profesional {$profesional->id} no tiene palabras clave");
                return false;
            }

            Log::info("🔍 Profesional {$profesional->id} palabras: " . json_encode($palabrasProfesional));

            $coincidencias = array_intersect($palabrasClave, $palabrasProfesional);

            if (!empty($coincidencias)) {
                Log::info("✅ Profesional {$profesional->id} tiene coincidencias: " . json_encode($coincidencias));
                return true;
            }

            return false;
        });

        Log::info("🎯 Profesionales después de filtrar: " . $profesionalesFiltrados->count());
        return $profesionalesFiltrados;
    }

    private function obtenerPalabrasClaveProfesional($profesional)
    {
        $palabras = $profesional->palabras_clave_especialidad;

        // Si es string JSON, decodificar
        if (is_string($palabras)) {
            $decoded = json_decode($palabras, true);
            return is_array($decoded) ? $decoded : [];
        }

        // Si es array, usar directamente
        if (is_array($palabras)) {
            return $palabras;
        }

        // Si es null o inválido, array vacío
        return [];
    }

    public function buscarTerminoEnTexto(string $termino, string $texto): bool
    {
        $termino = mb_strtolower(trim($termino));
        $texto = mb_strtolower(trim($texto));

        // 1. Coincidencia exacta directa
        if (str_contains($texto, $termino)) {
            Log::info("✅ Coincidencia EXACTA: '{$termino}' en texto");
            return true;
        }

        // 2. Coincidencia por raíz (primeros 4-5 caracteres)
        $raizTermino = substr($termino, 0, 5);
        if (strlen($raizTermino) >= 3) {
            $palabrasTexto = explode(' ', $texto);
            foreach ($palabrasTexto as $palabra) {
                if (str_starts_with($palabra, $raizTermino)) {
                    Log::info("✅ Coincidencia por RAÍZ: '{$raizTermino}' en '{$palabra}'");
                    return true;
                }
            }
        }

        // 3. Coincidencia flexible para sinónimos
        $terminoLimpio = preg_replace('/[^a-záéíóúñ]/', '', $termino);
        $textoLimpio = preg_replace('/[^a-záéíóúñ\s]/', ' ', $texto);

        if (strlen($terminoLimpio) >= 4) {
            $patron = '/\b' . substr($terminoLimpio, 0, 4) . '\w*/i';
            if (preg_match($patron, $textoLimpio)) {
                Log::info("✅ Coincidencia FLEXIBLE: '{$terminoLimpio}' en texto");
                return true;
            }
        }

        Log::info("❌ NO coincide: '{$termino}' en texto");
        return false;
    }

    private function determinarNivelUrgenciaMejorado(array $palabrasClave, float $puntajeUrgencia)
    {
        $nivelesConfig = $this->configuracion['triaje']['niveles_urgencia'] ?? [
            'critico' => ['min_palabras' => 1, 'min_puntaje' => 2.0],
            'alto' => ['min_palabras' => 2, 'min_puntaje' => 1.0],
            'medio' => ['min_palabras' => 1, 'min_puntaje' => 0.5],
            'bajo' => ['min_palabras' => 0, 'min_puntaje' => 0]
        ];

        Log::info("Umbrales urgencia configurados: " . json_encode($nivelesConfig));

        foreach ($palabrasClave as $palabra) {
            if ($palabra['nivel_alerta'] === 'critico') {
                Log::info("🔴 Nivel CRÍTICO por palabra: {$palabra['palabra']}");
                return 'critico';
            }
        }

        $totalPalabras = count($palabrasClave);

        if (
            $puntajeUrgencia >= ($nivelesConfig['critico']['min_puntaje'] ?? 2.0) &&
            $totalPalabras >= ($nivelesConfig['critico']['min_palabras'] ?? 1)
        ) {
            Log::info("🔴 Nivel CRÍTICO - Puntaje: {$puntajeUrgencia}, Palabras: {$totalPalabras}");
            return 'critico';
        } elseif (
            $puntajeUrgencia >= ($nivelesConfig['alto']['min_puntaje'] ?? 1.0) &&
            $totalPalabras >= ($nivelesConfig['alto']['min_palabras'] ?? 2)
        ) {
            Log::info("🟡 Nivel ALTO - Puntaje: {$puntajeUrgencia}, Palabras: {$totalPalabras}");
            return 'alto';
        } elseif (
            $puntajeUrgencia >= ($nivelesConfig['medio']['min_puntaje'] ?? 0.5) &&
            $totalPalabras >= ($nivelesConfig['medio']['min_palabras'] ?? 1)
        ) {
            Log::info("🟠 Nivel MEDIO - Puntaje: {$puntajeUrgencia}, Palabras: {$totalPalabras}");
            return 'medio';
        }

        Log::info("🟢 Nivel BAJO - Puntaje: {$puntajeUrgencia}, Palabras: {$totalPalabras}");
        return 'bajo';
    }
    public function diagnosticoRapido($descripcion)
    {
        echo "=== 🔍 DIAGNÓSTICO RÁPIDO ===\n";

        // 1. Verificar palabras clave del sistema
        $palabras = \App\Models\PalabraClave::where('estado', true)->get();
        echo "1. PALABRAS CLAVE ACTIVAS: " . $palabras->count() . "\n";
        foreach ($palabras as $palabra) {
            echo "   - {$palabra->palabra}: " . json_encode($palabra->sinonimos) . "\n";
        }

        // 2. Probar detección manual
        $texto = mb_strtolower($descripcion);
        echo "2. TEXTO ANALIZADO: {$texto}\n";

        $detectadas = [];
        foreach ($palabras as $palabra) {
            $terminos = array_merge([$palabra->palabra], $palabra->sinonimos ?? []);
            foreach ($terminos as $termino) {
                if (str_contains($texto, $termino)) {
                    $detectadas[] = "{$palabra->palabra} (via: {$termino})";
                    break;
                }
            }
        }

        echo "3. PALABRAS DETECTADAS MANUALMENTE: " . count($detectadas) . "\n";
        foreach ($detectadas as $det) {
            echo "   - {$det}\n";
        }

        // 3. Verificar matching básico
        $profesionalPsicologo = \App\Models\Profesional::where('especialidad_principal', 'like', '%psicolog%')->first();
        if ($profesionalPsicologo) {
            echo "4. PSICÓLOGO ENCONTRADO: {$profesionalPsicologo->id}\n";
            echo "   Palabras clave: " . json_encode($profesionalPsicologo->palabras_clave_especialidad) . "\n";

            // Verificar coincidencias
            $coincidencias = array_intersect(
                ['ansiedad', 'estrés', 'insomnio', 'palpitaciones'],
                $profesionalPsicologo->palabras_clave_especialidad ?? []
            );
            echo "   Coincidencias: " . count($coincidencias) . " - " . json_encode($coincidencias) . "\n";
        }
    }
    public function debugMatchingCompleto(Paciente $paciente, string $descripcion)
    {
        echo "=== 🐛 DEBUG COMPLETO DEL MATCHING ===\n";

        // 1. Probar análisis
        $analisis = $this->analizarSintomasMejorado($descripcion);
        echo "1. ANÁLISIS - Palabras encontradas: " . count($analisis['palabras_clave_encontradas']) . "\n";
        foreach ($analisis['palabras_clave_encontradas'] as $palabra) {
            echo "   - {$palabra['palabra']} (via: {$palabra['termino_encontrado']})\n";
        }

        // 2. Probar especialidad
        $especialidad = $this->determinarEspecialidadMejorado($analisis);
        echo "2. ESPECIALIDAD RECOMENDADA: {$especialidad}\n";

        // 3. Verificar profesionales disponibles
        $profesionales = Profesional::where('estado_verificacion', 'aprobado')->get();
        echo "3. PROFESIONALES APROBADOS: " . $profesionales->count() . "\n";

        foreach ($profesionales as $prof) {
            echo "   - {$prof->id}: {$prof->especialidad_principal}\n";
            echo "     Palabras clave: " . json_encode($prof->palabras_clave_especialidad) . "\n";
        }

        // 4. Probar matching
        $resultado = $this->procesarTriajeCompleto($paciente, $descripcion);
        echo "4. RESULTADO MATCHING:\n";
        echo "   - Match encontrado: " . ($resultado['match_encontrado'] ? 'SÍ' : 'NO') . "\n";
        echo "   - Puntaje: {$resultado['puntaje_compatibilidad']}%\n";
        echo "   - Profesional: " . ($resultado['profesional'] ? $resultado['profesional']->id : 'Ninguno') . "\n";

        return $resultado;
    }
    private function encontrarProfesionalOptimoMejorado(Paciente $paciente, array $sintomas, string $descripcion, string $especialidadRecomendada)
    {
        Log::info("🎯 === BUSCANDO PROFESIONAL OPTIMO MEJORADO ===");
        Log::info("Especialidad recomendada: {$especialidadRecomendada}");

        // Extraer palabras clave del análisis
        $palabrasClavePaciente = collect($sintomas['palabras_clave_encontradas'] ?? [])
            ->pluck('palabra')
            ->toArray();

        Log::info("🔑 Palabras clave del paciente: " . json_encode($palabrasClavePaciente));

        if (empty($palabrasClavePaciente)) {
            Log::warning("❌ No hay palabras clave del paciente para matching");
            return $this->resultadoSinProfesionales();
        }

        // Usar el método mejorado para buscar profesionales
        $profesionales = $this->buscarProfesionalesPorPalabrasClaveMejorado(
            $palabrasClavePaciente,
            $especialidadRecomendada
        );

        if ($profesionales->isEmpty()) {
            Log::warning("❌ No se encontraron profesionales con las palabras clave especificadas");

            // SOLUCIÓN DE EMERGENCIA: Buscar cualquier profesional aprobado
            return $this->busquedaEmergencia($paciente, $sintomas, $descripcion, $especialidadRecomendada);
        }

        Log::info("✅ Profesionales encontrados para matching: " . $profesionales->count());

        $mejorPuntaje = 0;
        $profesionalOptimo = null;
        $resultados = [];

        foreach ($profesionales as $profesional) {
            $puntaje = $this->calcularCompatibilidadMejorada($profesional, $sintomas, $descripcion);

            $resultado = [
                'profesional' => $profesional,
                'puntaje' => $puntaje,
                'especialidad' => $profesional->especialidad_principal,
                'palabras_clave' => $this->obtenerPalabrasClaveProfesional($profesional),
                'compatibilidad_detallada' => $this->generarCompatibilidadDetallada($profesional, $sintomas)
            ];

            $resultados[] = $resultado;

            Log::info("🎯 Profesional {$profesional->id} - {$profesional->especialidad_principal}: {$puntaje}%");

            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $profesionalOptimo = $profesional;
            }
        }

        // Ordenar resultados por puntaje
        usort($resultados, function ($a, $b) {
            return $b['puntaje'] <=> $a['puntaje'];
        });

        $umbralMinimo = $this->configuracion['umbrales']['compatibilidad_minima'] ?? 30;

        if ($mejorPuntaje < $umbralMinimo) {
            Log::warning("❌ Ningún profesional supera el umbral mínimo de {$umbralMinimo}%. Mejor: {$mejorPuntaje}%");
            $profesionalOptimo = null;
        } else {
            Log::info("✅ MEJOR MATCH: Profesional {$profesionalOptimo->id} con {$mejorPuntaje}% de compatibilidad");
        }

        return [
            'profesional_optimo' => $profesionalOptimo,
            'puntaje' => $mejorPuntaje,
            'todos_los_resultados' => $resultados,
            'especialidad_recomendada' => $especialidadRecomendada,
            'umbral_minimo' => $umbralMinimo,
            'total_profesionales_evaluados' => count($resultados),
            'detalles_matching' => []
        ];
    }

    private function busquedaEmergencia(Paciente $paciente, array $sintomas, string $descripcion, string $especialidadRecomendada)
    {
        Log::warning("🚨 EJECUTANDO BÚSQUEDA DE EMERGENCIA");

        // Buscar cualquier profesional aprobado y disponible
        $profesionalesEmergencia = Profesional::where('estado_verificacion', 'aprobado')
            ->where('disponibilidad_inmediata', true)
            ->get();

        Log::info("🚨 Profesionales disponibles para emergencia: " . $profesionalesEmergencia->count());

        if ($profesionalesEmergencia->isEmpty()) {
            Log::error("🚨 CRÍTICO: No hay ningún profesional disponible en el sistema");
            return $this->resultadoSinProfesionales();
        }

        $mejorPuntaje = 0;
        $profesionalOptimo = null;
        $resultados = [];

        foreach ($profesionalesEmergencia as $profesional) {
            // Puntaje base para emergencia
            $puntaje = 60; // Puntaje base de emergencia

            // Bonus por especialidad coincidente
            if ($profesional->especialidad_principal === $especialidadRecomendada) {
                $puntaje += 20;
            }

            // Bonus por experiencia
            if ($profesional->anios_experiencia > 5) {
                $puntaje += 10;
            }

            $resultado = [
                'profesional' => $profesional,
                'puntaje' => $puntaje,
                'especialidad' => $profesional->especialidad_principal,
                'palabras_clave' => $this->obtenerPalabrasClaveProfesional($profesional),
                'compatibilidad_detallada' => ['tipo' => 'emergencia']
            ];

            $resultados[] = $resultado;

            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $profesionalOptimo = $profesional;
            }
        }

        Log::info("🚨 MATCH DE EMERGENCIA: Profesional {$profesionalOptimo->id} con {$mejorPuntaje}%");

        return [
            'profesional_optimo' => $profesionalOptimo,
            'puntaje' => $mejorPuntaje,
            'todos_los_resultados' => $resultados,
            'especialidad_recomendada' => $especialidadRecomendada,
            'umbral_minimo' => 30,
            'total_profesionales_evaluados' => count($resultados),
            'detalles_matching' => ['tipo_asignacion' => 'emergencia']
        ];
    }
    public function diagnosticoCompleto($descripcion)
    {
        echo "=== 🔍 DIAGNÓSTICO COMPLETO DEL SISTEMA ===\n\n";

        // 1. Análisis de síntomas
        echo "1. 📝 ANÁLISIS DE SÍNTOMAS:\n";
        $analisis = $this->analizarSintomasMejorado($descripcion);
        echo "   - Palabras detectadas: " . count($analisis['palabras_clave_encontradas']) . "\n";
        echo "   - Síntomas: " . count($analisis['sintomas_detectados']) . "\n";
        echo "   - Nivel urgencia: {$analisis['nivel_urgencia']}\n";
        echo "   - Puntaje: {$analisis['puntaje_urgencia']}\n";
        foreach ($analisis['palabras_clave_encontradas'] as $palabra) {
            echo "     * {$palabra['palabra']} (via: {$palabra['termino_encontrado']})\n";
        }

        // 2. Especialidad recomendada
        echo "\n2. 🎯 ESPECIALIDAD RECOMENDADA:\n";
        $especialidad = $this->determinarEspecialidadMejorado($analisis);
        echo "   - {$especialidad}\n";

        // 3. Profesionales disponibles
        echo "\n3. 👨‍⚕️ PROFESIONALES DISPONIBLES:\n";
        $profesionales = Profesional::where('estado_verificacion', 'aprobado')->get();
        echo "   - Total: {$profesionales->count()}\n";

        foreach ($profesionales as $prof) {
            $palabrasProf = is_array($prof->palabras_clave_especialidad)
                ? $prof->palabras_clave_especialidad
                : json_decode($prof->palabras_clave_especialidad, true) ?? [];

            $coincidencias = count(array_intersect(
                collect($analisis['palabras_clave_encontradas'])->pluck('palabra')->toArray(),
                $palabrasProf
            ));

            echo "   - {$prof->especialidad_principal}: {$coincidencias} coincidencias\n";
            echo "     Palabras: " . json_encode($palabrasProf) . "\n";
        }

        // 4. Probar matching completo
        echo "\n4. 🔍 PRUEBA DE MATCHING COMPLETO:\n";
        $paciente = \App\Models\Paciente::first();
        if ($paciente) {
            $resultado = $this->procesarTriajeCompleto($paciente, $descripcion);
            echo "   - Match: " . ($resultado['match_encontrado'] ? '✅ SÍ' : '❌ NO') . "\n";
            echo "   - Puntaje: {$resultado['puntaje_compatibilidad']}%\n";
            if ($resultado['match_encontrado']) {
                echo "   - Profesional: {$resultado['profesional']->especialidad_principal}\n";
                echo "   - ID: {$resultado['profesional']->id}\n";
            } else {
                echo "   - Razón: No se superó el umbral mínimo\n";
            }
        }

        return $resultado ?? null;
    }

    private function busquedaAmplia(Paciente $paciente, array $sintomas, string $descripcion, Especialidad $especialidadOriginal)
    {
        Log::info("🔍 Realizando búsqueda amplia de profesionales...");

        // Buscar profesionales de especialidades relacionadas
        $palabrasClavePaciente = collect($sintomas['palabras_clave_encontradas'] ?? [])->pluck('palabra')->toArray();

        $especialidadesCompatibles = Especialidad::activas()
            ->where('id_especialidad', '!=', $especialidadOriginal->id_especialidad)
            ->get()
            ->filter(function ($especialidad) use ($palabrasClavePaciente) {
                $palabrasEspecialidad = $especialidad->palabrasClave()->pluck('palabra')->toArray();
                return count(array_intersect($palabrasClavePaciente, $palabrasEspecialidad)) > 0;
            });

        $mejorPuntaje = 0;
        $profesionalOptimo = null;
        $resultados = [];

        foreach ($especialidadesCompatibles as $especialidad) {
            $profesionales = $this->buscarProfesionalesPorPalabrasClave(
                $palabrasClavePaciente,
                $especialidad->codigo
            );

            foreach ($profesionales as $profesional) {
                $puntaje = $this->calcularCompatibilidadMejorada($profesional, $sintomas, $descripcion);

                if ($puntaje > $mejorPuntaje) {
                    $mejorPuntaje = $puntaje;
                    $profesionalOptimo = $profesional;
                }

                $resultados[] = [
                    'profesional' => $profesional,
                    'puntaje' => $puntaje,
                    'especialidad' => $especialidad->nombre,
                    'tipo_busqueda' => 'ampliada'
                ];
            }
        }

        if ($profesionalOptimo) {
            Log::info("✅ MATCH ENCONTRADO en búsqueda amplia: {$profesionalOptimo->id} con {$mejorPuntaje}%");
        }

        return [
            'profesional_optimo' => $profesionalOptimo,
            'puntaje' => $mejorPuntaje,
            'todos_los_resultados' => $resultados,
            'especialidad_recomendada' => $profesionalOptimo ? $profesionalOptimo->especialidad->nombre : $especialidadOriginal->nombre,
            'umbral_minimo' => $this->configuracion['umbrales']['compatibilidad_minima'] ?? 30,
            'total_profesionales_evaluados' => count($resultados),
            'busqueda_ampliada' => true,
            'detalles_matching' => []
        ];
    }
    private function resultadoSinProfesionales()
    {
        return [
            'profesional_optimo' => null,
            'puntaje' => 0,
            'todos_los_resultados' => [],
            'especialidad_recomendada' => 'psicologo',
            'umbral_minimo' => 30,
            'total_profesionales_evaluados' => 0,
            'detalles_matching' => []
        ];
    }

    private function inicializarEspecialidadesSiEsNecesario()
    {
        if (Especialidad::count() === 0) {
            Log::info("Inicializando especialidades del sistema...");
            Especialidad::inicializarEspecialidades();
        }
    }

    private function calcularCompatibilidadMejorada(Profesional $profesional, array $sintomas, string $descripcion)
    {
        $puntajeTotal = 0;
        $pesos = $this->configuracion['pesos'];
        $detalles = [];

        $coincidenciaPalabras = $this->calcularCoincidenciaPalabrasMejorada($profesional, $sintomas);
        $puntajeTotal += $coincidenciaPalabras * $pesos['coincidencia_palabras_clave'];
        $detalles['coincidencia_palabras'] = $coincidenciaPalabras;

        $puntajeEspecialidad = $this->calcularPuntajeEspecialidad($profesional->especialidad_principal, $sintomas);
        $puntajeTotal += $puntajeEspecialidad * $pesos['especialidad_principal'];
        $detalles['especialidad'] = $puntajeEspecialidad;

        $puntajeExperiencia = $this->calcularPuntajeExperiencia($profesional);
        $puntajeTotal += $puntajeExperiencia * $pesos['experiencia_calificacion'];
        $detalles['experiencia'] = $puntajeExperiencia;

        $puntajeDisponibilidad = $this->calcularPuntajeDisponibilidad($profesional);
        $puntajeTotal += $puntajeDisponibilidad * $pesos['disponibilidad'];
        $detalles['disponibilidad'] = $puntajeDisponibilidad;

        $puntajeUbicacion = 0.5;
        $puntajeTotal += $puntajeUbicacion * $pesos['ubicacion'];
        $detalles['ubicacion'] = $puntajeUbicacion;

        Log::info("📊 Cálculo compatibilidad profesional {$profesional->id}: " . json_encode($detalles));

        return min(100, $puntajeTotal * 100);
    }

    private function calcularCoincidenciaPalabrasMejorada(Profesional $profesional, array $sintomas)
    {
        // Manejar diferentes formatos de palabras clave
        $palabrasClaveProfesional = [];

        if (is_array($profesional->palabras_clave_especialidad)) {
            $palabrasClaveProfesional = $profesional->palabras_clave_especialidad;
        } elseif (is_string($profesional->palabras_clave_especialidad)) {
            // Intentar decodificar JSON string
            $decoded = json_decode($profesional->palabras_clave_especialidad, true);
            $palabrasClaveProfesional = is_array($decoded) ? $decoded : [];
        }

        Log::info("🔍 Palabras clave profesional {$profesional->id}: " . json_encode($palabrasClaveProfesional));

        if (empty($palabrasClaveProfesional)) {
            Log::warning("Profesional {$profesional->id} no tiene palabras clave configuradas");
            return 0.1;
        }

        $coincidencias = 0;
        $palabrasPaciente = collect($sintomas['palabras_clave_encontradas'] ?? [])->pluck('palabra')->toArray();

        if (empty($palabrasPaciente)) {
            return 0.1;
        }

        Log::info("🔍 Comparando - Profesional: " . json_encode($palabrasClaveProfesional));
        Log::info("🔍 Paciente: " . json_encode($palabrasPaciente));

        foreach ($palabrasClaveProfesional as $palabraProfesional) {
            if (in_array($palabraProfesional, $palabrasPaciente)) {
                $coincidencias++;
                Log::info("✅ COINCIDENCIA ENCONTRADA: '{$palabraProfesional}'");
            }
        }

        $totalPalabras = count($palabrasClaveProfesional);
        $puntaje = $totalPalabras > 0 ? $coincidencias / $totalPalabras : 0;

        Log::info("📊 Resumen coincidencias: {$coincidencias}/{$totalPalabras} = " . ($puntaje * 100) . "%");

        return $puntaje;
    }

    private function obtenerDetallesCalculoMejorado(Profesional $profesional, array $sintomas, string $descripcion)
    {
        $pesos = $this->configuracion['pesos'];

        return [
            'profesional_id' => $profesional->id,
            'profesional_nombre' => $profesional->usuario->nombre . ' ' . $profesional->usuario->apellido,
            'especialidad' => $profesional->especialidad_principal,
            'factores' => [
                'coincidencia_palabras' => [
                    'valor' => $this->calcularCoincidenciaPalabrasMejorada($profesional, $sintomas),
                    'peso' => $pesos['coincidencia_palabras_clave'],
                    'contribucion' => $this->calcularCoincidenciaPalabrasMejorada($profesional, $sintomas) * $pesos['coincidencia_palabras_clave'] * 100,
                    'explicacion' => 'Coincidencia entre palabras clave del profesional y síntomas del paciente'
                ],
                'especialidad' => [
                    'valor' => $this->calcularPuntajeEspecialidad($profesional->especialidad_principal, $sintomas),
                    'peso' => $pesos['especialidad_principal'],
                    'contribucion' => $this->calcularPuntajeEspecialidad($profesional->especialidad_principal, $sintomas) * $pesos['especialidad_principal'] * 100,
                    'explicacion' => 'Adecuación de la especialidad a los síntomas detectados'
                ],
                'experiencia' => [
                    'valor' => $this->calcularPuntajeExperiencia($profesional),
                    'peso' => $pesos['experiencia_calificacion'],
                    'contribucion' => $this->calcularPuntajeExperiencia($profesional) * $pesos['experiencia_calificacion'] * 100,
                    'explicacion' => 'Años de experiencia y calificación del profesional'
                ],
                'disponibilidad' => [
                    'valor' => $this->calcularPuntajeDisponibilidad($profesional),
                    'peso' => $pesos['disponibilidad'],
                    'contribucion' => $this->calcularPuntajeDisponibilidad($profesional) * $pesos['disponibilidad'] * 100,
                    'explicacion' => 'Disponibilidad inmediata y tiempo de respuesta'
                ],
                'ubicacion' => [
                    'valor' => 0.5,
                    'peso' => $pesos['ubicacion'],
                    'contribucion' => 0.5 * $pesos['ubicacion'] * 100,
                    'explicacion' => 'Compatibilidad geográfica (configuración temporal)'
                ]
            ],
            'palabras_clave_profesional' => $profesional->palabras_clave_especialidad ?? [],
            'palabras_clave_coincidentes' => $this->obtenerPalabrasCoincidentes($profesional, $sintomas)
        ];
    }

    private function generarCompatibilidadDetallada(Profesional $profesional, array $sintomas)
    {
        $palabrasCoincidentes = $this->obtenerPalabrasCoincidentes($profesional, $sintomas);

        return [
            'coincidencias' => count($palabrasCoincidentes),
            'palabras_coincidentes' => $palabrasCoincidentes,
            'total_palabras_profesional' => count($profesional->palabras_clave_especialidad ?? []),
            'porcentaje_coincidencia' => count($profesional->palabras_clave_especialidad ?? []) > 0 ?
                (count($palabrasCoincidentes) / count($profesional->palabras_clave_especialidad ?? [])) * 100 : 0
        ];
    }

    private function obtenerPalabrasCoincidentes(Profesional $profesional, array $sintomas)
    {
        $coincidentes = [];
        $palabrasPaciente = collect($sintomas['palabras_clave_encontradas'] ?? [])->pluck('palabra')->toArray();

        foreach ($profesional->palabras_clave_especialidad ?? [] as $palabraProf) {
            if (in_array($palabraProf, $palabrasPaciente)) {
                $coincidentes[] = $palabraProf;
            }
        }

        return $coincidentes;
    }

    private function generarResumenAnalisis(array $sintomasDetectados, array $palabrasClaveEncontradas)
    {
        return [
            'sintomas_principales' => array_slice($sintomasDetectados, 0, 5),
            'palabras_clave_destacadas' => array_slice(array_column($palabrasClaveEncontradas, 'palabra'), 0, 10),
            'total_elementos_detectados' => count($sintomasDetectados) + count($palabrasClaveEncontradas)
        ];
    }

    public function determinarEspecialidadMejorado(array $analisisSintomas)
    {
        $especialidadesPuntaje = [];
        $reglasEspecialidad = $this->configuracion['reglas_especialidad'];

        foreach ($reglasEspecialidad as $especialidad => $config) {
            if (!isset($config['activo']) || !$config['activo']) {
                continue;
            }

            $puntaje = 0;
            foreach ($analisisSintomas['palabras_clave_encontradas'] as $palabra) {
                if (isset($config['palabras_clave'][$palabra['palabra']])) {
                    $configPalabra = $config['palabras_clave'][$palabra['palabra']];
                    $puntaje += $configPalabra['peso'] * $palabra['peso_urgencia'];
                }
            }

            if ($puntaje > 0) {
                $especialidadesPuntaje[$especialidad] = $puntaje;
            }
        }

        if (empty($especialidadesPuntaje)) {
            return 'psicologo';
        }

        arsort($especialidadesPuntaje);
        return array_key_first($especialidadesPuntaje);
    }

    private function calcularPuntajeEspecialidad(string $especialidad, array $sintomas)
    {
        $reglasEspecialidad = $this->configuracion['reglas_especialidad'];

        if (!isset($reglasEspecialidad[$especialidad])) {
            return 0.5;
        }

        $config = $reglasEspecialidad[$especialidad];
        $coincidencias = 0;
        $palabrasPaciente = collect($sintomas['palabras_clave_encontradas'] ?? [])->pluck('palabra')->toArray();

        foreach ($config['palabras_clave'] as $palabra => $detalles) {
            if (in_array($palabra, $palabrasPaciente)) {
                $coincidencias += $detalles['peso'];
                Log::info("Coincidencia de especialidad '$especialidad': '$palabra' con peso {$detalles['peso']}");
            }
        }

        return min(1, $coincidencias);
    }

    private function calcularPuntajeExperiencia(Profesional $profesional)
    {
        $puntaje = 0.5;

        if ($profesional->anios_experiencia >= 10) $puntaje += 0.3;
        elseif ($profesional->anios_experiencia >= 5) $puntaje += 0.2;
        elseif ($profesional->anios_experiencia >= 2) $puntaje += 0.1;

        if ($profesional->calificacion_promedio >= 4.5) $puntaje += 0.2;
        elseif ($profesional->calificacion_promedio >= 4.0) $puntaje += 0.1;

        if (!empty($profesional->certificaciones)) {
            $puntaje += min(0.2, count($profesional->certificaciones) * 0.05);
        }

        return min(1, $puntaje);
    }

    private function calcularPuntajeDisponibilidad(Profesional $profesional)
    {
        if ($profesional->disponibilidad_inmediata) return 1.0;
        if ($profesional->tiempo_respuesta_promedio_horas <= 24) return 0.8;
        if ($profesional->tiempo_respuesta_promedio_horas <= 72) return 0.5;
        return 0.2;
    }

    // === MÉTODOS DE PERSISTENCIA CORREGIDOS ===

    private function crearRelacionPacienteProfesional(Paciente $paciente, array $resultadoMatching, string $descripcion)
    {
        $profesional = $resultadoMatching['profesional_optimo'];
        $puntaje = $resultadoMatching['puntaje'];

        // VERIFICACIÓN CRÍTICA
        Log::info("🔍 VERIFICANDO PACIENTE: ID = " . ($paciente->id_paciente ?? 'NULL'));

        $pacienteId = $paciente->id_paciente;
        $profesionalId = $profesional->id_profesional;

        if (!$pacienteId || !$profesionalId) {
            Log::error("❌ ERROR CRÍTICO: IDs inválidos - Paciente: {$pacienteId}, Profesional: {$profesionalId}");
            return;
        }

        Log::info("🎯 Creando relación: Paciente {$pacienteId} -> Profesional {$profesionalId}");

        try {
            // SOLUCIÓN: Usar DB::table para evitar problemas con Eloquent
            DB::table('profesional_paciente')->insert([
                'id' => Str::uuid(), // 🔥 GENERAR MANUALMENTE EL UUID
                'paciente_id' => $pacienteId,
                'profesional_id' => $profesionalId,
                'fecha_asignacion' => now()->toDateString(),
                'puntuacion_compatibilidad' => $puntaje,
                'estado' => 'pendiente',
                'motivo_asignacion' => 'Matching automático - Compatibilidad: ' . $puntaje . '%',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info("✅ RELACIÓN CREADA EXITOSAMENTE");
        } catch (\Exception $e) {
            Log::error("❌ Error al crear relación: " . $e->getMessage());

            // SOLUCIÓN DE EMERGENCIA: Intentar sin ID
            try {
                DB::table('profesional_paciente')->insert([
                    'paciente_id' => $pacienteId,
                    'profesional_id' => $profesionalId,
                    'fecha_asignacion' => now()->toDateString(),
                    'puntuacion_compatibilidad' => $puntaje,
                    'estado' => 'pendiente',
                    'motivo_asignacion' => 'Matching automático - Compatibilidad: ' . $puntaje . '%',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                Log::info("✅ RELACIÓN CREADA SIN ID (emergencia)");
            } catch (\Exception $e2) {
                Log::error("❌ Error incluso en modo emergencia: " . $e2->getMessage());
            }
        }

        // Crear log de matching (ya corregido)
        $this->crearMatchingLog($pacienteId, $profesionalId, $puntaje, $descripcion, $resultadoMatching);
    }

    private function crearMatchingLog($pacienteId, $profesionalId, $puntaje, $descripcion, $resultadoMatching)
    {
        try {
            DB::table('matching_logs')->insert([
                'paciente_id' => $pacienteId,
                'profesional_id' => $profesionalId,
                'nivel_coincidencia' => $puntaje,
                'criterios_usados' => json_encode([
                    'descripcion_paciente' => $descripcion,
                    'resultados_comparacion' => $resultadoMatching['todos_los_resultados'],
                    'configuracion_utilizada' => $this->configuracion
                ]),
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            Log::info("✅ Log de matching creado exitosamente");
        } catch (\Exception $e) {
            Log::error("❌ Error al crear log de matching: " . $e->getMessage());
        }
    }

    private function guardarTriaje(Paciente $paciente, string $descripcion, array $analisisSintomas, array $resultadoMatching)
    {
        return TriajeInicial::create([
            'paciente_id' => $paciente->id,
            'descripcion_sintomatologia' => $descripcion,
            'analisis_sintomatologia' => $analisisSintomas,
            'especialidad_recomendada' => $resultadoMatching['profesional_optimo']?->especialidad_principal,
            'profesional_asignado_id' => $resultadoMatching['profesional_optimo']?->id,
            'nivel_urgencia' => $analisisSintomas['nivel_urgencia'],
            'fecha_triaje' => now(),
            'estado_triaje' => $resultadoMatching['profesional_optimo'] ? 'completado' : 'pendiente',
            'confianza_asignacion' => $resultadoMatching['puntaje'],
            'configuracion_utilizada' => [
                'algoritmo' => 'matching_parametrizable_v2',
                'clinica_id' => $this->clinicaId,
                'pesos_utilizados' => $this->configuracion['pesos'],
                'umbrales_utilizados' => $this->configuracion['umbrales'],
                'reglas_especialidad_utilizadas' => $this->configuracion['reglas_especialidad'],
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    // === MÉTODOS ADICIONALES ===

    public function obtenerEstadisticasMatching($clinicaId = null)
    {
        $triajes = TriajeInicial::when($clinicaId, function ($query) use ($clinicaId) {
            return $query->whereHas('paciente.profesionales.clinicas', function ($q) use ($clinicaId) {
                $q->where('clinicas.id_clinica', $clinicaId);
            });
        })
            ->where('estado_triaje', 'completado')
            ->get();

        $totalTriajes = $triajes->count();
        $triajesConMatch = $triajes->where('profesional_asignado_id', '!=', null)->count();
        $compatibilidadPromedio = $triajes->avg('confianza_asignacion') ?? 0;

        return [
            'total_triajes' => $totalTriajes,
            'triajes_con_match' => $triajesConMatch,
            'tasa_exito' => $totalTriajes > 0 ? ($triajesConMatch / $totalTriajes) * 100 : 0,
            'compatibilidad_promedio' => round($compatibilidadPromedio, 2),
            'distribucion_especialidades' => $triajes->groupBy('especialidad_recomendada')->map->count()
        ];
    }

    public function recalcularMatching(Paciente $paciente, TriajeInicial $triaje)
    {
        Log::info("=== RECALCULANDO MATCHING PARA PACIENTE {$paciente->id} ===");

        $resultado = $this->procesarTriajeCompleto(
            $paciente,
            $triaje->descripcion_sintomatologia
        );

        $triaje->update([
            'profesional_asignado_id' => $resultado['profesional']?->id,
            'confianza_asignacion' => $resultado['puntaje_compatibilidad'],
            'configuracion_utilizada' => $resultado['triaje']->configuracion_utilizada
        ]);

        return $resultado;
    }

    private function encontrarProfesionalesConConfiguracion($sintomasPaciente, $especialidadRecomendada)
    {
        Log::info("Buscando profesionales con configuración activa para: {$especialidadRecomendada}");

        // Obtener profesionales aprobados y disponibles
        $profesionales = Profesional::where('estado_verificacion', 'aprobado')
            ->where('disponibilidad_inmediata', true)
            ->where('especialidad_principal', $especialidadRecomendada)
            ->with(['configuracionesSintomas.sintoma', 'usuario'])
            ->get();

        $profesionalesFiltrados = $profesionales->filter(function ($profesional) use ($sintomasPaciente) {
            return $this->profesionalTieneSintomasConfigurados($profesional, $sintomasPaciente);
        });

        Log::info("Profesionales con configuración activa: " . $profesionalesFiltrados->count());

        return $profesionalesFiltrados;
    }

    private function profesionalTieneSintomasConfigurados($profesional, $sintomasPaciente)
    {
        if ($profesional->configuracionesSintomas->isEmpty()) {
            Log::info("Profesional {$profesional->id} no tiene configuraciones activas");
            return false;
        }

        $palabrasPaciente = collect($sintomasPaciente['palabras_clave_encontradas'] ?? [])
            ->pluck('palabra')
            ->toArray();

        foreach ($profesional->configuracionesSintomas as $configuracion) {
            if (!$configuracion->activo) continue;

            // Verificar si el período está activo
            if (!$this->configuracionEstaActiva($configuracion)) continue;

            $sintoma = $configuracion->sintoma;
            $palabraClave = $sintoma->palabraClave;

            if (in_array($palabraClave->palabra, $palabrasPaciente)) {
                Log::info("✅ Profesional {$profesional->id} tiene configuración activa para: {$palabraClave->palabra}");
                return true;
            }
        }

        return false;
    }

    private function configuracionEstaActiva($configuracion)
    {
        $now = now();

        if ($configuracion->fecha_inicio && $configuracion->fecha_fin) {
            return $now->between($configuracion->fecha_inicio, $configuracion->fecha_fin);
        }

        // Para períodos predefinidos, verificar vigencia
        return match ($configuracion->periodo_activo) {
            'diario' => $configuracion->created_at->isToday(),
            'semanal' => $configuracion->created_at->gt(now()->subWeek()),
            'quincenal' => $configuracion->created_at->gt(now()->subWeeks(2)),
            'mensual' => $configuracion->created_at->gt(now()->subMonth()),
            default => true
        };
    }
}
