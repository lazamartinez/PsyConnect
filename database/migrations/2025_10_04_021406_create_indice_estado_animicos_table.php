<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indices_estado_animico', function (Blueprint $table) {
            //UUID como clave primaria
            $table->uuid('id_iea')->primary();

            // Relaciones UUID con pacientes y manuscritos
            $table->uuid('paciente_id');
            $table->foreign('paciente_id')
                  ->references('id_paciente')
                  ->on('pacientes')
                  ->onDelete('cascade');

            $table->uuid('manuscrito_id')->nullable();
            $table->foreign('manuscrito_id')
                  ->references('id_manuscrito')
                  ->on('manuscritos')
                  ->onDelete('set null');

            // Campos de datos
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
