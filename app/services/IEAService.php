<?php

namespace App\Services;

use App\Models\IndiceEstadoAnimico;
use App\Models\Paciente;
use App\Models\Alerta;
use Carbon\Carbon;

class IEAService
{
    public function calcularIEDesdeTexto(string $texto, int $pacienteId): array
    {
        // Análisis de emociones básico
        $emociones = $this->analizarEmociones($texto);
        $valorIEA = $this->calcularValorIEA($emociones);
        $categoria = $this->categorizarIEA($valorIEA);
        $banderasRiesgo = $this->detectarBanderasRiesgo($texto, $emociones);

        // Guardar en base de datos
        $iea = IndiceEstadoAnimico::create([
            'paciente_id' => $pacienteId,
            'valor_numerico' => $valorIEA,
            'categoria_emotional' => $categoria,
            'emociones_detectadas' => $emociones,
            'palabras_clave' => $this->extraerPalabrasClave($texto),
            'confiabilidad_analisis' => $this->calcularConfiabilidad($texto),
            'banderas_riesgo' => $banderasRiesgo,
            'fuente_datos' => 'texto_directo'
        ]);

        // Evaluar alertas automáticas
        $this->evaluarAlertasAutomaticas($iea);

        return [
            'iea' => $iea,
            'valor' => $valorIEA,
            'categoria' => $categoria,
            'emociones' => $emociones,
            'alertas_generadas' => $banderasRiesgo
        ];
    }

    private function analizarEmociones(string $texto): array
    {
        $emociones = [
            'alegria' => 0,
            'tristeza' => 0,
            'enojo' => 0,
            'miedo' => 0,
            'sorpresa' => 0,
            'neutral' => 0
        ];

        // Diccionario de palabras emocionales (simplificado)
        $diccionario = [
            'alegria' => ['feliz', 'contento', 'alegre', 'emocionado', 'genial', 'maravilloso'],
            'tristeza' => ['triste', 'deprimido', 'desanimado', 'desesperado', 'solo', 'vacío'],
            'enojo' => ['enojado', 'furioso', 'molesto', 'irritado', 'frustrado'],
            'miedo' => ['asustado', 'atemorizado', 'ansioso', 'nervioso', 'preocupado'],
            'sorpresa' => ['sorprendido', 'impactado', 'asombrado']
        ];

        $palabras = str_word_count(strtolower($texto), 1);
        
        foreach ($palabras as $palabra) {
            foreach ($diccionario as $emocion => $palabrasEmocion) {
                if (in_array($palabra, $palabrasEmocion)) {
                    $emociones[$emocion]++;
                }
            }
        }

        // Normalizar valores
        $total = array_sum($emociones);
        if ($total > 0) {
            foreach ($emociones as $key => $value) {
                $emociones[$key] = ($value / $total) * 100;
            }
        }

        return $emociones;
    }

    private function calcularValorIEA(array $emociones): float
    {
        // Fórmula ponderada para calcular IEA (0-100)
        $positivas = $emociones['alegria'] + $emociones['sorpresa'];
        $negativas = $emociones['tristeza'] + $emociones['enojo'] + $emociones['miedo'];
        
        $base = 50; // Punto neutral
        $ajuste = ($positivas - $negativas) / 2;
        
        $valor = $base + $ajuste;
        
        // Asegurar que esté en rango 0-100
        return max(0, min(100, $valor));
    }

    private function categorizarIEA(float $valor): string
    {
        return match(true) {
            $valor < 20 => 'muy_bajo',
            $valor < 40 => 'bajo',
            $valor < 60 => 'neutral',
            $valor < 80 => 'alto',
            default => 'muy_alto'
        };
    }

    private function detectarBanderasRiesgo(string $texto, array $emociones): array
    {
        $banderas = [];
        
        // Palabras de riesgo
        $palabrasRiesgo = [
            'suicidio', 'morir', 'matar', 'sin esperanza', 'no puedo más',
            'despedida', 'acabar con todo', 'no vale la pena'
        ];

        $textoLower = strtolower($texto);
        
        foreach ($palabrasRiesgo as $palabra) {
            if (str_contains($textoLower, $palabra)) {
                $banderas[] = 'riesgo_' . str_replace(' ', '_', $palabra);
            }
        }

        // Banderas basadas en emociones
        if ($emociones['tristeza'] > 70) {
            $banderas[] = 'tristeza_extrema';
        }

        if ($emociones['enojo'] > 70) {
            $banderas[] = 'enojo_intenso';
        }

        return $banderas;
    }

    private function extraerPalabrasClave(string $texto): array
    {
        $stopWords = ['el', 'la', 'los', 'las', 'de', 'en', 'y', 'o', 'pero', 'porque'];
        $palabras = str_word_count(strtolower($texto), 1);
        
        return array_filter($palabras, function($palabra) use ($stopWords) {
            return !in_array($palabra, $stopWords) && strlen($palabra) > 3;
        });
    }

    private function calcularConfiabilidad(string $texto): float
    {
        $longitud = strlen($texto);
        
        return match(true) {
            $longitud < 50 => 60.0,
            $longitud < 100 => 75.0,
            $longitud < 200 => 85.0,
            default => 95.0
        };
    }

    public function calcularTendencias(int $pacienteId, int $dias = 30): array
    {
        $fechaInicio = Carbon::now()->subDays($dias);
        
        $registros = IndiceEstadoAnimico::where('paciente_id', $pacienteId)
            ->where('created_at', '>=', $fechaInicio)
            ->orderBy('created_at')
            ->get();

        if ($registros->isEmpty()) {
            return [
                'tendencia' => 'estable',
                'promedio' => 50,
                'variacion' => 0,
                'registros' => []
            ];
        }

        $valores = $registros->pluck('valor_numerico')->toArray();
        $promedio = array_sum($valores) / count($valores);
        
        // Calcular tendencia (regresión lineal simple)
        $n = count($valores);
        $sumX = $n * ($n - 1) / 2;
        $sumY = array_sum($valores);
        $sumXY = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $i * $valores[$i];
        }
        
        $pendiente = ($n * $sumXY - $sumX * $sumY) / ($n * ($n * $n - 1) / 12);

        $tendencia = match(true) {
            $pendiente > 1 => 'mejorando',
            $pendiente < -1 => 'empeorando',
            default => 'estable'
        };

        return [
            'tendencia' => $tendencia,
            'promedio' => round($promedio, 2),
            'variacion' => round($pendiente, 2),
            'registros' => $registros
        ];
    }

    public function evaluarAlertasAutomaticas(IndiceEstadoAnimico $iea): void
    {
        $alertasGeneradas = [];

        // Alerta por IEA muy bajo
        if ($iea->valor_numerico < 20) {
            Alerta::create([
                'paciente_id' => $iea->paciente_id,
                'tipo' => 'riesgo',
                'nivel_urgencia' => 'alto',
                'descripcion' => 'IEA críticamente bajo detectado',
                'iea_id' => $iea->id
            ]);
            $alertasGeneradas[] = 'iea_critico';
        }

        // Alerta por banderas de riesgo
        if (in_array('riesgo_suicidio', $iea->banderas_riesgo ?? [])) {
            Alerta::create([
                'paciente_id' => $iea->paciente_id,
                'tipo' => 'crisis',
                'nivel_urgencia' => 'critico',
                'descripcion' => 'Posible riesgo suicida detectado',
                'iea_id' => $iea->id
            ]);
            $alertasGeneradas[] = 'riesgo_suicida';
        }
    }

    public function recalcularIEA(int $ieaId): ?IndiceEstadoAnimico
    {
        $iea = IndiceEstadoAnimico::find($ieaId);
        
        if (!$iea) {
            return null;
        }

        // Aquí iría la lógica para recalcular basado en el texto original
        // Por ahora, simplemente retornamos el existente
        return $iea;
    }
}