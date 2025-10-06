<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionMatching extends Model
{
    use HasFactory;

    protected $table = 'configuracion_matching';
    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'nombre_configuracion',
        'tipo_configuracion',
        'configuracion_json',
        'estado',
        'version',
        'descripcion',
        'clinica_id'
    ];

    protected $casts = [
        'configuracion_json' => 'array',
        'estado' => 'boolean'
    ];

    // Obtener configuración activa por tipo y clínica
    public static function obtenerConfiguracionActiva($tipo, $clinicaId = null)
    {
        return self::where('tipo_configuracion', $tipo)
            ->where('estado', true)
            ->when($clinicaId, function ($query) use ($clinicaId) {
                return $query->where('clinica_id', $clinicaId);
            })
            ->orderBy('version', 'desc')
            ->first();
    }

    // Obtener pesos para el algoritmo de matching (parametrizable por clínica)
    public static function obtenerPesosMatching($clinicaId = null)
    {
        $config = self::obtenerConfiguracionActiva('pesos_matching', $clinicaId);

        if (!$config) {
            return [
                'coincidencia_palabras_clave' => 0.4,
                'especialidad_principal' => 0.25,
                'experiencia_calificacion' => 0.2,
                'disponibilidad' => 0.15,
                'ubicacion' => 0.0, // Temporalmente 0 hasta resolver ubicación
                'precio' => 0.0,
                'nivel_urgencia' => 0.0
            ];
        }

        return $config->configuracion_json;
    }

    // Obtener reglas de especialidad parametrizables
    public static function obtenerReglasEspecialidad($clinicaId = null)
    {
        $config = self::obtenerConfiguracionActiva('reglas_especialidad', $clinicaId);

        if (!$config) {
            // Reglas por defecto completamente parametrizables
            return [
                'psicologo' => [
                    'nombre' => 'Psicólogo',
                    'descripcion' => 'Profesional especializado en terapia psicológica',
                    'palabras_clave' => [
                        'ansiedad' => ['peso' => 0.9, 'nivel_alerta' => 'medio'],
                        'depresión' => ['peso' => 0.9, 'nivel_alerta' => 'medio'],
                        'estrés' => ['peso' => 0.8, 'nivel_alerta' => 'medio'],
                        'trauma' => ['peso' => 0.7, 'nivel_alerta' => 'alto'],
                        'familia' => ['peso' => 0.8, 'nivel_alerta' => 'bajo'],
                        'trabajo' => ['peso' => 0.7, 'nivel_alerta' => 'bajo'],
                        'pareja' => ['peso' => 0.8, 'nivel_alerta' => 'bajo']
                    ],
                    'sintomas_automaticos' => [
                        'problemas familiares',
                        'conflictos de pareja',
                        'estrés laboral',
                        'ansiedad generalizada',
                        'depresión leve a moderada'
                    ],
                    'nivel_urgencia_maximo' => 'alto',
                    'requiere_derivacion_psiquiatra' => ['psicosis', 'ideacion_suicida', 'trastorno_bipolar'],
                    'activo' => true
                ],
                'psiquiatra' => [
                    'nombre' => 'Psiquiatra',
                    'descripcion' => 'Médico especialista en diagnóstico y tratamiento farmacológico',
                    'palabras_clave' => [
                        'medicamento' => ['peso' => 0.9, 'nivel_alerta' => 'bajo'],
                        'fármaco' => ['peso' => 0.9, 'nivel_alerta' => 'bajo'],
                        'diagnóstico' => ['peso' => 0.8, 'nivel_alerta' => 'medio'],
                        'trastorno' => ['peso' => 0.9, 'nivel_alerta' => 'medio'],
                        'hospital' => ['peso' => 0.7, 'nivel_alerta' => 'alto'],
                        'urgencia' => ['peso' => 0.8, 'nivel_alerta' => 'alto'],
                        'psicosis' => ['peso' => 1.0, 'nivel_alerta' => 'critico'],
                        'bipolar' => ['peso' => 0.9, 'nivel_alerta' => 'alto'],
                        'esquizofrenia' => ['peso' => 1.0, 'nivel_alerta' => 'critico'],
                        'suicidio' => ['peso' => 1.0, 'nivel_alerta' => 'critico']
                    ],
                    'sintomas_automaticos' => [
                        'trastornos psicóticos',
                        'ideación suicida',
                        'trastorno bipolar',
                        'depresión severa',
                        'ansiedad incapacitante'
                    ],
                    'nivel_urgencia_minimo' => 'medio',
                    'puede_recetar_medicamentos' => true,
                    'activo' => true
                ],
                'nutricionista' => [
                    'nombre' => 'Nutricionista',
                    'descripcion' => 'Especialista en alimentación y trastornos nutricionales',
                    'palabras_clave' => [
                        'dieta' => ['peso' => 0.9, 'nivel_alerta' => 'bajo'],
                        'alimentación' => ['peso' => 0.8, 'nivel_alerta' => 'bajo'],
                        'peso' => ['peso' => 0.9, 'nivel_alerta' => 'medio'],
                        'comida' => ['peso' => 0.7, 'nivel_alerta' => 'bajo'],
                        'nutrición' => ['peso' => 0.8, 'nivel_alerta' => 'bajo'],
                        'ejercicio' => ['peso' => 0.6, 'nivel_alerta' => 'bajo'],
                        'metabolismo' => ['peso' => 0.7, 'nivel_alerta' => 'bajo'],
                        'obesidad' => ['peso' => 0.8, 'nivel_alerta' => 'medio'],
                        'anorexia' => ['peso' => 1.0, 'nivel_alerta' => 'alto'],
                        'bulimia' => ['peso' => 1.0, 'nivel_alerta' => 'alto']
                    ],
                    'sintomas_automaticos' => [
                        'trastornos alimenticios',
                        'obesidad',
                        'desnutrición',
                        'relación problemática con la comida'
                    ],
                    'nivel_urgencia_maximo' => 'medio',
                    'derivar_a_psicologo' => ['anorexia', 'bulimia'],
                    'activo' => true
                ]
            ];
        }

        return $config->configuracion_json;
    }

    // Obtener umbrales del sistema parametrizables
    public static function obtenerUmbrales($clinicaId = null)
    {
        $config = self::obtenerConfiguracionActiva('umbrales', $clinicaId);

        if (!$config) {
            return [
                'compatibilidad_minima' => 30,
                'confianza_minima_asignacion' => 60,
                'maximo_pacientes_profesional' => 50,
                'tiempo_maximo_respuesta_horas' => 72,
                'reintentos_matching' => 3,
                'tiempo_espera_reintento_horas' => 24
            ];
        }

        return $config->configuracion_json;
    }

    // Obtener reglas de triaje parametrizables
    public static function obtenerReglasTriaje($clinicaId = null)
    {
        $config = self::obtenerConfiguracionActiva('reglas_triaje', $clinicaId);

        if (!$config) {
            return [
                'longitud_minima_descripcion' => 50,
                'longitud_maxima_descripcion' => 2000,
                'palabras_clave_obligatorias' => 3,
                'tiempo_procesamiento_minutos' => 30,
                'revision_manual_activada' => false,
                'niveles_urgencia' => [
                    'critico' => ['min_palabras' => 1, 'accion' => 'contacto_inmediato'],
                    'alto' => ['min_palabras' => 2, 'accion' => 'asignacion_24h'],
                    'medio' => ['min_palabras' => 3, 'accion' => 'asignacion_72h'],
                    'bajo' => ['min_palabras' => 1, 'accion' => 'asignacion_normal']
                ]
            ];
        }

        return $config->configuracion_json;
    }
}
