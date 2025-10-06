<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_matching', function (Blueprint $table) {
            // Añadir la columna si no existe
            if (!Schema::hasColumn('configuracion_matching', 'clinica_id')) {
                $table->unsignedBigInteger('clinica_id')->nullable();

                // Ajustar la FK al nombre correcto de la columna primaria
                $table->foreign('clinica_id')
                      ->references('id_clinica')
                      ->on('clinicas')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_matching', function (Blueprint $table) {
            if (Schema::hasColumn('configuracion_matching', 'clinica_id')) {
                $table->dropForeign(['clinica_id']);
                $table->dropColumn('clinica_id');
            }
        });
    }
};
