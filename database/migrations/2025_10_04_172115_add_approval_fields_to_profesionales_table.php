<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('profesionales', function (Blueprint $table) {
            // Agregar nuevos campos para aprobación
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->dateTime('fecha_rechazo')->nullable();
        });

        // Cambiar el tipo de estado_verificacion usando enum de PostgreSQL
        DB::statement("ALTER TABLE profesionales 
            ALTER COLUMN estado_verificacion TYPE VARCHAR(25) USING estado_verificacion::VARCHAR");

        DB::statement("ALTER TABLE profesionales 
            DROP CONSTRAINT IF EXISTS chk_estado_verificacion");

        DB::statement("ALTER TABLE profesionales 
            ADD CONSTRAINT chk_estado_verificacion CHECK (estado_verificacion IN ('pendiente', 'aprobado', 'rechazado'))");

        // Asegurar valor por defecto
        DB::statement("ALTER TABLE profesionales 
            ALTER COLUMN estado_verificacion SET DEFAULT 'pendiente'");

        // Asegurar que no sea nulo
        DB::statement("ALTER TABLE profesionales 
            ALTER COLUMN estado_verificacion SET NOT NULL");
    }

    public function down()
    {
        Schema::table('profesionales', function (Blueprint $table) {
            $table->dropColumn(['fecha_aprobacion', 'motivo_rechazo', 'fecha_rechazo']);
        });

        // Revertir cambios del estado_verificacion
        DB::statement("ALTER TABLE profesionales 
            DROP CONSTRAINT IF EXISTS chk_estado_verificacion");

        DB::statement("ALTER TABLE profesionales 
            ALTER COLUMN estado_verificacion TYPE VARCHAR(255)");

        DB::statement("ALTER TABLE profesionales 
            ALTER COLUMN estado_verificacion DROP DEFAULT");

        DB::statement("ALTER TABLE profesionales 
            ALTER COLUMN estado_verificacion DROP NOT NULL");
    }
};
