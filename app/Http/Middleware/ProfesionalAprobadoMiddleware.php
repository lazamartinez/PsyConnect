<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProfesionalAprobadoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if ($user->esProfesional()) {
            $profesional = $user->profesional;
            
            if (!$profesional) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Perfil profesional no encontrado.');
            }
            
            if ($profesional->estado_verificacion === 'pendiente') {
                return redirect()->route('dashboard')
                    ->with('info', 'Tu cuenta está pendiente de aprobación. Podrás acceder a todas las funciones una vez que el administrador apruebe tu solicitud.');
            }
            
            if ($profesional->estado_verificacion === 'rechazado') {
                return redirect()->route('dashboard')
                    ->with('error', 'Tu solicitud ha sido rechazada. Contacta al administrador para más información.');
            }
        }
        
        return $next($request);
    }
}