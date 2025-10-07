<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clinica_profesional', function (Blueprint $table) {
            $table->uuid('profesional_id'); // UUID del profesional
            $table->bigInteger('clinica_id'); // BIGINT de la clínica
            $table->string('horario_trabajo')->nullable();
            $table->string('estado')->default('activo');
            $table->dateTime('fecha_ingreso')->nullable();
            $table->timestamps();

            $table->primary(['profesional_id', 'clinica_id']);

            $table->foreign('profesional_id')
                ->references('id_profesional')->on('profesionales')->onDelete('cascade');

            $table->foreign('clinica_id')
                ->references('id_clinica')->on('clinicas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinica_profesional');
    }
};
