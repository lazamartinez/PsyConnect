<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\TriajeInicial;
use App\Models\ConfiguracionMatching;
use App\Models\PalabraClave;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MatchingService
{
    protected $configuracion;
    protected $clinicaId;

    public function __construct($clinicaId = null)
    {
        $this->clinicaId = $clinicaId;
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
                    'categoria' => 'ansiedad',
                    'nivel_alerta' => 'medio',
                    'peso_urgencia' => 0.8,
                    'especialidad_recomendada' => 'psicologo',
                    'descripcion' => 'Sensación de nerviosismo, preocupación o inquietud'
                ],
                [
                    'palabra' => 'depresión',
                    'categoria' => 'depresion',
                    'nivel_alerta' => 'medio',
                    'peso_urgencia' => 0.8,
                    'especialidad_recomendada' => 'psicologo',
                    'descripcion' => 'Estado de tristeza profunda, pérdida de interés o placer'
                ],
                [
                    'palabra' => 'estrés',
                    'categoria' => 'estres',
                    'nivel_alerta' => 'medio',
                    'peso_urgencia' => 0.7,
                    'especialidad_recomendada' => 'psicologo',
                    'descripcion' => 'Tensión física o emocional'
                ],
                [
                    'palabra' => 'crisis',
                    'categoria' => 'crisis',
                    'nivel_alerta' => 'alto',
                    'peso_urgencia' => 0.9,
                    'especialidad_recomendada' => 'psicologo',
                    'descripcion' => 'Situación de emergencia emocional'
                ],
                [
                    'palabra' => 'suicidio',
                    'categoria' => 'suicida',
                    'nivel_alerta' => 'critico',
                    'peso_urgencia' => 1.0,
                    'especialidad_recomendada' => 'psiquiatra',
                    'descripcion' => 'Ideación o intento de quitarse la vida'
                ],
                [
                    'palabra' => 'psicosis',
                    'categoria' => 'psicosis',
                    'nivel_alerta' => 'critico',
                    'peso_urgencia' => 1.0,
                    'especialidad_recomendada' => 'psiquiatra',
                    'descripcion' => 'Pérdida de contacto con la realidad'
                ]
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

    private function reparacionEmergencia()
    {
        if (Profesional::where('estado_verificacion', 'aprobado')->count() === 0) {
            $this->crearProfesionalEmergencia();
        }

        if (PalabraClave::where('estado', true)->count() === 0) {
            $this->crearPalabrasClaveEmergencia();
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

    private function analizarSintomasMejorado(string $descripcion)
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

    private function buscarTerminoEnTexto(string $termino, string $texto): bool
    {
        $patron = '/\b' . preg_quote($termino, '/') . '\b/i';
        return preg_match($patron, $texto) === 1;
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

    private function encontrarProfesionalOptimoMejorado(Paciente $paciente, array $sintomas, string $descripcion, string $especialidadRecomendada)
    {
        Log::info("=== BUSCANDO PROFESIONAL OPTIMO MEJORADO ===");
        Log::info("Especialidad: {$especialidadRecomendada}, Síntomas: " . json_encode($sintomas['sintomas_detectados']));

        $profesionales = Profesional::where('estado_verificacion', 'aprobado')
            ->where('especialidad_principal', $especialidadRecomendada)
            ->where('disponibilidad_inmediata', true)
            ->where(function ($query) {
                $query->where('disponibilidad_inmediata', true)
                    ->orWhere('tiempo_respuesta_promedio_horas', '<=', 72);
            })
            ->with(['usuario', 'clinicas'])
            ->get();

        if ($profesionales->isEmpty()) {
            Log::warning("No hay profesionales aprobados disponibles para: " . $especialidadRecomendada);
            return [
                'profesional_optimo' => null,
                'puntaje' => 0,
                'todos_los_resultados' => [],
                'detalles_matching' => [],
                'umbral_minimo' => 30,
                'total_profesionales_evaluados' => 0
            ];
        }

        Log::info("Profesionales encontrados: " . $profesionales->count());

        $mejorPuntaje = 0;
        $profesionalOptimo = null;
        $resultados = [];
        $detallesMatching = [];

        foreach ($profesionales as $profesional) {
            $puntaje = $this->calcularCompatibilidadMejorada($profesional, $sintomas, $descripcion);
            $detallesCalculo = $this->obtenerDetallesCalculoMejorado($profesional, $sintomas, $descripcion);

            $resultado = [
                'profesional' => $profesional,
                'puntaje' => $puntaje,
                'especialidad' => $profesional->especialidad_principal,
                'palabras_clave' => $profesional->palabras_clave_especialidad ?? [],
                'detalles_calculo' => $detallesCalculo,
                'compatibilidad_detallada' => $this->generarCompatibilidadDetallada($profesional, $sintomas)
            ];

            $resultados[] = $resultado;
            $detallesMatching[] = $detallesCalculo;

            Log::info("🎯 Profesional {$profesional->id} - {$profesional->especialidad_principal}: {$puntaje}%");

            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $profesionalOptimo = $profesional;
            }
        }

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
            'detalles_matching' => $detallesMatching,
            'umbral_minimo' => $umbralMinimo,
            'total_profesionales_evaluados' => count($resultados)
        ];
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
        $palabrasClaveProfesional = $profesional->palabras_clave_especialidad ?? [];

        if (empty($palabrasClaveProfesional)) {
            Log::warning("Profesional {$profesional->id} no tiene palabras clave configuradas");
            return 0.1;
        }

        $coincidencias = 0;
        $palabrasPaciente = collect($sintomas['palabras_clave_encontradas'] ?? [])->pluck('palabra')->toArray();

        if (empty($palabrasPaciente)) {
            return 0.1;
        }

        Log::info("📝 Comparando palabras - Profesional: " . json_encode($palabrasClaveProfesional));
        Log::info("📝 Palabras paciente: " . json_encode($palabrasPaciente));

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

    private function determinarEspecialidadMejorado(array $analisisSintomas)
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

        // CORRECCIÓN: Manejar correctamente las palabras clave
        $palabrasClaveArray = [];
        if (isset($resultadoMatching['todos_los_resultados'][0]['palabras_clave'])) {
            $palabrasClave = $resultadoMatching['todos_los_resultados'][0]['palabras_clave'];
            if ($palabrasClave instanceof \Illuminate\Support\Collection) {
                $palabrasClaveArray = $palabrasClave->toArray();
            } else {
                $palabrasClaveArray = (array)$palabrasClave;
            }
        }

        $primerasPalabras = array_slice($palabrasClaveArray, 0, 3);
        $palabrasTexto = !empty($primerasPalabras) ? implode(', ', $primerasPalabras) : 'varios síntomas detectados';

        // Crear relación paciente-profesional
        $paciente->profesionales()->attach($profesional->id, [
            'fecha_asignacion' => now(),
            'puntuacion_compatibilidad' => $puntaje,
            'estado' => 'pendiente',
            'motivo_asignacion' => 'Matching automático - Compatibilidad: ' . $puntaje . '% - Síntomas: ' . $palabrasTexto
        ]);

        // CORRECCIÓN: Usar las columnas correctas de matching_logs
        if (DB::getSchemaBuilder()->hasTable('matching_logs')) {
            DB::table('matching_logs')->insert([
                'paciente_id' => $paciente->id,
                'profesional_id' => $profesional->id,
                'nivel_coincidencia' => $puntaje, // Usar nivel_coincidencia en lugar de puntuacion_compatibilidad
                'criterios_usados' => json_encode([
                    'descripcion_paciente' => $descripcion,
                    'resultados_comparacion' => $resultadoMatching['todos_los_resultados'],
                    'configuracion_utilizada' => $this->configuracion
                ]),
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        Log::info("✅ MATCH CREADO: Paciente {$paciente->id} asignado a Profesional {$profesional->id} con {$puntaje}% de compatibilidad");
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
}