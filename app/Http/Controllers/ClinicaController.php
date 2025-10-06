<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClinicaController extends Controller
{
    public function index(Request $request)
    {
        $query = Clinica::with(['administrador', 'profesionales.usuario']);

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('ciudad')) {
            $query->where('ciudad', 'like', "%{$request->ciudad}%");
        }

        $clinicas = $query->orderBy('nombre')->paginate(15);

        return response()->json([
            'clinicas' => $clinicas
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:500',
            'ciudad' => 'required|string|max:100',
            'provincia' => 'required|string|max:100',
            'pais' => 'required|string|max:100',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email',
            'coordenadas' => 'required|array',
            'horario_atencion' => 'required|array',
            'servicios_especializados' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Solo administradores pueden crear clínicas
        if (!$request->user()->esAdministrador()) {
            return response()->json([
                'message' => 'No autorizado para crear clínicas'
            ], 403);
        }

        $clinica = Clinica::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'ciudad' => $request->ciudad,
            'provincia' => $request->provincia,
            'pais' => $request->pais,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'coordenadas' => $request->coordenadas,
            'horario_atencion' => $request->horario_atencion,
            'servicios_especializados' => $request->servicios_especializados,
            'estado' => 'activa',
            'administrador_id' => $request->user()->id
        ]);

        return response()->json([
            'message' => 'Clínica creada exitosamente',
            'clinica' => $clinica
        ], 201);
    }

    public function updateEstado(Request $request, $id)
    {
        $clinica = Clinica::findOrFail($id);

        if (!$request->user()->esAdministrador()) {
            return response()->json([
                'message' => 'No autorizado para modificar clínicas'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:activa,inactiva,pendiente'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $clinica->update([
            'estado' => $request->estado
        ]);

        return response()->json([
            'message' => 'Estado de clínica actualizado exitosamente',
            'clinica' => $clinica
        ]);
    }

    public function asignarProfesional(Request $request, $clinicaId)
    {
        $clinica = Clinica::findOrFail($clinicaId);
        $profesional = Profesional::findOrFail($request->profesional_id);

        if (!$request->user()->esAdministrador()) {
            return response()->json([
                'message' => 'No autorizado para asignar profesionales'
            ], 403);
        }

        $clinica->profesionales()->attach($profesional->id, [
            'horario_trabajo' => $request->horario_trabajo,
            'estado' => 'activo',
            'fecha_ingreso' => now()
        ]);

        return response()->json([
            'message' => 'Profesional asignado a la clínica exitosamente'
        ]);
    }
}