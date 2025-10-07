<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PalabraClave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Especialidad;

class GestionPalabrasClaveController extends Controller
{
    public function index()
    {
        $palabrasClave = PalabraClave::with(['creador', 'especialidad'])
            ->orderBy('categoria')
            ->orderBy('palabra')
            ->get()
            ->groupBy('categoria');

        $categorias = PalabraClave::obtenerCategorias();
        $nivelesAlerta = PalabraClave::obtenerNivelesAlerta();

        // Obtener especialidades agrupadas por rol
        $especialidades = Especialidad::withCount('palabrasClave')
            ->activas()
            ->orderBy('rol_permitido')
            ->orderBy('nombre')
            ->get()
            ->groupBy('rol_permitido');

        $rolesPermitidos = Especialidad::obtenerRolesPermitidos();

        return view(
            'admin.palabras-clave.index',
            compact('palabrasClave', 'categorias', 'nivelesAlerta', 'especialidades', 'rolesPermitidos')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'palabra' => 'required|string|max:100|unique:palabras_clave,palabra',
            'categoria' => 'required|string|in:' . implode(',', array_keys(PalabraClave::obtenerCategorias())),
            'nivel_alerta' => 'required|string|in:' . implode(',', array_keys(PalabraClave::obtenerNivelesAlerta())),
            'peso_urgencia' => 'required|numeric|min:0.1|max:1.0',
            'especialidad_id' => 'required|exists:especialidades,id_especialidad',
            'descripcion' => 'nullable|string|max:500'
        ]);

        try {
            $especialidad = Especialidad::find($request->especialidad_id);

            $palabraClave = PalabraClave::create([
                'palabra' => strtolower($request->palabra),
                'categoria' => $request->categoria,
                'nivel_alerta' => $request->nivel_alerta,
                'peso_urgencia' => $request->peso_urgencia,
                'especialidad_id' => $request->especialidad_id,
                'especialidad_recomendada' => $especialidad->codigo,
                'descripcion' => $request->descripcion,
                'estado' => true,
                'creado_por' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Palabra clave creada exitosamente',
                'palabra' => $palabraClave
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear palabra clave: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $palabraClave = PalabraClave::findOrFail($id);

        $request->validate([
            'palabra' => [
                'required',
                'string',
                'max:100',
                Rule::unique('palabras_clave')->ignore($palabraClave->id_palabra_clave, 'id_palabra_clave')
            ],
            'categoria' => 'required|string|in:' . implode(',', array_keys(PalabraClave::obtenerCategorias())),
            'nivel_alerta' => 'required|string|in:' . implode(',', array_keys(PalabraClave::obtenerNivelesAlerta())),
            'peso_urgencia' => 'required|numeric|min:0.1|max:1.0',
            'especialidad_id' => 'required|exists:especialidades,id_especialidad',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'required|boolean'
        ]);

        try {
            $especialidad = Especialidad::find($request->especialidad_id);

            $palabraClave->update([
                'palabra' => strtolower($request->palabra),
                'categoria' => $request->categoria,
                'nivel_alerta' => $request->nivel_alerta,
                'peso_urgencia' => $request->peso_urgencia,
                'especialidad_id' => $request->especialidad_id,
                'especialidad_recomendada' => $especialidad->codigo,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Palabra clave actualizada exitosamente',
                'palabra' => $palabraClave
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar palabra clave: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $palabraClave = PalabraClave::findOrFail($id);
            $palabraClave->delete();

            return response()->json([
                'success' => true,
                'message' => 'Palabra clave eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar palabra clave: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerCodigoEspecialidad($especialidadId)
    {
        if (!$especialidadId) {
            return null;
        }

        $especialidad = Especialidad::find($especialidadId);
        return $especialidad ? $especialidad->codigo : null;
    }

    public function cambiarEstado($id)
    {
        try {
            $palabraClave = PalabraClave::findOrFail($id);
            $palabraClave->update([
                'estado' => !$palabraClave->estado
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'nuevo_estado' => $palabraClave->estado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }
    public function obtenerPorEspecialidad($especialidadId)
    {
        try {
            $palabrasClave = PalabraClave::where('especialidad_id', $especialidadId)
                ->activas()
                ->orderBy('categoria')
                ->orderBy('palabra')
                ->get()
                ->groupBy('categoria');

            return response()->json([
                'success' => true,
                'palabrasClave' => $palabrasClave
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener palabras clave: ' . $e->getMessage()
            ], 500);
        }
    }
}
