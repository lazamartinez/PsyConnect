<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinicas', function (Blueprint $table) {
            $table->id('id_clinica');
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->json('coordenadas')->nullable();
            $table->enum('estado', ['activa', 'inactiva', 'pendiente'])->default('pendiente');
            $table->json('horario_atencion')->nullable();
            $table->json('servicios_especializados')->nullable();
            $table->foreignId('administrador_id')->nullable()->constrained('usuarios', 'id_usuario');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinicas');
    }
};
