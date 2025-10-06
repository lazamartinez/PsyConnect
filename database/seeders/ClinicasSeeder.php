<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicasSeeder extends Seeder
{
    public function run(): void
    {
        // Suponiendo que ya existen administradores con id 1 y 2
        $clinicas = [
            [
                'nombre' => 'Clínica San José',
                'direccion' => 'Av. San Martín 1234',
                'ciudad' => 'Posadas',
                'provincia' => 'Misiones',
                'pais' => 'Argentina',
                'codigo_postal' => '3300',
                'telefono' => '+54 376 123-4567',
                'email' => 'contacto@clinicasanjose.com',
                'coordenadas' => json_encode(['lat' => -27.367, 'lng' => -55.896]),
                'estado' => 'activa',
                'horario_atencion' => json_encode([
                    'lunes_viernes' => '08:00-20:00',
                    'sabado' => '09:00-13:00',
                    'domingo' => 'Cerrado'
                ]),
                'servicios_especializados' => json_encode(['Cardiología', 'Pediatría', 'Traumatología']),
                'administrador_id' => 1, // debe existir en administradors
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Clínica del Sol',
                'direccion' => 'Calle 9 de Julio 567',
                'ciudad' => 'Posadas',
                'provincia' => 'Misiones',
                'pais' => 'Argentina',
                'codigo_postal' => '3302',
                'telefono' => '+54 376 234-5678',
                'email' => 'info@clinicadelsol.com',
                'coordenadas' => json_encode(['lat' => -27.38, 'lng' => -55.88]),
                'estado' => 'activa',
                'horario_atencion' => json_encode([
                    'lunes_viernes' => '07:00-19:00',
                    'sabado' => '08:00-12:00',
                    'domingo' => 'Cerrado'
                ]),
                'servicios_especializados' => json_encode(['Dermatología', 'Neurología', 'Ginecología']),
                'administrador_id' => 2, // debe existir en administradors
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Clínica Vida',
                'direccion' => 'Av. Uruguay 890',
                'ciudad' => 'Posadas',
                'provincia' => 'Misiones',
                'pais' => 'Argentina',
                'codigo_postal' => '3304',
                'telefono' => '+54 376 345-6789',
                'email' => 'contacto@clinicavida.com',
                'coordenadas' => json_encode(['lat' => -27.36, 'lng' => -55.90]),
                'estado' => 'pendiente',
                'horario_atencion' => json_encode([
                    'lunes_viernes' => '08:00-18:00',
                    'sabado' => 'Cerrado',
                    'domingo' => 'Cerrado'
                ]),
                'servicios_especializados' => json_encode(['Oncología', 'Psiquiatría']),
                'administrador_id' => null, // opcional
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('clinicas')->insert($clinicas);
    }
}
