<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Eliminar la restricción CHECK existente
        DB::statement('ALTER TABLE triajes_iniciales DROP CONSTRAINT IF EXISTS triajes_iniciales_nivel_urgencia_check');
        
        // Crear nueva restricción CHECK que incluya 'critico'
        DB::statement("ALTER TABLE triajes_iniciales ADD CONSTRAINT triajes_iniciales_nivel_urgencia_check 
                      CHECK (nivel_urgencia IN ('bajo', 'medio', 'alto', 'critico'))");
    }

    public function down()
    {
        DB::statement('ALTER TABLE triajes_iniciales DROP CONSTRAINT IF EXISTS triajes_iniciales_nivel_urgencia_check');
        DB::statement("ALTER TABLE triajes_iniciales ADD CONSTRAINT triajes_iniciales_nivel_urgencia_check 
                      CHECK (nivel_urgencia IN ('bajo', 'medio', 'alto'))");
    }
};