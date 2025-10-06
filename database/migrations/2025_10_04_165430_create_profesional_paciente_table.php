<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('profesional_paciente', function (Blueprint $table) {
            $table->uuid('id')->primary();
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
    }

    public function down()
    {
        Schema::dropIfExists('profesional_paciente');
    }
};
