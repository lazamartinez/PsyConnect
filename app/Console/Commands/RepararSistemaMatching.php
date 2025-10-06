<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Profesional;
use App\Models\Usuario;

class RepararSistemaMatching extends Command
{
    protected $signature = 'sistema:reparar';
    protected $description = 'Repara el sistema de matching configurando profesionales';

    public function handle()
    {
        $this->info('🔧 Reparando sistema de matching...');

        // Configurar palabras clave por especialidad
        $configuraciones = [
            'psicologo' => [
                'palabras_clave' => ['ansiedad', 'depresión', 'trabajo', 'despido', 'solo', 'familia', 'pareja', 'crisis', 'desesperanza'],
                'sintomas' => ['Ansiedad', 'Depresión', 'Problemas laborales', 'Aislamiento social', 'Problemas familiares'],
                'experiencia' => 5,
                'calificacion' => 4.5
            ],
            'psiquiatra' => [
                'palabras_clave' => ['suicidio', 'crisis', 'depresión', 'ansiedad', 'desesperanza'],
                'sintomas' => ['Crisis emocional', 'Ideación suicida', 'Depresión severa', 'Ansiedad incapacitante'],
                'experiencia' => 8,
                'calificacion' => 4.7
            ],
            'nutricionista' => [
                'palabras_clave' => ['apetito', 'alimentación', 'energía', 'peso'],
                'sintomas' => ['Problemas alimenticios', 'Falta de energía', 'Cambios de peso', 'Alteraciones del apetito'],
                'experiencia' => 4,
                'calificacion' => 4.3
            ]
        ];

        $profesionales = Profesional::all();
        $reparados = 0;

        foreach ($profesionales as $profesional) {
            // Determinar especialidad basada en el tipo de usuario
            $especialidad = $profesional->especialidad_principal;
            if (empty($especialidad)) {
                $especialidad = match($profesional->usuario->tipo_usuario) {
                    'psicologo' => 'psicologo',
                    'psiquiatra' => 'psiquiatra', 
                    'nutricionista' => 'nutricionista',
                    default => 'psicologo'
                };
            }

            if (isset($configuraciones[$especialidad])) {
                $config = $configuraciones[$especialidad];
                
                $profesional->update([
                    'especialidad_principal' => $especialidad,
                    'palabras_clave_especialidad' => $config['palabras_clave'],
                    'sintomas_atiende' => $config['sintomas'],
                    'anios_experiencia' => $config['experiencia'],
                    'calificacion_promedio' => $config['calificacion'],
                    'disponibilidad_inmediata' => true,
                    'tiempo_respuesta_promedio_horas' => 24,
                    'estado_verificacion' => 'aprobado'
                ]);

                $this->line("✅ {$especialidad} configurado con " . count($config['palabras_clave']) . " palabras clave");
                $reparados++;
            }
        }

        $this->info("🎉 Reparación completada: {$reparados} profesionales configurados");

        // Mostrar resumen
        $this->info("\n📊 Resumen final:");
        foreach (Profesional::all() as $prof) {
            $this->line("• {$prof->especialidad_principal}: " . 
                       count($prof->palabras_clave_especialidad ?? []) . " palabras, " .
                       ($prof->disponibilidad_inmediata ? '✅ Disponible' : '❌ No disponible'));
        }

        return Command::SUCCESS;
    }
}