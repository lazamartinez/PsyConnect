<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsuariosSeeder::class,
            ClinicasSeeder::class,
            TriajeInicialSeeder::class,
            PalabrasClaveSeeder::class,
            ActualizarConfiguracionMatchingSeeder::class,
            MejorarPalabrasClaveProfesionalesSeeder::class,
            AsignarPalabrasClaveProfesionalesSeeder::class,
        ]);
    }
}
