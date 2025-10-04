<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manuscritos', function (Blueprint $table) {
            $table->id('id_manuscrito');
            $table->foreignId('paciente_id')->constrained('pacientes', 'id_paciente');
            $table->string('imagen_original');
            $table->string('imagen_procesada')->nullable();
            $table->text('texto_digitalizado')->nullable();
            $table->decimal('confianza_ocr', 5, 2)->nullable()->comment('Porcentaje de confianza OCR 0-100');
            $table->timestamp('fecha_captura');
            $table->timestamp('fecha_procesamiento')->nullable();
            $table->enum('estado_procesamiento', ['pendiente', 'procesado', 'error'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manuscritos');
    }
};