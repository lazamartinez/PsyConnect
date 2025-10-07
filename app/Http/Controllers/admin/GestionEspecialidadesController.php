<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GestionEspecialidadesController extends Controller
{
    public function index()
    {
        $especialidades = Especialidad::withCount(['profesionales', 'palabrasClave'])
            ->orderBy('rol_permitido')
            ->orderBy('nombre')
            ->get()
            ->groupBy('rol_permitido');

        $rolesPermitidos = Especialidad::obtenerRolesPermitidos();

        return view('admin.especialidades.index', compact('especialidades', 'rolesPermitidos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:especialidades,nombre',
            'codigo' => 'required|string|max:100|unique:especialidades,codigo',
            'descripcion' => 'required|string|max:500',
            'rol_permitido' => 'required|in:psicologo,psiquiatra,nutricionista,general',
            'color' => 'nullable|string|max:7',
            'icono' => 'nullable|string|max:50'
        ]);

        try {
            $especialidad = Especialidad::create([
                'nombre' => $request->nombre,
                'codigo' => $request->codigo,
                'descripcion' => $request->descripcion,
                'rol_permitido' => $request->rol_permitido,
                'color' => $request->color,
                'icono' => $request->icono,
                'activo' => true,
                'configuracion' => $this->generarConfiguracionPorRol($request->rol_permitido)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Especialidad creada exitosamente',
                'especialidad' => $especialidad
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear especialidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear especialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $especialidad = Especialidad::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:especialidades,nombre,' . $especialidad->id_especialidad . ',id_especialidad',
            'codigo' => 'required|string|max:100|unique:especialidades,codigo,' . $especialidad->id_especialidad . ',id_especialidad',
            'descripcion' => 'required|string|max:500',
            'rol_permitido' => 'required|in:psicologo,psiquiatra,nutricionista,general',
            'color' => 'nullable|string|max:7',
            'icono' => 'nullable|string|max:50',
            'activo' => 'required|boolean'
        ]);

        try {
            $especialidad->update([
                'nombre' => $request->nombre,
                'codigo' => $request->codigo,
                'descripcion' => $request->descripcion,
                'rol_permitido' => $request->rol_permitido,
                'color' => $request->color,
                'icono' => $request->icono,
                'activo' => $request->activo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Especialidad actualizada exitosamente',
                'especialidad' => $especialidad
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar especialidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar especialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $especialidad = Especialidad::findOrFail($id);

            // Verificar si hay profesionales usando esta especialidad
            if ($especialidad->profesionales()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la especialidad porque tiene profesionales asignados'
                ], 422);
            }

            $especialidad->delete();

            return response()->json([
                'success' => true,
                'message' => 'Especialidad eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar especialidad: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar especialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cambiarEstado($id)
    {
        try {
            $especialidad = Especialidad::findOrFail($id);
            $especialidad->update([
                'activo' => !$especialidad->activo
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'nuevo_estado' => $especialidad->activo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generarConfiguracionPorRol($rol)
    {
        $configuraciones = [
            'psicologo' => [
                'puede_recetar_medicamentos' => false,
                'nivel_urgencia_maximo' => 'alto',
                'derivar_a_psiquiatra' => ['psicosis', 'ideacion_suicida', 'trastorno_bipolar']
            ],
            'psiquiatra' => [
                'puede_recetar_medicamentos' => true,
                'nivel_urgencia_maximo' => 'critico',
                'puede_manejar_urgencias' => true
            ],
            'nutricionista' => [
                'puede_recetar_medicamentos' => false,
                'nivel_urgencia_maximo' => 'medio',
                'derivar_a_psicologo' => ['anorexia', 'bulimia', 'trastorno_alimenticio']
            ],
            'general' => [
                'puede_recetar_medicamentos' => false,
                'nivel_urgencia_maximo' => 'medio'
            ]
        ];

        return $configuraciones[$rol] ?? [];
    }

    public function obtenerPorRol($rol)
    {
        try {
            $especialidades = Especialidad::porRol($rol)
                ->activas()
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'success' => true,
                'especialidades' => $especialidades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener especialidades: ' . $e->getMessage()
            ], 500);
        }
    }
}