<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfesionalMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('=== PROFESIONAL MIDDLEWARE EJECUTADO ===');
        
        if (!Auth::check()) {
            Log::warning('Usuario no autenticado');
            return redirect()->route('login');
        }

        $user = Auth::user();
        Log::info("Usuario: {$user->id}, Tipo: {$user->tipo_usuario}");
        
        if (!$user->esProfesional()) {
            Log::warning("Usuario {$user->id} no es profesional. Tipo: {$user->tipo_usuario}");
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        // Verificar que el usuario tenga un perfil de profesional
        if (!$user->profesional) {
            Log::warning("Usuario {$user->id} no tiene perfil profesional");
            return redirect()->route('dashboard')
                ->with('error', 'Perfil de profesional no encontrado.');
        }

        Log::info("Usuario {$user->id} pasó todas las validaciones");
        return $next($request);
    }
}