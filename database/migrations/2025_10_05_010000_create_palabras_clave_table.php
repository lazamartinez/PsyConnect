<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('palabras_clave', function (Blueprint $table) {
            $table->id('id_palabra_clave');
            $table->string('palabra')->unique();
            $table->string('categoria');
            $table->string('nivel_alerta');
            $table->float('peso_urgencia');
            $table->string('especialidad_recomendada'); // Mantener por compatibilidad si quieres
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades', 'id_especialidad');
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->foreignId('creado_por')->constrained('usuarios', 'id_usuario');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('palabras_clave');
    }
};
