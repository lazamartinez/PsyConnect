<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {

        $fecha = now();
        // ---------------------------
        // Crear usuarios
        // ---------------------------
        $usuarios = [
            [
                'id_usuario' => 1,
                'nombre' => 'Admin',
                'apellido' => 'Uno',
                'email' => 'admin1@example.com',
                'contrasenia' => Hash::make('password123'),
                'tipo_usuario' => 'administrador',
                'estado' => true,
                'telefono' => '123456789',
                'fecha_registro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_usuario' => 2,
                'nombre' => 'Admin',
                'apellido' => 'Dos',
                'email' => 'admin2@example.com',
                'contrasenia' => Hash::make('password123'),
                'tipo_usuario' => 'administrador',
                'estado' => true,
                'telefono' => '987654321',
                'fecha_registro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_usuario' => 3,
                'nombre' => 'Paciente',
                'apellido' => 'Test',
                'email' => 'paciente@example.com',
                'contrasenia' => Hash::make('password123'),
                'tipo_usuario' => 'paciente',
                'estado' => true,
                'telefono' => '3761234567',
                'fecha_registro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_usuario' => 4,
                'nombre' => 'Psicologo',
                'apellido' => 'Test',
                'email' => 'psicologo@example.com',
                'contrasenia' => Hash::make('password123'),
                'tipo_usuario' => 'psicologo',
                'estado' => true,
                'telefono' => '3762345678',
                'fecha_registro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_usuario' => 5,
                'nombre' => 'Psiquiatra',
                'apellido' => 'Test',
                'email' => 'psiquiatra@example.com',
                'contrasenia' => Hash::make('password123'),
                'tipo_usuario' => 'psiquiatra',
                'estado' => true,
                'telefono' => '3763456789',
                'fecha_registro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_usuario' => 6,
                'nombre' => 'Nutricionista',
                'apellido' => 'Test',
                'email' => 'nutricionista@example.com',
                'contrasenia' => Hash::make('password123'),
                'tipo_usuario' => 'nutricionista',
                'estado' => true,
                'telefono' => '3764567890',
                'fecha_registro' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('usuarios')->insert($usuarios);

        // ---------------------------
        // Crear administradores
        // ---------------------------
        DB::table('administradors')->insert([
            ['id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ---------------------------
        // Crear pacientes
        // ---------------------------
        DB::table('pacientes')->insert([
            [
                'id_paciente' => Str::uuid(),
                'usuario_id' => 3, // paciente@example.com
                'fecha_nacimiento' => '1990-01-01',
                'genero' => 'masculino',
                'direccion' => 'Calle Falsa 123',
                'ciudad' => 'Posadas',
                'contacto_emergencia_nombre' => 'Juan Perez',
                'contacto_emergencia_telefono' => '+54 376 123-4567',
                'alergias' => 'Ninguna',
                'medicamentos_actuales' => 'Ninguno',
                'estado_tratamiento' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ---------------------------
        // Crear profesionales
        // ---------------------------
        DB::table('profesionales')->insert([
            [
                'id_profesional'       => Str::uuid(),
                'usuario_id'           => 4,
                'especialidad_principal' => 'Psicología Clínica',
                'matricula'            => 'PSI-1234',
                'institucion'          => 'Instituto Psico',
                'estado_verificacion'  => 'aprobado', // Asegurate de usar solo estos 3: 'pendiente', 'aprobado', 'rechazado'
                'anios_experiencia'    => 5,
                'bio'                  => 'Psicólogo con experiencia en terapia cognitiva.',
                'created_at'           => $fecha,
                'updated_at'           => $fecha,
                'fecha_aprobacion'     => $fecha,
                'motivo_rechazo'       => null,
                'fecha_rechazo'        => null,
            ],
            [
                'id_profesional'       => Str::uuid(),
                'usuario_id'           => 5,
                'especialidad_principal' => 'Psiquiatría General',
                'matricula'            => 'PSQ-5678',
                'institucion'          => 'Clínica Salud Mental',
                'estado_verificacion'  => 'aprobado',
                'anios_experiencia'    => 8,
                'bio'                  => 'Psiquiatra especialista en trastornos del ánimo.',
                'created_at'           => $fecha,
                'updated_at'           => $fecha,
                'fecha_aprobacion'     => $fecha,
                'motivo_rechazo'       => null,
                'fecha_rechazo'        => null,
            ],
            [
                'id_profesional'       => Str::uuid(),
                'usuario_id'           => 6,
                'especialidad_principal' => 'Nutrición',
                'matricula'            => 'NUT-11223',
                'institucion'          => 'Clínica Salud',
                'estado_verificacion'  => 'aprobado',
                'anios_experiencia'    => 4,
                'bio'                  => 'Nutricionista especializado en dietas balanceadas.',
                'created_at'           => $fecha,
                'updated_at'           => $fecha,
                'fecha_aprobacion'     => $fecha,
                'motivo_rechazo'       => null,
                'fecha_rechazo'        => null,
            ],
        ]);
    }
}
