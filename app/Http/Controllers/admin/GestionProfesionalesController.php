<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use App\Models\Clinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GestionProfesionalesController extends Controller
{
    public function index()
    {
        try {
            // Obtener todas las solicitudes pendientes de profesionales
            $solicitudes = Profesional::with(['usuario', 'clinicas'])
                ->where('estado_verificacion', 'pendiente')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info("Cargando solicitudes de profesionales: " . $solicitudes->count() . " encontradas");

            return view('admin.solicitudes-profesionales', compact('solicitudes'));
        } catch (\Exception $e) {
            Log::error("Error al cargar solicitudes: " . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Error al cargar las solicitudes: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $profesional = Profesional::with(['usuario', 'clinicas'])->findOrFail($id);
            return view('admin.detalle-solicitud', compact('profesional'));
        } catch (\Exception $e) {
            Log::error("Error al mostrar detalle de solicitud: " . $e->getMessage());
            return redirect()->route('admin.solicitudes.index')
                ->with('error', 'Error al cargar los detalles de la solicitud');
        }
    }

    public function aprobar($id)
    {
        try {
            $profesional = Profesional::with('usuario')->findOrFail($id);
            
            $profesional->update([
                'estado_verificacion' => 'aprobado',
                'fecha_aprobacion' => now(),
                'motivo_rechazo' => null,
                'fecha_rechazo' => null
            ]);

            Log::info("Profesional aprobado: " . $profesional->id, [
                'usuario' => $profesional->usuario->email,
                'aprobado_por' => Auth::user()->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al aprobar solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|min:10|max:500'
        ]);

        try {
            $profesional = Profesional::with('usuario')->findOrFail($id);
            
            $profesional->update([
                'estado_verificacion' => 'rechazado',
                'motivo_rechazo' => $request->motivo_rechazo,
                'fecha_rechazo' => now(),
                'fecha_aprobacion' => null
            ]);

            Log::info("Profesional rechazado: " . $profesional->id, [
                'usuario' => $profesional->usuario->email,
                'motivo' => $request->motivo_rechazo,
                'rechazado_por' => Auth::user()->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al rechazar solicitud: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerEstadisticas()
    {
        try {
            $totalPendientes = Profesional::where('estado_verificacion', 'pendiente')->count();
            $totalAprobados = Profesional::where('estado_verificacion', 'aprobado')->count();
            $totalRechazados = Profesional::where('estado_verificacion', 'rechazado')->count();

            return response()->json([
                'success' => true,
                'estadisticas' => [
                    'pendientes' => $totalPendientes,
                    'aprobados' => $totalAprobados,
                    'rechazados' => $totalRechazados
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }
}