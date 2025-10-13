<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepararTablaProfesionalPaciente extends Command
{
    protected $signature = 'sistema:reparar-tabla-relacion';
    protected $description = 'Repara la tabla profesional_paciente';

    public function handle()
    {
        $this->info('🔧 Reparando tabla profesional_paciente...');

        if (!Schema::hasTable('profesional_paciente')) {
            $this->error('❌ La tabla profesional_paciente no existe');
            return 1;
        }

        // Verificar estructura
        $columns = Schema::getColumnListing('profesional_paciente');
        $this->info('📊 Columnas: ' . implode(', ', $columns));

        // Verificar si hay datos
        $count = DB::table('profesional_paciente')->count();
        $this->info("📈 Registros existentes: {$count}");

        // Solución temporal: crear una relación de prueba
        try {
            $paciente = DB::table('pacientes')->first();
            $profesional = DB::table('profesionales')->where('estado_verificacion', 'aprobado')->first();

            if ($paciente && $profesional) {
                DB::table('profesional_paciente')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'paciente_id' => $paciente->id_paciente,
                    'profesional_id' => $profesional->id_profesional,
                    'fecha_asignacion' => now()->toDateString(),
                    'puntuacion_compatibilidad' => 85.50,
                    'estado' => 'pendiente',
                    'motivo_asignacion' => 'Prueba de reparación',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->info('✅ Relación de prueba creada exitosamente');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error al crear relación de prueba: ' . $e->getMessage());
        }

        return 0;
    }
}