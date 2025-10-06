<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPalabrasClaveToProfesionalesTable extends Migration
{
    public function up()
    {
        Schema::table('profesionales', function (Blueprint $table) {
            // Agregar columnas JSON para palabras clave y síntomas
            $table->json('palabras_clave_especialidad')->nullable();
            $table->json('sintomas_atiende')->nullable();
            $table->boolean('disponibilidad_inmediata')->default(true);
            $table->integer('tiempo_respuesta_promedio_horas')->default(24);
            $table->decimal('calificacion_promedio', 3, 2)->nullable();
            
            // Nueva columna 'sinonimos' como JSON
            $table->json('sinonimos')->nullable()->after('palabras_clave_especialidad');

            // Columnas adicionales que pueden faltar
            $table->json('especialidades_secundarias')->nullable();
            $table->json('idiomas')->nullable();
            $table->json('formacion_academica')->nullable();
            $table->json('certificaciones')->nullable();
            $table->json('poblacion_objetivo')->nullable();
            $table->string('modalidad_trabajo')->nullable();
            $table->text('enfoque_terapeutico')->nullable();
            $table->integer('capacidad_pacientes')->default(20);
        });
    }

    public function down()
    {
        Schema::table('profesionales', function (Blueprint $table) {
            $table->dropColumn([
                'palabras_clave_especialidad',
                'sintomas_atiende',
                'disponibilidad_inmediata',
                'tiempo_respuesta_promedio_horas',
                'calificacion_promedio',
                'sinonimos', // <-- también eliminamos esta columna
                'especialidades_secundarias',
                'idiomas',
                'formacion_academica',
                'certificaciones',
                'poblacion_objetivo',
                'modalidad_trabajo',
                'enfoque_terapeutico',
                'capacidad_pacientes'
            ]);
        });
    }
}
