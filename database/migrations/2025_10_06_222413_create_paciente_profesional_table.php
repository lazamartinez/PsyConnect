<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_profesional', function (Blueprint $table) {
            $table->uuid('paciente_id');
            $table->uuid('profesional_id');

            // 🔽 Campos que faltaban:
            $table->date('fecha_asignacion')->nullable();
            $table->decimal('puntuacion_compatibilidad', 5, 2)->nullable();
            $table->string('estado')->default('pendiente');
            $table->text('motivo_asignacion')->nullable();

            $table->timestamps();

            $table->primary(['paciente_id', 'profesional_id']);

            $table->foreign('paciente_id')
                ->references('id_paciente')
                ->on('pacientes')
                ->onDelete('cascade');

            $table->foreign('profesional_id')
                ->references('id_profesional')
                ->on('profesionales')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_profesional');
    }
};
