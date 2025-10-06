<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use App\Models\Clinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GestionProfesionalesController extends Controller
{
    public function index()
    {
        // Obtener la clínica del administrador (asumiendo que el usuario admin tiene relación con clínica)
        $clinicaId = Auth::user()->clinica_id ?? Clinica::first()->id_clinica;

        $solicitudes = Profesional::with(['usuario', 'clinicas'])
            ->whereHas('clinicas', function ($query) use ($clinicaId) {
                $query->where('clinicas.id_clinica', $clinicaId)
                    ->where('clinica_profesional.estado', 'pendiente');
            })
            ->where('estado_verificacion', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.solicitudes-profesionales', compact('solicitudes'));
    }

    public function aprobar($id)
    {
        try {
            $profesional = Profesional::with(['usuario', 'clinicas'])->findOrFail($id);

            $profesional->update([
                'estado_verificacion' => 'aprobado',
                'disponibilidad_inmediata' => true, // ✅ Ahora disponible
                'fecha_aprobacion' => now(),
                'motivo_rechazo' => null,
                'fecha_rechazo' => null
            ]);

            // Actualizar relación con clínica
            if ($profesional->clinicas->isNotEmpty()) {
                $profesional->clinicas()->updateExistingPivot($profesional->clinicas->first()->id_clinica, [
                    'estado' => 'activo'
                ]);
            }

            Log::info("Profesional aprobado: " . $profesional->id, [
                'usuario' => $profesional->usuario->email,
                'especialidad' => $profesional->especialidad_principal,
                'aprobado_por' => Auth::user()->email
            ]);

            // ✅ PODEMOS AGREGAR AQUÍ UNA NOTIFICACIÓN POR EMAIL AL PROFESIONAL
            // Mail::to($profesional->usuario->email)->send(new ProfesionalAprobadoMail($profesional));

            return response()->json([
                'success' => true,
                'message' => 'Profesional aprobado exitosamente. Ahora puede configurar sus palabras clave y recibir pacientes.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al aprobar profesional: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar el profesional: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|min:10|max:500'
        ]);

        try {
            $profesional = Profesional::with(['usuario', 'clinicas'])->findOrFail($id);

            $profesional->update([
                'estado_verificacion' => 'rechazado',
                'motivo_rechazo' => $request->motivo_rechazo,
                'fecha_rechazo' => now()
            ]);

            return redirect()->back()
                ->with('exito', 'Profesional rechazado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al rechazar profesional: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $profesional = Profesional::with(['usuario', 'clinicas'])->findOrFail($id);
        return view('admin.detalle-solicitud', compact('profesional'));
    }
}
