<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\Usuario;
use App\Models\Clinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class RegistroController extends Controller
{
    /**
     * Mostrar el formulario de registro
     */
    public function mostrarFormularioRegistro()
    {
        $clinicas = Clinica::where('estado', 'activa')
            ->orderBy('nombre')
            ->get();

        $tipo_usuario = 'paciente';

        return view('auth.registro', compact('clinicas', 'tipo_usuario'));
    }

    /**
     * Procesar el registro de usuarios
     */
    public function registrar(Request $request)
    {
        Log::info('=== INICIANDO REGISTRO SIMPLIFICADO ===');

        // Validación básica para todos los usuarios
        $reglasValidacion = [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'contrasenia' => ['required', 'confirmed', Rules\Password::defaults()],
            'tipo_usuario' => 'required|in:paciente,psicologo,psiquiatra,nutricionista',
            'telefono' => 'nullable|string|max:20',
            'terminos' => 'required|accepted',
        ];

        // Campos específicos para profesionales
        if ($request->tipo_usuario !== 'paciente') {
            $reglasValidacion['especialidad_principal'] = 'required|string|max:100';
            $reglasValidacion['matricula'] = 'nullable|string|max:50';
            $reglasValidacion['clinica_id'] = 'required|exists:clinicas,id_clinica';
        } else {
            // Solo datos básicos para pacientes
            $reglasValidacion['fecha_nacimiento'] = 'required|date|before:today';
            $reglasValidacion['genero'] = 'required|in:masculino,femenino,otro,prefiero_no_decir';
        }

        $datosValidados = $request->validate($reglasValidacion);

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

            // Crear registro específico
            if ($datosValidados['tipo_usuario'] === 'paciente') {
                $paciente = Paciente::create([
                    'usuario_id' => $usuario->id_usuario,
                    'fecha_nacimiento' => $datosValidados['fecha_nacimiento'],
                    'genero' => $datosValidados['genero'],
                ]);

                Log::info('Paciente registrado exitosamente:', [
                    'paciente_id' => $paciente->id,
                    'usuario_id' => $usuario->id_usuario
                ]);
            } else {
                $profesional = Profesional::create([
                    'usuario_id' => $usuario->id_usuario,
                    'especialidad_principal' => $datosValidados['especialidad_principal'],
                    'matricula' => $datosValidados['matricula'] ?? null,
                    'estado_verificacion' => 'pendiente', // Ya lo tienes bien!
                    'palabras_clave_especialidad' => json_encode([]), // Vacío hasta aprobación
                    'sintomas_atiende' => json_encode([]),
                    'disponibilidad_inmediata' => false, // No disponible hasta aprobación
                    'tiempo_respuesta_promedio_horas' => 0
                ]);

                // Asignar a clínica
                $profesional->clinicas()->attach($datosValidados['clinica_id'], [
                    'horario_trabajo' => $request->horario_trabajo ?? 'Lunes a Viernes 9:00-18:00',
                    'estado' => 'pendiente', // Pendiente de aprobación
                    'fecha_ingreso' => now()
                ]);

                Log::info('Profesional registrado pendiente de aprobación:', [
                    'profesional_id' => $profesional->id,
                    'clinica_id' => $datosValidados['clinica_id'],
                    'email' => $usuario->email
                ]);
            }

            DB::commit();

            // Autenticar al usuario
            auth()->login($usuario);

            return redirect()->route('dashboard')
                ->with('exito', '¡Registro completado exitosamente!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en registro: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Error en el registro: ' . $e->getMessage());
        }
    }
}
