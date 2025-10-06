<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triajes_iniciales', function (Blueprint $table) {
            $table->uuid('id_triaje')->primary()->default(DB::raw('gen_random_uuid()'));

            // FK a paciente
            $table->uuid('paciente_id')->nullable();
            $table->foreign('paciente_id')
                  ->references('id_paciente')
                  ->on('pacientes')
                  ->onDelete('set null');

            // FK a profesional
            $table->uuid('profesional_asignado_id')->nullable();
            $table->foreign('profesional_asignado_id')
                  ->references('id_profesional')
                  ->on('profesionales')
                  ->onDelete('set null');

            $table->text('descripcion_sintomatologia')->nullable();
            $table->json('analisis_sintomatologia')->nullable();
            $table->string('especialidad_recomendada')->nullable();
            $table->enum('nivel_urgencia', ['bajo', 'medio', 'alto'])->default('medio');
            $table->dateTime('fecha_triaje')->nullable();
            $table->enum('estado_triaje', ['pendiente', 'completado', 'cancelado'])->default('pendiente');
            $table->decimal('confianza_asignacion', 5, 2)->nullable();
            $table->json('configuracion_utilizada')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triajes_iniciales');
    }
};
