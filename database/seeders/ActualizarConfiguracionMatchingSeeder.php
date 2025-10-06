<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActualizarConfiguracionMatchingSeeder extends Seeder
{
    public function run()
    {
        // Actualizar configuración de pesos
        DB::table('configuracion_matching')
            ->where('tipo_configuracion', 'pesos_matching')
            ->where('estado', true)
            ->update([
                'configuracion_json' => json_encode([
                    'coincidencia_palabras_clave' => 0.4,
                    'especialidad_principal' => 0.25,
                    'experiencia_calificacion' => 0.2,
                    'disponibilidad' => 0.15,
                    'ubicacion' => 0.0,
                    'precio' => 0.0,
                    'nivel_urgencia' => 0.0
                ])
            ]);

        $this->command->info('✅ Configuración de pesos actualizada');
    }
}