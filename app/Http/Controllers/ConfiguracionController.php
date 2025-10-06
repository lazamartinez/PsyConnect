<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionMatching;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    public function configuracionMatching()
    {
        $pesos = ConfiguracionMatching::obtenerPesosMatching();
        $reglas = ConfiguracionMatching::obtenerReglasEspecialidad();
        $umbrales = ConfiguracionMatching::obtenerUmbrales();

        return response()->json([
            'pesos_matching' => $pesos,
            'reglas_especialidad' => $reglas,
            'umbrales' => $umbrales
        ]);
    }

    public function guardarConfiguracionMatching(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pesos' => 'required|array',
            'pesos.coincidencia_palabras_clave' => 'required|numeric|min:0|max:1',
            'pesos.especialidad_principal' => 'required|numeric|min:0|max:1',
            'pesos.experiencia_calificacion' => 'required|numeric|min:0|max:1',
            'pesos.disponibilidad' => 'required|numeric|min:0|max:1',
            'pesos.ubicacion' => 'required|numeric|min:0|max:1',
            'pesos.precio' => 'required|numeric|min:0|max:1',
            'umbrales' => 'required|array',
            'umbrales.compatibilidad_minima' => 'required|integer|min:0|max:100',
            'umbrales.confianza_minima_asignacion' => 'required|integer|min:0|max:100'
        ]);

        // Verificar que la suma de pesos sea 1
        $sumaPesos = array_sum($request->pesos);
        if (abs($sumaPesos - 1.0) > 0.01) {
            return response()->json([
                'errors' => ['pesos' => ['La suma de todos los pesos debe ser igual a 1.0']]
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Desactivar configuraciones anteriores
        ConfiguracionMatching::where('tipo_configuracion', 'pesos_matching')
                            ->orWhere('tipo_configuracion', 'umbrales')
                            ->update(['estado' => false]);

        // Guardar nueva configuración de pesos
        ConfiguracionMatching::create([
            'nombre_configuracion' => 'Pesos Matching v' . now()->format('YmdHis'),
            'tipo_configuracion' => 'pesos_matching',
            'configuracion_json' => $request->pesos,
            'estado' => true,
            'version' => now()->timestamp,
            'descripcion' => 'Configuración de pesos para algoritmo de matching'
        ]);

        // Guardar nueva configuración de umbrales
        ConfiguracionMatching::create([
            'nombre_configuracion' => 'Umbrales Sistema v' . now()->format('YmdHis'),
            'tipo_configuracion' => 'umbrales',
            'configuracion_json' => $request->umbrales,
            'estado' => true,
            'version' => now()->timestamp,
            'descripcion' => 'Umbrales del sistema de matching'
        ]);

        return response()->json([
            'message' => 'Configuración guardada exitosamente',
            'pesos' => $request->pesos,
            'umbrales' => $request->umbrales
        ]);
    }

    public function guardarReglasEspecialidad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reglas' => 'required|array',
            'reglas.psicologo' => 'required|array',
            'reglas.psiquiatra' => 'required|array',
            'reglas.nutricionista' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Desactivar reglas anteriores
        ConfiguracionMatching::where('tipo_configuracion', 'reglas_especialidad')
                            ->update(['estado' => false]);

        // Guardar nuevas reglas
        ConfiguracionMatching::create([
            'nombre_configuracion' => 'Reglas Especialidad v' . now()->format('YmdHis'),
            'tipo_configuracion' => 'reglas_especialidad',
            'configuracion_json' => $request->reglas,
            'estado' => true,
            'version' => now()->timestamp,
            'descripcion' => 'Reglas de especialidad para triaje automático'
        ]);

        return response()->json([
            'message' => 'Reglas de especialidad guardadas exitosamente',
            'reglas' => $request->reglas
        ]);
    }
}