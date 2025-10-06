<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manuscritos', function (Blueprint $table) {
            // UUID como clave primaria
            $table->uuid('id_manuscrito')->primary();

            // Relación UUID con la tabla pacientes
            $table->uuid('paciente_id');
            $table->foreign('paciente_id')
                  ->references('id_paciente')
                  ->on('pacientes')
                  ->onDelete('cascade');

            // Campos de imágenes
            $table->string('imagen_original');
            $table->string('imagen_procesada')->nullable();

            // OCR y procesamiento
            $table->text('texto_digitalizado')->nullable();
            $table->decimal('confianza_ocr', 5, 2)->nullable()->comment('Porcentaje de confianza OCR 0-100');
            $table->timestamp('fecha_captura');
            $table->timestamp('fecha_procesamiento')->nullable();
            $table->enum('estado_procesamiento', ['pendiente', 'procesado', 'error'])->default('pendiente');

            // Timestamps automáticos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manuscritos');
    }
};
