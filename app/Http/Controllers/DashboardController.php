<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Usuario;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        if ($usuario->esPaciente()) {
            return $this->dashboardPaciente($usuario);
        } elseif ($usuario->esProfesional()) {
            return $this->dashboardProfesional($usuario);
        } else {
            return $this->dashboardAdministrador($usuario);
        }
    }

    private function dashboardPaciente(Usuario $usuario)
    {
        $paciente = $usuario->paciente;
        $manuscritosRecientes = $paciente->manuscritos()
            ->with('indiceEstadoAnimico')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $ieaReciente = $paciente->indicesEstadoAnimico()
            ->orderBy('fecha_calculo', 'desc')
            ->first();

        return view('dashboard.paciente', compact('paciente', 'manuscritosRecientes', 'ieaReciente'));
    }

    private function dashboardProfesional(Usuario $usuario)
    {
        $profesional = $usuario->profesional;
        
        return view('dashboard.profesional', compact('profesional'));
    }

    private function dashboardAdministrador(Usuario $usuario)
    {
        return view('dashboard.administrador', compact('usuario'));
    }
}
