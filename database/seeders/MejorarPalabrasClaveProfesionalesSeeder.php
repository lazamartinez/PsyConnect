<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MejorarPalabrasClaveProfesionalesSeeder extends Seeder
{
    public function run()
    {
        $palabrasPorEspecialidad = [
            'psicologo' => [
                'ansiedad', 'depresión', 'estrés', 'trauma', 'familia', 
                'trabajo', 'pareja', 'autoestima', 'duelo', 'fobia',
                'problemas familiares', 'conflictos', 'emociones', 'mente',
                'terapia', 'psicológico', 'bienestar mental'
            ],
            'psiquiatra' => [
                'medicamento', 'diagnóstico', 'trastorno', 'psicosis', 
                'bipolar', 'esquizofrenia', 'tdah', 'depresión mayor',
                'fármaco', 'tratamiento', 'hospital', 'urgencia',
                'medicación', 'prescripción', 'evaluación médica'
            ],
            'nutricionista' => [
                'dieta', 'alimentación', 'peso', 'nutrición', 'ejercicio',
                'metabolismo', 'obesidad', 'hábitos alimenticios',
                'comida', 'adelgazar', 'salud', 'vitaminas', 'minerales',
                'plan alimenticio', 'control peso'
            ]
        ];

        $profesionales = DB::table('profesionales')
            ->join('usuarios', 'profesionales.usuario_id', '=', 'usuarios.id_usuario')
            ->select('profesionales.id_profesional', 'profesionales.especialidad_principal', 'usuarios.nombre')
            ->get();

        foreach ($profesionales as $prof) {
            $especialidad = strtolower($prof->especialidad_principal);
            $palabras = $palabrasPorEspecialidad[$especialidad] ?? ['general', 'consulta', 'terapia'];
            
            DB::table('profesionales')
                ->where('id_profesional', $prof->id_profesional)
                ->update([
                    'palabras_clave_especialidad' => json_encode($palabras),
                    'sintomas_atiende' => json_encode($palabras),
                    'updated_at' => now()
                ]);

            $this->command->info("✅ {$prof->nombre} ({$prof->especialidad_principal}): " . implode(', ', $palabras));
        }
    }
}