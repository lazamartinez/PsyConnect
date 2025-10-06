<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixConfiguracionMatchingCheckConstraint extends Migration
{
    public function up()
    {
        // Eliminar la restricción CHECK existente
        DB::statement('ALTER TABLE configuracion_matching DROP CONSTRAINT IF EXISTS configuracion_matching_tipo_configuracion_check');
        
        // Crear nueva restricción CHECK que incluya 'reglas_triaje'
        DB::statement("ALTER TABLE configuracion_matching ADD CONSTRAINT configuracion_matching_tipo_configuracion_check 
                      CHECK (tipo_configuracion IN ('pesos_matching', 'reglas_especialidad', 'umbrales', 'reglas_triaje', 'palabras_clave_sistema'))");
    }

    public function down()
    {
        // Revertir a la restricción original si es necesario
        DB::statement('ALTER TABLE configuracion_matching DROP CONSTRAINT IF EXISTS configuracion_matching_tipo_configuracion_check');
        DB::statement("ALTER TABLE configuracion_matching ADD CONSTRAINT configuracion_matching_tipo_configuracion_check 
                      CHECK (tipo_configuracion IN ('pesos_matching', 'reglas_especialidad', 'umbrales', 'palabras_clave_sistema'))");
    }
}