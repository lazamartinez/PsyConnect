<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function mostrarFormularioLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => 'required|email',
            'contrasenia' => 'required',
        ]);

        if (Auth::attempt(['email' => $credenciales['email'], 'password' => $credenciales['contrasenia']])) {
            $request->session()->regenerate();
            
            return redirect()->route('dashboard')
                ->with('exito', '¡Bienvenido de vuelta!');
        }

        return back()
            ->withInput()
            ->with('error', 'Las credenciales proporcionadas no son válidas.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')
            ->with('exito', 'Has cerrado sesión exitosamente.');
    }
}