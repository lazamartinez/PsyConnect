<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sintomas_especialidad', function (Blueprint $table) {
            $table->id('id_sintoma');
            $table->foreignId('especialidad_id')->constrained('especialidades', 'id_especialidad');
            $table->foreignId('palabra_clave_id')->constrained('palabras_clave', 'id_palabra_clave');
            $table->string('sintoma', 255);
            $table->text('descripcion')->nullable();
            $table->enum('nivel_gravedad', ['leve', 'moderado', 'grave', 'critico']);
            $table->string('periodo_recomendado', 50)->default('semanal');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['especialidad_id', 'activo']);
            $table->index(['palabra_clave_id', 'activo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sintomas_especialidad');
    }
};
