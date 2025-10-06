<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\TriajeInicial;
use App\Models\ConfiguracionMatching;
use App\Models\PalabraClave;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

    public function procesarTriajeCompleto(Paciente $paciente, string $descripcionSintomatologia)
    {
        Log::info("=== INICIANDO TRIAJE COMPLETO ===");
        Log::info("Paciente: {$paciente->id}, Texto: " . substr($descripcionSintomatologia, 0, 100));

        try {
            // 1. Analizar síntomas mejorado
            $analisisSintomas = $this->analizarSintomasMejorado($descripcionSintomatologia);

            Log::info("Análisis completado:", [
                'sintomas_detectados' => $analisisSintomas['sintomas_detectados'],
                'palabras_encontradas' => count($analisisSintomas['palabras_clave_encontradas']),
                'nivel_urgencia' => $analisisSintomas['nivel_urgencia']
            ]);

            // 2. Determinar especialidad
            $especialidadRecomendada = $this->determinarEspecialidadMejorado($analisisSintomas);
            Log::info("Especialidad recomendada: {$especialidadRecomendada}");

            // 3. Buscar profesionales con criterios más flexibles
            $resultadoMatching = $this->encontrarProfesionalOptimoMejorado(
                $paciente,
                $analisisSintomas,
                $descripcionSintomatologia,
                $especialidadRecomendada
            );

            // 4. Guardar resultados
            $triaje = $this->guardarTriaje($paciente, $descripcionSintomatologia, $analisisSintomas, $resultadoMatching);

            // 5. Crear relación si hay match
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

        // Obtener todas las palabras clave activas
        $palabrasClaveSistema = PalabraClave::activas()->get();
        Log::info("Palabras clave en sistema: " . $palabrasClaveSistema->count());

        $sintomasDetectados = [];
        $palabrasClaveEncontradas = [];
        $puntajeUrgencia = 0;

        // Búsqueda más inteligente - incluir sinónimos y búsqueda parcial
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
                    break; // Evitar duplicados
                }
            }
        }

        Log::info("Resumen análisis - Encontradas: " . count($palabrasClaveEncontradas) . ", Puntaje: {$puntajeUrgencia}");

        // Determinar nivel de urgencia con configuración real
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
        // Búsqueda más flexible - considerar palabras compuestas y variaciones
        $patron = '/\b' . preg_quote($termino, '/') . '\b/i';
        return preg_match($patron, $texto) === 1;
    }

    private function determinarNivelUrgenciaMejorado(array $palabrasClave, float $puntajeUrgencia)
    {
        // Usar configuración real del sistema
        $nivelesConfig = $this->configuracion['triaje']['niveles_urgencia'] ?? [
            'critico' => ['min_palabras' => 1, 'min_puntaje' => 2.0],
            'alto' => ['min_palabras' => 2, 'min_puntaje' => 1.0],
            'medio' => ['min_palabras' => 1, 'min_puntaje' => 0.5],
            'bajo' => ['min_palabras' => 0, 'min_puntaje' => 0]
        ];

        Log::info("Umbrales urgencia configurados: " . json_encode($nivelesConfig));

        // 1. Verificar palabras críticas primero
        foreach ($palabrasClave as $palabra) {
            if ($palabra['nivel_alerta'] === 'critico') {
                Log::info("🔴 Nivel CRÍTICO por palabra: {$palabra['palabra']}");
                return 'critico';
            }
        }

        // 2. Determinar por puntaje y cantidad de palabras
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

        // Buscar profesionales con criterios más flexibles
        $profesionales = Profesional::where('estado_verificacion', 'aprobado')
            ->where('especialidad_principal', $especialidadRecomendada)
            ->where(function ($query) {
                $query->where('disponibilidad_inmediata', true)
                    ->orWhere('tiempo_respuesta_promedio_horas', '<=', 72);
            })
            ->with(['usuario', 'clinicas'])
            ->get();

        if ($profesionales->isEmpty()) {
            Log::warning("No hay profesionales aprobados disponibles para: " . $especialidadRecomendada);
            return null;
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

        // Ordenar resultados por puntaje
        usort($resultados, function ($a, $b) {
            return $b['puntaje'] <=> $a['puntaje'];
        });

        // Aplicar umbral mínimo
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

    // === MÉTODO FALTANTE - AGREGAR ESTO ===
    private function calcularCompatibilidadMejorada(Profesional $profesional, array $sintomas, string $descripcion)
    {
        $puntajeTotal = 0;
        $pesos = $this->configuracion['pesos'];
        $detalles = [];

        // 1. Coincidencia con palabras clave (mejorada)
        $coincidenciaPalabras = $this->calcularCoincidenciaPalabrasMejorada($profesional, $sintomas);
        $puntajeTotal += $coincidenciaPalabras * $pesos['coincidencia_palabras_clave'];
        $detalles['coincidencia_palabras'] = $coincidenciaPalabras;

        // 2. Especialidad principal
        $puntajeEspecialidad = $this->calcularPuntajeEspecialidad($profesional->especialidad_principal, $sintomas);
        $puntajeTotal += $puntajeEspecialidad * $pesos['especialidad_principal'];
        $detalles['especialidad'] = $puntajeEspecialidad;

        // 3. Experiencia y calificación
        $puntajeExperiencia = $this->calcularPuntajeExperiencia($profesional);
        $puntajeTotal += $puntajeExperiencia * $pesos['experiencia_calificacion'];
        $detalles['experiencia'] = $puntajeExperiencia;

        // 4. Disponibilidad
        $puntajeDisponibilidad = $this->calcularPuntajeDisponibilidad($profesional);
        $puntajeTotal += $puntajeDisponibilidad * $pesos['disponibilidad'];
        $detalles['disponibilidad'] = $puntajeDisponibilidad;

        // 5. Ubicación - CORRECCIÓN: Usar puntaje neutral temporal
        $puntajeUbicacion = 0.5; // Puntaje neutral hasta que resolvamos cómo pasar el paciente
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
            return 0.1; // Puntaje mínimo para profesionales sin configuración
        }

        $coincidencias = 0;

        // CORRECCIÓN: Convertir Collection a array si es necesario
        $palabrasClaveEncontradas = $sintomas['palabras_clave_encontradas'] ?? [];
        $palabrasPaciente = [];

        foreach ($palabrasClaveEncontradas as $palabraData) {
            if (is_array($palabraData) && isset($palabraData['palabra'])) {
                $palabrasPaciente[] = $palabraData['palabra'];
            } elseif (is_object($palabraData) && isset($palabraData->palabra)) {
                $palabrasPaciente[] = $palabraData->palabra;
            }
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
                    'valor' => 0.5, // Puntaje neutral temporal
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
        $palabrasPaciente = array_column($sintomas['palabras_clave_encontradas'], 'palabra');

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

    // Mantener los métodos existentes pero asegurar que funcionen correctamente
    private function determinarEspecialidadMejorado(array $analisisSintomas)
    {
        // Lógica mejorada para determinar especialidad
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
            return 'psicologo'; // Valor por defecto
        }

        arsort($especialidadesPuntaje);
        return array_key_first($especialidadesPuntaje);
    }

    public function encontrarProfesionalOptimo(Paciente $paciente, array $sintomas, string $descripcion, string $especialidadRecomendada)
    {
        Log::info("=== BUSCANDO PROFESIONAL OPTIMO CON CONFIGURACIÓN PARAMETRIZABLE ===");

        // CORRECCIÓN: Quitar 'palabras_clave_especialidad' del with() ya que no es una relación
        $profesionales = Profesional::where('estado_verificacion', 'aprobado')
            ->where('especialidad_principal', $especialidadRecomendada)
            ->where('disponibilidad_inmediata', true)
            ->with(['usuario', 'clinicas']) // Solo relaciones existentes
            ->get();

        Log::info("Profesionales disponibles para {$especialidadRecomendada}: " . $profesionales->count());

        $mejorPuntaje = 0;
        $profesionalOptimo = null;
        $resultados = [];

        foreach ($profesionales as $profesional) {
            $puntaje = $this->calcularCompatibilidadParametrizable($profesional, $sintomas, $descripcion);

            $resultados[] = [
                'profesional' => $profesional,
                'puntaje' => $puntaje,
                'especialidad' => $profesional->especialidad_principal,
                'palabras_clave' => $profesional->palabras_clave_especialidad, // Acceso directo al atributo
                'detalles_calculo' => $this->obtenerDetallesCalculo($profesional, $sintomas, $descripcion)
            ];

            Log::info("Profesional {$profesional->id} ({$profesional->especialidad_principal}): $puntaje puntos");

            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $profesionalOptimo = $profesional;
            }
        }

        // Aplicar umbral mínimo de compatibilidad
        $umbralMinimo = $this->configuracion['umbrales']['compatibilidad_minima'] ?? 30;
        if ($mejorPuntaje < $umbralMinimo) {
            Log::info("Ningún profesional supera el umbral mínimo de {$umbralMinimo}%. Mejor puntaje: {$mejorPuntaje}");
            $profesionalOptimo = null;
        }

        // Ordenar resultados por puntaje
        usort($resultados, function ($a, $b) {
            return $b['puntaje'] <=> $a['puntaje'];
        });

        return [
            'profesional_optimo' => $profesionalOptimo,
            'puntaje' => $mejorPuntaje,
            'todos_los_resultados' => $resultados,
            'umbral_minimo' => $umbralMinimo
        ];
    }

    private function calcularCompatibilidadParametrizable(Profesional $profesional, array $sintomas, string $descripcion)
    {
        $puntajeTotal = 0;
        $pesos = $this->configuracion['pesos'];
        $detalles = [];

        // 1. Coincidencia con palabras clave del profesional
        $coincidenciaPalabras = $this->calcularCoincidenciaPalabras($profesional, $sintomas);
        $puntajeTotal += $coincidenciaPalabras * $pesos['coincidencia_palabras_clave'];
        $detalles['coincidencia_palabras'] = $coincidenciaPalabras;

        // 2. Especialidad principal
        $puntajeEspecialidad = $this->calcularPuntajeEspecialidad($profesional->especialidad_principal, $sintomas);
        $puntajeTotal += $puntajeEspecialidad * $pesos['especialidad_principal'];
        $detalles['especialidad'] = $puntajeEspecialidad;

        // 3. Experiencia y calificación
        $puntajeExperiencia = $this->calcularPuntajeExperiencia($profesional);
        $puntajeTotal += $puntajeExperiencia * $pesos['experiencia_calificacion'];
        $detalles['experiencia'] = $puntajeExperiencia;

        // 4. Disponibilidad
        $puntajeDisponibilidad = $this->calcularPuntajeDisponibilidad($profesional);
        $puntajeTotal += $puntajeDisponibilidad * $pesos['disponibilidad'];
        $detalles['disponibilidad'] = $puntajeDisponibilidad;

        // 5. Nivel de urgencia (nuevo factor parametrizable)
        $puntajeUrgencia = $this->calcularPuntajeUrgencia($profesional, $sintomas);
        $puntajeTotal += $puntajeUrgencia * ($pesos['nivel_urgencia'] ?? 0.02);
        $detalles['urgencia'] = $puntajeUrgencia;

        Log::info("Detalles cálculo compatibilidad para profesional {$profesional->id}: " . json_encode($detalles));

        return min(100, $puntajeTotal * 100);
    }

    private function obtenerDetallesCalculo(Profesional $profesional, array $sintomas, string $descripcion)
    {
        $pesos = $this->configuracion['pesos'];

        return [
            'coincidencia_palabras' => [
                'valor' => $this->calcularCoincidenciaPalabras($profesional, $sintomas),
                'peso' => $pesos['coincidencia_palabras_clave'],
                'contribucion' => $this->calcularCoincidenciaPalabras($profesional, $sintomas) * $pesos['coincidencia_palabras_clave'] * 100
            ],
            'especialidad' => [
                'valor' => $this->calcularPuntajeEspecialidad($profesional->especialidad_principal, $sintomas),
                'peso' => $pesos['especialidad_principal'],
                'contribucion' => $this->calcularPuntajeEspecialidad($profesional->especialidad_principal, $sintomas) * $pesos['especialidad_principal'] * 100
            ],
            'experiencia' => [
                'valor' => $this->calcularPuntajeExperiencia($profesional),
                'peso' => $pesos['experiencia_calificacion'],
                'contribucion' => $this->calcularPuntajeExperiencia($profesional) * $pesos['experiencia_calificacion'] * 100
            ]
        ];
    }

    // === MÉTODOS DE CÁLCULO DE COMPATIBILIDAD ===

    private function calcularPuntajeUbicacion(Profesional $profesional, ?Paciente $paciente = null)
    {
        // Si no hay paciente o el paciente no tiene ubicación, retornar puntaje neutral
        if (!$paciente || !$paciente->ciudad) {
            return 0.5; // Puntaje neutral
        }

        $ciudadPaciente = mb_strtolower(trim($paciente->ciudad));

        // Verificar si el profesional atiende en la misma ciudad
        if ($profesional->ciudad && mb_strtolower(trim($profesional->ciudad)) === $ciudadPaciente) {
            Log::info("✅ Coincidencia de ubicación: {$profesional->ciudad} = {$paciente->ciudad}");
            return 1.0; // Máxima compatibilidad
        }

        // Verificar si el profesional atiende en múltiples ciudades
        if (!empty($profesional->ciudades_atencion)) {
            foreach ($profesional->ciudades_atencion as $ciudadProf) {
                if (mb_strtolower(trim($ciudadProf)) === $ciudadPaciente) {
                    Log::info("✅ Coincidencia de ubicación en ciudades de atención: {$ciudadProf}");
                    return 0.8; // Alta compatibilidad
                }
            }
        }

        // Verificar si el profesional atiende virtualmente
        if ($profesional->atencion_virtual) {
            Log::info("ℹ️ Profesional ofrece atención virtual");
            return 0.7; // Buena compatibilidad por atención virtual
        }

        Log::info("❌ Sin coincidencia de ubicación: Profesional en {$profesional->ciudad}, Paciente en {$paciente->ciudad}");
        return 0.3; // Baja compatibilidad por ubicación diferente
    }

    private function calcularCoincidenciaPalabras(Profesional $profesional, array $sintomas)
    {
        // CORRECCIÓN: Acceder directamente al atributo, no como relación
        $palabrasClaveProfesional = $profesional->palabras_clave_especialidad;

        if (empty($palabrasClaveProfesional)) {
            return 0;
        }

        $coincidencias = 0;
        $palabrasPaciente = array_column($sintomas['palabras_clave_encontradas'], 'palabra');

        foreach ($palabrasClaveProfesional as $palabraProfesional) {
            if (in_array($palabraProfesional, $palabrasPaciente)) {
                $coincidencias++;
                Log::info("Coincidencia encontrada: '$palabraProfesional' entre profesional y paciente");
            }
        }

        $totalPalabras = count($palabrasClaveProfesional);
        return $totalPalabras > 0 ? $coincidencias / $totalPalabras : 0;
    }

    private function calcularPuntajeEspecialidad(string $especialidad, array $sintomas)
    {
        $reglasEspecialidad = $this->configuracion['reglas_especialidad'];

        if (!isset($reglasEspecialidad[$especialidad])) {
            return 0.5;
        }

        $config = $reglasEspecialidad[$especialidad];
        $coincidencias = 0;
        $palabrasPaciente = array_column($sintomas['palabras_clave_encontradas'], 'palabra');

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

        // Años de experiencia
        if ($profesional->anios_experiencia >= 10) $puntaje += 0.3;
        elseif ($profesional->anios_experiencia >= 5) $puntaje += 0.2;
        elseif ($profesional->anios_experiencia >= 2) $puntaje += 0.1;

        // Calificación promedio
        if ($profesional->calificacion_promedio >= 4.5) $puntaje += 0.2;
        elseif ($profesional->calificacion_promedio >= 4.0) $puntaje += 0.1;

        // Certificaciones adicionales
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

    private function calcularPuntajeUrgencia(Profesional $profesional, array $sintomas)
    {
        $nivelUrgenciaPaciente = $sintomas['nivel_urgencia'];
        $reglasEspecialidad = $this->configuracion['reglas_especialidad'];
        $especialidad = $profesional->especialidad_principal;

        if (!isset($reglasEspecialidad[$especialidad])) {
            return 0.5;
        }

        $config = $reglasEspecialidad[$especialidad];

        // Verificar si el profesional puede atender el nivel de urgencia
        $nivelUrgenciaMinimo = $config['nivel_urgencia_minimo'] ?? 'bajo';
        $nivelUrgenciaMaximo = $config['nivel_urgencia_maximo'] ?? 'alto';

        $niveles = ['bajo' => 1, 'medio' => 2, 'alto' => 3, 'critico' => 4];

        $nivelPaciente = $niveles[$nivelUrgenciaPaciente] ?? 1;
        $nivelMinimoProfesional = $niveles[$nivelUrgenciaMinimo] ?? 1;
        $nivelMaximoProfesional = $niveles[$nivelUrgenciaMaximo] ?? 4;

        // Si el paciente tiene mayor urgencia de la que el profesional puede manejar
        if ($nivelPaciente > $nivelMaximoProfesional) {
            return 0.2; // Penalización
        }

        // Si el paciente tiene menor urgencia de la mínima que el profesional atiende
        if ($nivelPaciente < $nivelMinimoProfesional) {
            return 0.3; // Penalización menor
        }

        // Si está en el rango óptimo
        return 0.8;
    }

    // === MÉTODOS DE PERSISTENCIA ===

    private function crearRelacionPacienteProfesional(Paciente $paciente, array $resultadoMatching, string $descripcion)
    {
        $profesional = $resultadoMatching['profesional_optimo'];
        $puntaje = $resultadoMatching['puntaje'];

        // Crear relación paciente-profesional
        $paciente->profesionales()->attach($profesional->id, [
            'fecha_asignacion' => now(),
            'puntuacion_compatibilidad' => $puntaje,
            'estado' => 'pendiente',
            'motivo_asignacion' => 'Matching automático - Compatibilidad: ' . $puntaje . '% - Síntomas: ' .
                implode(', ', array_slice(array_column($resultadoMatching['todos_los_resultados'][0]['palabras_clave'] ?? [], 0), 3))
        ]);

        // Guardar detalles del matching
        if (DB::getSchemaBuilder()->hasTable('matching_logs')) {
            DB::table('matching_logs')->insert([
                'paciente_id' => $paciente->id,
                'profesional_id' => $profesional->id,
                'puntuacion_compatibilidad' => $puntaje,
                'descripcion_paciente' => $descripcion,
                'resultados_comparacion' => json_encode($resultadoMatching['todos_los_resultados']),
                'configuracion_utilizada' => json_encode($this->configuracion),
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

    // === MÉTODOS ADICIONALES DE UTILIDAD ===

    public function obtenerEstadisticasMatching($clinicaId = null)
    {
        $triajes = TriajeInicial::when($clinicaId, function ($query) use ($clinicaId) {
            // Aquí necesitarías una relación para filtrar por clínica
            // Esto es un ejemplo - ajusta según tu estructura de datos
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

        // Re-procesar el triaje con la configuración actual
        $resultado = $this->procesarTriajeCompleto(
            $paciente,
            $triaje->descripcion_sintomatologia
        );

        // Actualizar el triaje existente
        $triaje->update([
            'profesional_asignado_id' => $resultado['profesional']?->id,
            'confianza_asignacion' => $resultado['puntaje_compatibilidad'],
            'configuracion_utilizada' => $resultado['triaje']->configuracion_utilizada
        ]);

        return $resultado;
    }
}
