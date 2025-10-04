<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Eliminar las columnas JSON problemáticas
        Schema::table('indices_estado_animico', function (Blueprint $table) {
            if (Schema::hasColumn('indices_estado_animico', 'emociones_detectadas')) {
                $table->dropColumn('emociones_detectadas');
            }
            if (Schema::hasColumn('indices_estado_animico', 'analisis_completo')) {
                $table->dropColumn('analisis_completo');
            }
        });

        // Agregar columnas simples en su lugar
        Schema::table('indices_estado_animico', function (Blueprint $table) {
            $table->string('emocion_principal')->nullable();
            $table->decimal('intensidad_principal', 5, 2)->nullable();
            $table->text('resumen_analisis')->nullable();
        });
    }

    public function down()
    {
        Schema::table('indices_estado_animico', function (Blueprint $table) {
            $table->dropColumn(['emocion_principal', 'intensidad_principal', 'resumen_analisis']);
            
            // Restaurar columnas JSON (pueden ser text temporalmente)
            $table->text('emociones_detectadas')->nullable();
            $table->text('analisis_completo')->nullable();
        });
    }
};