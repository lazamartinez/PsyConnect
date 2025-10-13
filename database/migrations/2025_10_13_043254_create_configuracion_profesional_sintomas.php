<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('configuracion_profesional_sintomas', function (Blueprint $table) {
            $table->id('id_configuracion');

            // 🔧 Cambiado a UUID (para coincidir con profesionales.id_profesional)
            $table->uuid('profesional_id');
            $table->foreign('profesional_id')
                ->references('id_profesional')
                ->on('profesionales')
                ->onDelete('cascade');

            // 🔧 Este sí puede quedarse como foreignId porque es BIGINT
            $table->foreignId('sintoma_id')
                ->constrained('sintomas_especialidad', 'id_sintoma');

            $table->enum('periodo_activo', ['diario', 'semanal', 'quincenal', 'mensual', 'personalizado']);
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->integer('max_pacientes')->default(10);
            $table->enum('prioridad', ['baja', 'media', 'alta', 'urgente'])->default('media');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['profesional_id', 'activo']);
            $table->index(['sintoma_id', 'activo']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('configuracion_profesional_sintomas');
    }
};
