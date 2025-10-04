<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\TriajeInicial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function registro(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:usuarios',
            'contrasenia' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'telefono' => 'required|string|max:20',
            'tipo_usuario' => 'required|in:paciente,psicologo,psiquiatra,nutricionista,administrador',
            // Campos específicos de paciente para triaje
            'descripcion_sintomatologia' => 'required_if:tipo_usuario,paciente|string|min:50',
            'fecha_nacimiento' => 'required_if:tipo_usuario,paciente|date',
            'genero' => 'required_if:tipo_usuario,paciente|in:masculino,femenino,otro,prefiero_no_decir',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Crear usuario base
        $usuario = Usuario::create([
            'email' => $request->email,
            'contrasenia_hash' => Hash::make($request->contrasenia),
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
            'fecha_registro' => now(),
            'estado' => 'activo',
            'rol_id' => $this->obtenerRolId($request->tipo_usuario)
        ]);

        // Crear registro específico según tipo de usuario
        if ($request->tipo_usuario === 'paciente') {
            $this->registrarPaciente($usuario, $request);
        } else {
            $this->registrarProfesional($usuario, $request);
        }

        // Generar token de acceso
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'usuario' => $usuario,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'contrasenia' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->contrasenia, $usuario->contrasenia_hash)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        if ($usuario->estado !== 'activo') {
            return response()->json([
                'message' => 'Cuenta desactivada'
            ], 403);
        }

        // Actualizar último acceso
        $usuario->update(['ultimo_acceso' => now()]);

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'usuario' => $usuario,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    public function perfil(Request $request)
    {
        $usuario = $request->user();
        
        // Cargar relaciones según el tipo de usuario
        if ($usuario->esPaciente()) {
            $usuario->load('paciente');
        } elseif ($usuario->esProfesional()) {
            $usuario->load('profesional');
        }

        return response()->json([
            'usuario' => $usuario
        ]);
    }

    private function obtenerRolId($tipoUsuario)
    {
        $roles = [
            'paciente' => 1,
            'psicologo' => 2,
            'psiquiatra' => 3,
            'nutricionista' => 4,
            'administrador' => 5
        ];

        return $roles[$tipoUsuario] ?? 1;
    }

    private function registrarPaciente(Usuario $usuario, Request $request)
    {
        $paciente = Paciente::create([
            'usuario_id' => $usuario->id,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'genero' => $request->genero,
            'estado_tratamiento' => 'evaluacion',
            'fecha_alta' => now()
        ]);

        // Procesar triaje inicial
        if ($request->descripcion_sintomatologia) {
            $this->procesarTriajeInicial($paciente, $request->descripcion_sintomatologia);
        }

        return $paciente;
    }

    private function registrarProfesional(Usuario $usuario, Request $request)
    {
        return Profesional::create([
            'usuario_id' => $usuario->id,
            'especialidad_principal' => $request->tipo_usuario,
            'estado_verificacion' => 'pendiente',
            'modalidad_trabajo' => 'mixto'
        ]);
    }

    private function procesarTriajeInicial(Paciente $paciente, $descripcionSintomatologia)
    {
        // Aquí se integrará el algoritmo de matching con profesionales
        // Por ahora creamos el registro básico
        TriajeInicial::create([
            'paciente_id' => $paciente->id,
            'descripcion_sintomatologia' => $descripcionSintomatologia,
            'fecha_triaje' => now(),
            'estado_triaje' => 'pendiente'
        ]);
    }
}