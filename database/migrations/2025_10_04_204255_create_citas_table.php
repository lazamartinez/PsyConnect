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
        Schema::create('citas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profesional_id');
            $table->uuid('paciente_id');
            $table->dateTime('fecha_cita');
            $table->string('estado')->default('pendiente');
            $table->decimal('monto', 10, 2)->nullable(); // ← sin 'after'
            $table->timestamps();

            $table->foreign('profesional_id')
                ->references('id_profesional')
                ->on('profesionales')
                ->onDelete('cascade');

            $table->foreign('paciente_id')
                ->references('id_paciente')
                ->on('pacientes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
