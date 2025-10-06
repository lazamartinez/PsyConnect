<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PacienteMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!$user->esPaciente()) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        // Verificar que el usuario tenga un perfil de paciente
        if (!$user->paciente) {
            return redirect()->route('dashboard')
                ->with('error', 'Perfil de paciente no encontrado.');
        }

        return $next($request);
    }
}