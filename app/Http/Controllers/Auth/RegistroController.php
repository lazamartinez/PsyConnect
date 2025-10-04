<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class RegistroController extends Controller
{
    public function mostrarFormularioRegistro()
    {
        return view('auth.registro');
    }

    public function registrar(Request $request)
    {
        Log::info('=== INICIANDO REGISTRO ===');
        Log::info('Tipo de usuario seleccionado:', ['tipo' => $request->tipo_usuario]);

        // Validación condicional más flexible
        $reglasValidacion = [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'contrasenia' => ['required', 'confirmed', Rules\Password::defaults()],
            'tipo_usuario' => 'required|in:paciente,psicologo,psiquiatra,nutricionista',
            'telefono' => 'nullable|string|max:20',
            'terminos' => 'required|accepted',
        ];

        // Solo validar campos de paciente si el tipo es paciente
        if ($request->tipo_usuario === 'paciente') {
            $reglasValidacion['fecha_nacimiento'] = 'required|date|before:today';
            $reglasValidacion['genero'] = 'required|in:masculino,femenino,otro,prefiero_no_decir';
            
            // Hacer opcionales los campos de profesional para pacientes
            $request->merge(['especialidad_principal' => null]);
            $request->merge(['matricula' => null]);
        } else {
            // Solo validar campos de profesional si NO es paciente
            $reglasValidacion['especialidad_principal'] = 'required|string|max:100';
            $reglasValidacion['matricula'] = 'nullable|string|max:50';
            
            // Hacer opcionales los campos de paciente para profesionales
            $request->merge(['fecha_nacimiento' => null]);
            $request->merge(['genero' => null]);
        }

        Log::info('Reglas de validación:', $reglasValidacion);

        try {
            $datosValidados = $request->validate($reglasValidacion);
            Log::info('Validación exitosa - datos validados:', $datosValidados);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ERRORES DE VALIDACIÓN:', $e->errors());
            throw $e;
        }

        try {
            DB::beginTransaction();

            // Crear usuario base
            $usuario = Usuario::create([
                'nombre' => $datosValidados['nombre'],
                'apellido' => $datosValidados['apellido'],
                'email' => $datosValidados['email'],
                'contrasenia' => Hash::make($datosValidados['contrasenia']),
                'tipo_usuario' => $datosValidados['tipo_usuario'],
                'telefono' => $datosValidados['telefono'] ?? null,
            ]);

            Log::info('Usuario creado:', ['id' => $usuario->id_usuario, 'tipo' => $usuario->tipo_usuario]);

            // Crear registro específico según tipo de usuario
            if ($datosValidados['tipo_usuario'] === 'paciente') {
                Paciente::create([
                    'usuario_id' => $usuario->id_usuario,
                    'fecha_nacimiento' => $datosValidados['fecha_nacimiento'],
                    'genero' => $datosValidados['genero'],
                ]);
                Log::info('Paciente creado exitosamente');
            } else {
                Profesional::create([
                    'usuario_id' => $usuario->id_usuario,
                    'especialidad_principal' => $datosValidados['especialidad_principal'],
                    'matricula' => $datosValidados['matricula'] ?? null,
                    'estado_verificacion' => 'pendiente',
                ]);
                Log::info('Profesional creado exitosamente');
            }

            DB::commit();

            // Autenticar al usuario después del registro
            auth()->login($usuario);
            Log::info('Usuario autenticado en el sistema');

            // Redirigir al dashboard
            return redirect()->route('dashboard')
                ->with('exito', '¡Registro completado exitosamente! Bienvenido a PsyConnect.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error en registro: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return back()
                ->withInput()
                ->with('error', 'Error en el registro: ' . $e->getMessage());
        }
    }
}