<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesionales', function (Blueprint $table) {
            $table->uuid('id_profesional')->primary();
            $table->foreignId('usuario_id')->constrained('usuarios', 'id_usuario');
            $table->string('especialidad_principal');
            $table->string('matricula')->nullable();
            $table->string('institucion')->nullable();
            $table->enum('estado_verificacion', ['aprobado', 'pendiente', 'rechazado'])->default('pendiente');
            $table->integer('anios_experiencia')->default(0);
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesionales');
    }
};