<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PalabrasClaveSeeder extends Seeder
{
    public function run(): void
    {
        $usuarioId = 1; // ID del administrador

        $palabrasClave = [
            // ANSIEDAD
            [
                'palabra' => 'ansiedad',
                'categoria' => 'ansiedad',
                'nivel_alerta' => 'medio',
                'peso_urgencia' => 0.8,
                'especialidad_recomendada' => 'psicologo',
                'sinonimos' => json_encode(['nervios', 'preocupación', 'tensión', 'angustia']),
                'descripcion' => 'Sensación de preocupación y tensión constante',
                'estado' => true,
                'creado_por' => $usuarioId,
            ],
            [
                'palabra' => 'estrés',
                'categoria' => 'ansiedad',
                'nivel_alerta' => 'medio',
                'peso_urgencia' => 0.7,
                'especialidad_recomendada' => 'psicologo',
                'sinonimos' => json_encode(['agobio', 'presión', 'sobrecarga']),
                'descripcion' => 'Estado de cansancio mental provocado por exigencias',
                'estado' => true,
                'creado_por' => $usuarioId,
            ],

            // DEPRESIÓN
            [
                'palabra' => 'depresión',
                'categoria' => 'depresion',
                'nivel_alerta' => 'alto',
                'peso_urgencia' => 0.9,
                'especialidad_recomendada' => 'psicologo',
                'sinonimos' => json_encode(['tristeza', 'desesperanza', 'desánimo', 'melancolía']),
                'descripcion' => 'Estado de ánimo bajo persistente',
                'estado' => true,
                'creado_por' => $usuarioId,
            ],
            [
                'palabra' => 'desesperanza',
                'categoria' => 'depresion',
                'nivel_alerta' => 'alto',
                'peso_urgencia' => 0.8,
                'especialidad_recomendada' => 'psicologo',
                'sinonimos' => json_encode(['desesperación', 'sin salida']),
                'descripcion' => 'Sentimiento de que las cosas no mejorarán',
                'estado' => true,
                'creado_por' => $usuarioId,
            ],

            // TRABAJO
            [
                'palabra' => 'trabajo',
                'categoria' => 'trabajo',
                'nivel_alerta' => 'bajo',
                'peso_urgencia' => 0.6,
                'especialidad_recomendada' => 'psicologo',
                'sinonimos' => json_encode(['empleo', 'oficina', 'laboral']),
                'descripcion' => 'Problemas relacionados con el ámbito laboral',
                'estado' => true,
                'creado_por' => $usuarioId,
            ],
            [
                'palabra' => 'despido',
                'categoria' => 'trabajo',
                'nivel_alerta' => 'medio',
                'peso_urgencia' => 0.7,
                'especialidad_recomendada' => 'psicologo',
                'sinonimos' => json_encode(['desempleo', 'paro', 'quedarse sin trabajo']),
                'descripcion' => 'Pérdida del empleo',
                'estado' => true,
                'creado_por' => $usuarioId,
            ],

            // SOLEDAD
            [
                'palabra' => 'solo',
                'categoria' => 'relaciones',
                'nivel_alerta' => 'medio',
                'peso_urgencia' => 0.7,
                'especialidad_recomendada' => 'psicologo',
                'sinonimos' => json_encode(['soledad', 'aislamiento', 'incompañía']),
                'descripcion' => 'Sentimiento de soledad y aislamiento',
                'estado' => true,
                'creado_por' => $usuarioId,
            ],

            // FAMILIA
            [
                'palabra' => 'familia',
                'categoria' => 'familia',
                'nivel_alerta' => 'bajo',
                'peso_urgencia' => 0.5,
                'especialidad_recomendada' => 'psicologo',
                'sinonimos' => json_encode(['parental', 'familiar', 'hogar']),
                'descripcion' => 'Problemas familiares y relaciones parentales',
                'estado' => true,
                'creado_por' => $usuarioId,
            ],

            // CRÍTICAS
            [
                'palabra' => 'suicidio',
                'categoria' => 'suicida',
                'nivel_alerta' => 'critico',
                'peso_urgencia' => 1.0,
                'especialidad_recomendada' => 'psiquiatra',
                'sinonimos' => json_encode(['matar', 'morir', 'acabar con todo']),
                'descripcion' => 'Ideación suicida o pensamientos de muerte',
                'estado' => true,
                'creado_por' => $usuarioId,
            ],
        ];

        foreach ($palabrasClave as $palabra) {
            DB::table('palabras_clave')->updateOrInsert(
                ['palabra' => $palabra['palabra']],
                array_merge($palabra, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ Palabras clave actualizadas con sinónimos y categorías expandidas');
    }
}