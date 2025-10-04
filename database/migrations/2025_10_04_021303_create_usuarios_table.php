<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('email')->unique();
            $table->string('contrasenia');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('telefono')->nullable();
            $table->enum('tipo_usuario', ['paciente', 'psicologo', 'psiquiatra', 'nutricionista', 'administrador']);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->boolean('estado')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};