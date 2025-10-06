<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsignarPalabrasClaveProfesionalesSeeder extends Seeder
{
    public function run()
    {
        $palabrasPorEspecialidad = [
            'Psicología Clínica' => [
                'ansiedad', 'depresión', 'estrés', 'trauma', 'familia', 
                'trabajo', 'pareja', 'autoestima', 'duelo', 'fobia'
            ],
            'Psiquiatría General' => [
                'medicamento', 'diagnóstico', 'trastorno', 'psicosis', 
                'bipolar', 'esquizofrenia', 'tdah', 'depresión mayor'
            ],
            'Nutrición' => [
                'dieta', 'alimentación', 'peso', 'nutrición', 'ejercicio',
                'metabolismo', 'obesidad', 'hábitos alimenticios'
            ]
        ];

        $profesionales = DB::table('profesionales')
            ->join('usuarios', 'profesionales.usuario_id', '=', 'usuarios.id_usuario')
            ->select('profesionales.id_profesional', 'profesionales.especialidad_principal', 'usuarios.nombre')
            ->get();

        foreach ($profesionales as $prof) {
            $palabras = $palabrasPorEspecialidad[$prof->especialidad_principal] ?? ['general', 'consulta', 'terapia'];
            
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