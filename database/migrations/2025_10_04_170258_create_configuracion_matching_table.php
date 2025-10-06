<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('configuracion_matching', function (Blueprint $table) {
            $table->id('id_configuracion');
            $table->string('nombre_configuracion', 255);
            $table->enum('tipo_configuracion', [
                'pesos_matching', 
                'reglas_especialidad', 
                'umbrales',
                'palabras_clave_sistema'
            ]);
            $table->json('configuracion_json');
            $table->boolean('estado')->default(true);
            $table->integer('version')->default(1);
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            $table->index(['tipo_configuracion', 'estado']);
        });

        // Insertar configuraciones por defecto
        $this->insertarConfiguracionesPorDefecto();
    }

    private function insertarConfiguracionesPorDefecto()
    {
        // Configuración de pesos por defecto
        DB::table('configuracion_matching')->insert([
            'nombre_configuracion' => 'Pesos Matching Inicial',
            'tipo_configuracion' => 'pesos_matching',
            'configuracion_json' => json_encode([
                'coincidencia_palabras_clave' => 0.4,
                'especialidad_principal' => 0.25,
                'experiencia_calificacion' => 0.2,
                'disponibilidad' => 0.15
            ]),
            'estado' => true,
            'version' => 1,
            'descripcion' => 'Configuración inicial de pesos para algoritmo de matching',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Configuración de reglas de especialidad por defecto
        DB::table('configuracion_matching')->insert([
            'nombre_configuracion' => 'Reglas Especialidad Inicial',
            'tipo_configuracion' => 'reglas_especialidad',
            'configuracion_json' => json_encode([
                'psicologo' => [
                    'palabras_clave' => ['ansiedad', 'depresión', 'estrés', 'trauma', 'terapia', 'emocion', 'mente', 'pensamiento', 'familia', 'trabajo'],
                    'sintomas' => ['ansiedad', 'depresión', 'estrés', 'problemas familiares', 'problemas laborales'],
                    'nivel_urgencia_maximo' => 'alto',
                    'requiere_derivacion_psiquiatra' => ['psicosis', 'ideacion_suicida', 'trastorno_bipolar']
                ],
                'psiquiatra' => [
                    'palabras_clave' => ['medicamento', 'fármaco', 'diagnóstico', 'trastorno', 'hospital', 'urgencia', 'psicosis', 'bipolar', 'esquizofrenia', 'suicidio'],
                    'sintomas' => ['psicosis', 'ideacion_suicida', 'trastorno_bipolar', 'esquizofrenia', 'tdah'],
                    'nivel_urgencia_minimo' => 'alto',
                    'puede_recetar_medicamentos' => true
                ],
                'nutricionista' => [
                    'palabras_clave' => ['dieta', 'alimentación', 'peso', 'comida', 'nutrición', 'ejercicio', 'metabolismo', 'vitamina', 'obesidad', 'anorexia'],
                    'sintomas' => ['trastornos alimenticios', 'obesidad', 'desnutrición', 'alergias alimentarias'],
                    'nivel_urgencia_maximo' => 'medio',
                    'derivar_a_psicologo' => ['anorexia', 'bulimia']
                ]
            ]),
            'estado' => true,
            'version' => 1,
            'descripcion' => 'Reglas iniciales de especialidad para triaje automático',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Configuración de umbrales por defecto
        DB::table('configuracion_matching')->insert([
            'nombre_configuracion' => 'Umbrales Sistema Inicial',
            'tipo_configuracion' => 'umbrales',
            'configuracion_json' => json_encode([
                'compatibilidad_minima' => 30,
                'confianza_minima_asignacion' => 60,
                'maximo_pacientes_profesional' => 50,
                'tiempo_maximo_respuesta_horas' => 72
            ]),
            'estado' => true,
            'version' => 1,
            'descripcion' => 'Umbrales iniciales del sistema de matching',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('configuracion_matching');
    }
};