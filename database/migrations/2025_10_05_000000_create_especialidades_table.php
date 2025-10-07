<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Crear tabla especialidades
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id('id_especialidad');
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->text('descripcion')->nullable();
            
            // Nuevo campo: rol que puede usar esta especialidad (psicologo, psiquiatra, nutricionista, etc.)
            $table->string('rol_permitido')->nullable();
            
            $table->boolean('activo')->default(true);
            $table->string('color')->nullable(); // opcional para mostrar en interfaz
            $table->string('icono')->nullable(); // opcional para iconos de UI
            $table->json('configuracion')->nullable(); // configuración específica de la especialidad
            $table->timestamps();
        });

        // Actualizar tabla profesionales solo si existe
        if (Schema::hasTable('profesionales')) {
            Schema::table('profesionales', function (Blueprint $table) {
                // relación con especialidades parametrizables
                $table->foreignId('especialidad_id')
                    ->nullable()
                    ->constrained('especialidades', 'id_especialidad')
                    ->nullOnDelete();

                $table->json('subespecialidades')->nullable();
            });
        }
    }

    public function down()
    {
        // Revertir cambios en profesionales si la tabla existe
        if (Schema::hasTable('profesionales')) {
            Schema::table('profesionales', function (Blueprint $table) {
                if (Schema::hasColumn('profesionales', 'especialidad_id')) {
                    $table->dropForeign(['especialidad_id']);
                    $table->dropColumn(['especialidad_id']);
                }
                if (Schema::hasColumn('profesionales', 'subespecialidades')) {
                    $table->dropColumn(['subespecialidades']);
                }
            });
        }

        Schema::dropIfExists('especialidades');
    }
};
