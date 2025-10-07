<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Especialidad;

class EspecialidadesSeeder extends Seeder
{
    public function run()
    {
        Especialidad::inicializarEspecialidades();
        
        $this->command->info('✅ Especialidades parametrizables creadas');
        
        // Migrar profesionales existentes a especialidades
        $this->migrarProfesionalesExistentes();
    }
    
    private function migrarProfesionalesExistentes()
    {
        $mapeoEspecialidades = [
            'psicologo' => 'psicologia_clinica',
            'psiquiatra' => 'psiquiatria_general', 
            'nutricionista' => 'nutricion_clinica'
        ];
        
        foreach ($mapeoEspecialidades as $vieja => $nueva) {
            $especialidad = Especialidad::where('codigo', $nueva)->first();
            if ($especialidad) {
                \App\Models\Profesional::where('especialidad_principal', $vieja)
                    ->update(['especialidad_id' => $especialidad->id_especialidad]);
            }
        }
        
        $this->command->info('✅ Profesionales migrados a especialidades parametrizables');
    }
}