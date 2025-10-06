<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('clinica_profesional', function (Blueprint $table) {
            $table->uuid('profesional_id_profesional'); // UUID
            $table->bigInteger('clinica_id_clinica'); // BIGINT
            $table->string('horario_trabajo')->nullable();
            $table->string('estado')->default('activo');
            $table->dateTime('fecha_ingreso')->nullable();
            $table->timestamps();

            $table->primary(['profesional_id_profesional', 'clinica_id_clinica']);

            $table->foreign('profesional_id_profesional')
                ->references('id_profesional')->on('profesionales')->onDelete('cascade');

            $table->foreign('clinica_id_clinica')
                ->references('id_clinica')->on('clinicas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinica_profesional');
    }
};
