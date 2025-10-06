<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PalabraClave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GestionPalabrasClaveController extends Controller
{
    public function index()
    {
        $palabrasClave = PalabraClave::with('creador')
            ->orderBy('categoria')
            ->orderBy('palabra')
            ->get()
            ->groupBy('categoria');

        $categorias = PalabraClave::obtenerCategorias();
        $nivelesAlerta = PalabraClave::obtenerNivelesAlerta();

        return view('admin.palabras-clave.index', compact('palabrasClave', 'categorias', 'nivelesAlerta'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'palabra' => 'required|string|max:100|unique:palabras_clave,palabra',
            'categoria' => 'required|string|in:' . implode(',', array_keys(PalabraClave::obtenerCategorias())),
            'nivel_alerta' => 'required|string|in:' . implode(',', array_keys(PalabraClave::obtenerNivelesAlerta())),
            'peso_urgencia' => 'required|numeric|min:0.1|max:1.0',
            'especialidad_recomendada' => 'required|in:psicologo,psiquiatra,nutricionista',
            'descripcion' => 'nullable|string|max:500'
        ]);

        try {
            $palabraClave = PalabraClave::create([
                'palabra' => strtolower($request->palabra),
                'categoria' => $request->categoria,
                'nivel_alerta' => $request->nivel_alerta,
                'peso_urgencia' => $request->peso_urgencia,
                'especialidad_recomendada' => $request->especialidad_recomendada,
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
            'especialidad_recomendada' => 'required|in:psicologo,psiquiatra,nutricionista',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'required|boolean'
        ]);

        try {
            $palabraClave->update([
                'palabra' => strtolower($request->palabra),
                'categoria' => $request->categoria,
                'nivel_alerta' => $request->nivel_alerta,
                'peso_urgencia' => $request->peso_urgencia,
                'especialidad_recomendada' => $request->especialidad_recomendada,
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
}