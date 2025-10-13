<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerificarEstructuraBD extends Command
{
    protected $signature = 'bd:verificar-estructura';
    protected $description = 'Verifica la estructura de la base de datos para matching';

    public function handle()
    {
        $this->info('🔍 Verificando estructura de la base de datos...');

        // Verificar tabla matching_logs
        if (!Schema::hasTable('matching_logs')) {
            $this->error('❌ La tabla matching_logs no existe');
            return 1;
        }

        $this->info('✅ Tabla matching_logs existe');

        // Verificar columnas de matching_logs
        $columns = Schema::getColumnListing('matching_logs');
        $this->info('📊 Columnas de matching_logs: ' . implode(', ', $columns));

        // Verificar si paciente_id permite nulos
        $columnType = DB::select("
            SELECT is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'matching_logs' AND column_name = 'paciente_id'
        ");

        if ($columnType && $columnType[0]->is_nullable === 'YES') {
            $this->warn('⚠️  Columna paciente_id permite valores NULL - esto puede causar problemas');
        } else {
            $this->info('✅ Columna paciente_id NO permite valores NULL');
        }

        // Verificar datos de prueba
        $pacientes = DB::table('pacientes')->count();
        $profesionales = DB::table('profesionales')->where('estado_verificacion', 'aprobado')->count();
        
        $this->info("👥 Pacientes en sistema: {$pacientes}");
        $this->info("👨‍⚕️ Profesionales aprobados: {$profesionales}");

        // Verificar primer paciente
        $primerPaciente = DB::table('pacientes')->first();
        if ($primerPaciente) {
            $this->info("📋 Primer paciente - ID: {$primerPaciente->id_paciente}, Usuario: {$primerPaciente->usuario_id}");
        } else {
            $this->error('❌ No hay pacientes en el sistema');
        }

        return 0;
    }
}
