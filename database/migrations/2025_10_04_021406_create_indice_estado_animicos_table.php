<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indices_estado_animico', function (Blueprint $table) {
            $table->id('id_iea');
            $table->foreignId('paciente_id')->constrained('pacientes', 'id_paciente');
            $table->foreignId('manuscrito_id')->nullable()->constrained('manuscritos', 'id_manuscrito');
            $table->decimal('valor_numerico', 5, 2)->comment('Valor IEA entre 0-100');
            $table->enum('categoria_emotional', ['muy_bajo', 'bajo', 'neutral', 'alto', 'muy_alto']);
            $table->json('emociones_detectadas')->nullable();
            $table->decimal('confiabilidad_analisis', 5, 2)->default(0);
            $table->timestamp('fecha_calculo');
            $table->text('analisis_completo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indices_estado_animico');
    }
};