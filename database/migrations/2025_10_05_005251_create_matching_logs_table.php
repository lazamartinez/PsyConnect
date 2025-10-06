<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_logs', function (Blueprint $table) {
            // UUID como primary key
            $table->uuid('id_matching')->primary()->default(DB::raw('gen_random_uuid()'));

            // Relación con pacientes (UUID)
            $table->uuid('paciente_id');
            $table->foreign('paciente_id')
                  ->references('id_paciente')
                  ->on('pacientes')
                  ->onDelete('cascade');

            // Relación con profesionales (UUID)
            $table->uuid('profesional_id');
            $table->foreign('profesional_id')
                  ->references('id_profesional')
                  ->on('profesionales')
                  ->onDelete('cascade');

            // Datos del matching
            $table->float('nivel_coincidencia')->default(0); // % de coincidencia
            $table->text('criterios_usados')->nullable();    // JSON o descripción de criterios

            // Estado del matching
            $table->enum('estado', ['pendiente', 'aceptado', 'rechazado'])
                  ->default('pendiente');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_logs');
    }
};
