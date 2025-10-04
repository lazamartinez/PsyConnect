<?php

namespace App\Services;

use App\Models\IndiceEstadoAnimico;
use Illuminate\Support\Facades\Log;

class AnalisisEmocionalService
{
    public function analizarTextoYCalcularIEA(string $texto, int $pacienteId, ?int $manuscritoId = null): IndiceEstadoAnimico
    {
        Log::info('=== ANALISIS EMOCIONAL SIMPLIFICADO ===');
        Log::info('Texto: ' . substr($texto, 0, 100));

        $analisis = $this->analizarTexto($texto);
        $iea = $this->calcularIEA($analisis);

        // ✅ Obtener emoción principal
        $emocionPrincipal = $this->obtenerEmocionPrincipal($analisis['emociones_detectadas']);

        Log::info('IEA calculado: ' . $iea['valor_numerico']);
        Log::info('Emoción principal: ' . $emocionPrincipal['nombre'] . ' - ' . $emocionPrincipal['intensidad']);

        // ✅ CREAR con datos SIMPLES (sin JSON)
        return IndiceEstadoAnimico::create([
            'paciente_id' => $pacienteId,
            'manuscrito_id' => $manuscritoId,
            'valor_numerico' => $iea['valor_numerico'],
            'categoria_emotional' => $iea['categoria'],
            'confiabilidad_analisis' => $iea['confiabilidad'],
            'fecha_calculo' => now(),
            'emocion_principal' => $emocionPrincipal['nombre'],
            'intensidad_principal' => $emocionPrincipal['intensidad'],
            'resumen_analisis' => $this->generarResumen($analisis, $iea),
        ]);
    }

    /**
     * Obtener la emoción principal del análisis
     */
    private function obtenerEmocionPrincipal(array $emociones): array
    {
        if (empty($emociones)) {
            return ['nombre' => 'neutral', 'intensidad' => 0.0];
        }

        $emocionPrincipal = array_key_first($emociones);
        $intensidad = $emociones[$emocionPrincipal];

        foreach ($emociones as $emocion => $intens) {
            if ($intens > $intensidad) {
                $emocionPrincipal = $emocion;
                $intensidad = $intens;
            }
        }

        return [
            'nombre' => $emocionPrincipal,
            'intensidad' => round($intensidad * 100, 2)
        ];
    }

    /**
     * Generar resumen textual del análisis
     */
    private function generarResumen(array $analisis, array $iea): string
    {
        $emocionesCount = count($analisis['emociones_detectadas']);
        $palabrasCount = $analisis['conteo_total_palabras'];

        return sprintf(
            "Análisis de %d palabras. Detectadas %d emociones. IEA: %s (%s)",
            $palabrasCount,
            $emocionesCount,
            $iea['valor_numerico'],
            $iea['categoria']
        );
    }


    private function analizarTexto(string $texto): array
    {
        // Diccionario emocional más completo en español
        $emociones = [
            'alegria' => [
                'palabras' => ['feliz', 'contento', 'alegre', 'risa', 'sonrisa', 'diversión', 'gozo', 'entusiasmo', 'optimista', 'satisfecho', 'éxito', 'logro', 'celebrar', 'dichoso'],
                'peso' => 1.2
            ],
            'tristeza' => [
                'palabras' => ['triste', 'llorar', 'deprimido', 'desanimado', 'desesperado', 'melancolía', 'desolado', 'abatido', 'desconsuelo', 'pesar', 'pena', 'dolor'],
                'peso' => 1.3
            ],
            'ansiedad' => [
                'palabras' => ['ansioso', 'nervioso', 'preocupado', 'tenso', 'estrés', 'agitado', 'inquieto', 'angustia', 'pánico', 'miedo', 'aprensión', 'intranquilo'],
                'peso' => 1.4
            ],
            'enojo' => [
                'palabras' => ['enojado', 'furioso', 'molesto', 'irritado', 'rabia', 'frustrado', 'indignado', 'colérico', 'airado', 'exasperado', 'resentido'],
                'peso' => 1.3
            ],
            'miedo' => [
                'palabras' => ['miedo', 'asustado', 'temeroso', 'pánico', 'aterrado', 'terror', 'espanto', 'susto', 'aprensivo', 'receloso', 'amedrentado'],
                'peso' => 1.4
            ],
            'calma' => [
                'palabras' => ['tranquilo', 'pacífico', 'calma', 'sereno', 'relajado', 'quietud', 'sosegado', 'plácido', 'apacible', 'armonía', 'equilibrio'],
                'peso' => 1.1
            ],
            'amor' => [
                'palabras' => ['amor', 'cariño', 'afecto', 'ternura', 'querer', 'adorar', 'compañerismo', 'compasión', 'bondad', 'generosidad', 'empatía'],
                'peso' => 1.1
            ],
            'sorpresa' => [
                'palabras' => ['sorpresa', 'asombro', 'sorprendido', 'impactado', 'maravilla', 'increíble', 'inesperado', 'extraño', 'curioso', 'novedad'],
                'peso' => 1.0
            ]
        ];

        $textoMinusculas = mb_strtolower($texto);
        $totalPalabras = str_word_count($texto);

        $resultado = [
            'conteo_total_palabras' => $totalPalabras,
            'emociones_detectadas' => [],
            'intensidad_total' => 0
        ];

        foreach ($emociones as $emocion => $config) {
            $puntaje = $this->calcularPuntajeEmocion($textoMinusculas, $config['palabras'], $config['peso'], $totalPalabras);
            if ($puntaje > 0) {
                $resultado['emociones_detectadas'][$emocion] = $puntaje;
                $resultado['intensidad_total'] += $puntaje;
            }
        }

        // Normalizar puntajes
        if ($resultado['intensidad_total'] > 0) {
            foreach ($resultado['emociones_detectadas'] as $emocion => $puntaje) {
                $resultado['emociones_detectadas'][$emocion] = $puntaje / $resultado['intensidad_total'];
            }
        }

        return $resultado;
    }

    private function calcularPuntajeEmocion(string $texto, array $palabras, float $peso, int $totalPalabras): float
    {
        $conteo = 0;
        foreach ($palabras as $palabra) {
            // Buscar la palabra completa (evitar subcadenas)
            $conteo += preg_match_all('/\b' . preg_quote($palabra, '/') . '\b/', $texto);
        }

        if ($totalPalabras === 0) return 0;

        return ($conteo / $totalPalabras) * 100 * $peso;
    }

    private function calcularIEA(array $analisis): array
    {
        $emociones = $analisis['emociones_detectadas'];

        if (empty($emociones)) {
            return $this->crearResultadoIEA(50.0, 'neutral', [], 0.0);
        }

        // Calcular valencia emocional
        $emocionesPositivas = ['alegria', 'calma', 'amor', 'sorpresa'];
        $emocionesNegativas = ['tristeza', 'ansiedad', 'enojo', 'miedo'];

        $puntajePositivo = 0;
        $puntajeNegativo = 0;

        foreach ($emociones as $emocion => $intensidad) {
            if (in_array($emocion, $emocionesPositivas)) {
                $puntajePositivo += $intensidad;
            } elseif (in_array($emocion, $emocionesNegativas)) {
                $puntajeNegativo += $intensidad;
            }
        }

        // Calcular IEA base (0-100)
        $ieaBase = 50 + ($puntajePositivo * 50) - ($puntajeNegativo * 50);
        $ieaFinal = max(0, min(100, $ieaBase));

        // Calcular confiabilidad basada en la riqueza del texto
        $confiabilidad = min(95, max(30, ($analisis['conteo_total_palabras'] * 2)));

        return $this->crearResultadoIEA(
            round($ieaFinal, 2),
            $this->determinarCategoria($ieaFinal),
            $emociones,
            round($confiabilidad, 2)
        );
    }

    private function crearResultadoIEA(float $valor, string $categoria, array $emociones, float $confiabilidad): array
    {
        return [
            'valor_numerico' => $valor,
            'categoria' => $categoria,
            'emociones' => $emociones,
            'confiabilidad' => $confiabilidad
        ];
    }

    private function determinarCategoria(float $iea): string
    {
        if ($iea >= 80) return 'muy_alto';
        if ($iea >= 60) return 'alto';
        if ($iea >= 40) return 'neutral';
        if ($iea >= 20) return 'bajo';
        return 'muy_bajo';
    }

    public function obtenerRecomendaciones(float $iea, array $emociones): array
    {
        $recomendaciones = [];

        if ($iea < 30) {
            $recomendaciones[] = "Contacta a tu profesional de salud mental";
            $recomendaciones[] = "Practica ejercicios de respiración profunda";
            $recomendaciones[] = "Busca apoyo en tu red de confianza";
        } elseif ($iea < 50) {
            $recomendaciones[] = "Realiza actividad física suave";
            $recomendaciones[] = "Practica mindfulness por 5 minutos";
            $recomendaciones[] = "Escribe tres cosas por las que estés agradecido";
        } elseif ($iea < 70) {
            $recomendaciones[] = "Mantén tus rutinas de autocuidado";
            $recomendaciones[] = "Conecta con amigos o familiares";
            $recomendaciones[] = "Dedica tiempo a tus hobbies";
        } else {
            $recomendaciones[] = "¡Excelente! Continúa con tus prácticas";
            $recomendaciones[] = "Comparte tu energía positiva con otros";
            $recomendaciones[] = "Establece nuevos objetivos personales";
        }

        // Recomendaciones específicas por emociones predominantes
        $emocionPredominante = array_key_first($emociones);
        if ($emocionPredominante) {
            switch ($emocionPredominante) {
                case 'ansiedad':
                    $recomendaciones[] = "Practica la técnica 4-7-8 de respiración";
                    break;
                case 'tristeza':
                    $recomendaciones[] = "Escucha música que te levante el ánimo";
                    break;
                case 'enojo':
                    $recomendaciones[] = "Realiza actividad física para liberar tensiones";
                    break;
            }
        }

        return $recomendaciones;
    }
}
