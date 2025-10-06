<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionMatching;
use App\Models\Clinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ConfiguracionAvanzadaController extends Controller
{
    public function index()
    {
        $clinicas = Clinica::where('estado', 'activa')->get();
        $clinicaSeleccionada = request('clinica_id') ?? $clinicas->first()->id_clinica ?? null;

        $configuraciones = [
            'pesos_matching' => ConfiguracionMatching::obtenerPesosMatching($clinicaSeleccionada),
            'reglas_especialidad' => ConfiguracionMatching::obtenerReglasEspecialidad($clinicaSeleccionada),
            'umbrales' => ConfiguracionMatching::obtenerUmbrales($clinicaSeleccionada),
            'reglas_triaje' => ConfiguracionMatching::obtenerReglasTriaje($clinicaSeleccionada)
        ];

        return view('admin.configuracion-avanzada', compact(
            'clinicas',
            'clinicaSeleccionada',
            'configuraciones'
        ));
    }

    public function guardarConfiguracionCompleta(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'clinica_id' => 'required|exists:clinicas,id_clinica',
                'tipo_configuracion' => 'required|in:pesos_matching,reglas_especialidad,umbrales,reglas_triaje',
                'configuracion' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::info("Guardando configuración: " . json_encode($request->all()));

            // Desactivar configuraciones anteriores para esta clínica
            ConfiguracionMatching::where('tipo_configuracion', $request->tipo_configuracion)
                ->where('clinica_id', $request->clinica_id)
                ->update(['estado' => false]);

            // Crear nueva configuración
            $configuracion = ConfiguracionMatching::create([
                'nombre_configuracion' => ucfirst(str_replace('_', ' ', $request->tipo_configuracion)) . ' - ' . now()->format('d/m/Y H:i'),
                'tipo_configuracion' => $request->tipo_configuracion,
                'configuracion_json' => $request->configuracion,
                'clinica_id' => $request->clinica_id,
                'estado' => true,
                'version' => now()->timestamp,
                'descripcion' => 'Configuración personalizada para clínica ' . $request->clinica_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuración guardada exitosamente',
                'configuracion' => $configuracion
            ]);
        } catch (\Exception $e) {
            Log::error("Error al guardar configuración: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerConfiguracionClinica($clinicaId, $tipo)
    {
        try {
            $configuracion = ConfiguracionMatching::obtenerConfiguracionActiva($tipo, $clinicaId);

            return response()->json([
                'success' => true,
                'configuracion' => $configuracion ? $configuracion->configuracion_json : []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración: ' . $e->getMessage()
            ], 500);
        }
    }
}
