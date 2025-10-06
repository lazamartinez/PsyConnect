<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TriajeInicial;
use App\Models\Paciente;
use App\Models\Profesional;
use Illuminate\Support\Facades\DB;

class TriajeInicialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla antes de insertar
        DB::table('triajes_iniciales')->truncate();

        $pacientes = Paciente::all();
        $profesionales = Profesional::all();

        foreach ($pacientes as $paciente) {
            $profesional = $profesionales->random();

            TriajeInicial::create([
                'paciente_id' => $paciente->id,
                'profesional_asignado_id' => $profesional->id,
                'descripcion_sintomatologia' => 'Paciente presenta ansiedad y depresión leve.',
                'analisis_sintomatologia' => [
                    'sintomas_detectados' => ['ansiedad', 'depresión'],
                    'palabras_clave_encontradas' => [
                        [
                            'palabra' => 'ansiedad',
                            'categoria' => 'ansiedad',
                            'nivel_alerta' => 'medio',
                            'peso_urgencia' => 0.8,
                            'especialidad_recomendada' => 'psicologo'
                        ],
                        [
                            'palabra' => 'depresión',
                            'categoria' => 'depresión',
                            'nivel_alerta' => 'bajo',
                            'peso_urgencia' => 0.6,
                            'especialidad_recomendada' => 'psicologo'
                        ]
                    ],
                    'nivel_urgencia' => 'medio',
                    'puntaje_urgencia' => 1.4,
                    'total_palabras_clave' => 2,
                ],
                'especialidad_recomendada' => 'psicologo',
                'nivel_urgencia' => 'medio',
                'fecha_triaje' => now(),
                'estado_triaje' => 'pendiente',
                'confianza_asignacion' => 75,
                'configuracion_utilizada' => [
                    'algoritmo' => 'matching_parametrizable_v2',
                    'pesos_utilizados' => [
                        'coincidencia_palabras_clave' => 0.4,
                        'especialidad_principal' => 0.25,
                        'experiencia_calificacion' => 0.2,
                        'disponibilidad' => 0.15
                    ],
                    'umbrales_utilizados' => [
                        'compatibilidad_minima' => 30,
                        'confianza_minima_asignacion' => 60,
                        'maximo_pacientes_profesional' => 50,
                        'tiempo_maximo_respuesta_horas' => 72
                    ]
                ],
            ]);
        }
    }
}
