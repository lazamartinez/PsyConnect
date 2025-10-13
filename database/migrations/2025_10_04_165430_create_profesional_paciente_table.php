<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('profesional_paciente', function (Blueprint $table) {
            // 🔥 CORRECCIÓN CRÍTICA: Agregar default para UUID
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            
            $table->uuid('profesional_id');
            $table->uuid('paciente_id');
            $table->date('fecha_asignacion');
            $table->decimal('puntuacion_compatibilidad', 5, 2);
            $table->enum('estado', ['pendiente', 'activo', 'inactivo', 'rechazado'])->default('pendiente');
            $table->text('motivo_asignacion')->nullable();
            $table->timestamps();

            // 🔗 Relaciones
            $table->foreign('profesional_id')
                  ->references('id_profesional')
                  ->on('profesionales')
                  ->onDelete('cascade');

            $table->foreign('paciente_id')
                  ->references('id_paciente')
                  ->on('pacientes')
                  ->onDelete('cascade');

            // 🔒 Unicidad de relación
            $table->unique(['profesional_id', 'paciente_id']);
        });

        // 🔥 CORRECCIÓN ADICIONAL: Asegurar que PostgreSQL genere UUIDs
        DB::statement('ALTER TABLE profesional_paciente ALTER COLUMN id SET DEFAULT gen_random_uuid()');
    }

    public function down()
    {
        Schema::dropIfExists('profesional_paciente');
    }
};