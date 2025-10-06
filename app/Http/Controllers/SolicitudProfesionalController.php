<?php

namespace App\Http\Controllers;

use App\Models\Profesional;
use App\Models\Clinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SolicitudAprobadaMail;
use App\Mail\SolicitudRechazadaMail;
use Illuminate\Support\Facades\Log;

class SolicitudProfesionalController extends Controller
{
    public function index()
    {
        $solicitudes = Profesional::with(['usuario', 'clinicas'])
            ->where('estado_verificacion', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.solicitudes-profesionales', compact('solicitudes'));
    }

    public function aprobar($id)
    {
        try {
            $profesional = Profesional::with('usuario')->findOrFail($id);
            
            $profesional->update([
                'estado_verificacion' => 'aprobado',
                'fecha_aprobacion' => now()
            ]);

            // Enviar email de aprobación
            Mail::to($profesional->usuario->email)->send(new SolicitudAprobadaMail($profesional));

            return redirect()->back()->with('exito', 'Solicitud aprobada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al aprobar solicitud: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al aprobar la solicitud: ' . $e->getMessage());
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
                'fecha_rechazo' => now()
            ]);

            // Enviar email de rechazo
            Mail::to($profesional->usuario->email)->send(new SolicitudRechazadaMail($profesional, $request->motivo_rechazo));

            return redirect()->back()->with('exito', 'Solicitud rechazada exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al rechazar solicitud: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al rechazar la solicitud: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $profesional = Profesional::with(['usuario', 'clinicas'])->findOrFail($id);
        return view('admin.detalle-solicitud', compact('profesional'));
    }
}