<?php

namespace App\Console\Commands;

use App\Models\Especialidad;
use Illuminate\Console\Command;
use App\Models\Profesional;
use App\Models\PalabraClave;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RepararSistemaMatching extends Command
{
    protected $signature = 'sistema:reparar-matching';
    protected $description = 'Repara automáticamente el sistema de matching';

    public function handle()
    {
        $this->info('=== INICIANDO REPARACIÓN DEL SISTEMA DE MATCHING ===');

        // 1. Normalizar especialidades
        $this->normalizarEspecialidades();

        // 2. Reparar profesionales sin palabras clave (PostgreSQL compatible)
        $this->repararProfesionalesSinPalabrasClavePostgres();

        // 3. Verificar palabras clave del sistema
        $this->verificarPalabrasClaveSistema();

        // 4. Crear profesional de emergencia si es necesario
        $this->crearProfesionalEmergenciaSiNecesario();

        // 5. Verificar y crear especialidades si no existen
        Especialidad::inicializarEspecialidades();
        $this->info('✅ Especialidades verificadas');

        // 6. Asignar especialidades a profesionales sin especialidad_id
        $profesionalesSinEspecialidad = Profesional::whereNull('especialidad_id');

        foreach ($profesionalesSinEspecialidad as $profesional) {
            $especialidad = Especialidad::where('codigo', $profesional->especialidad_principal)
                ->orWhere('nombre', 'like', "%{$profesional->especialidad_principal}%")
                ->first();
                
            if ($especialidad) {
                $profesional->especialidad_id = $especialidad->id_especialidad;
                $profesional->save();
            }
        }

        $this->info("✅ {$profesionalesSinEspecialidad->count()} profesionales actualizados");

        // 3. Verificar palabras clave del sistema
        $totalPalabras = PalabraClave::where('estado', true)->count();
        if ($totalPalabras < 10) {
            $this->call('db:seed', ['--class' => 'PalabrasClaveSeeder']);
            $this->info('✅ Palabras clave del sistema verificadas');
        }

        $this->info('=== REPARACIÓN COMPLETADA ===');
        
        Log::info('Reparación del sistema completada');
        return Command::SUCCESS;
    }

    private function normalizarEspecialidades()
    {
        $this->info('Normalizando especialidades...');

        $mapeoEspecialidades = [
            'Psicoanálisis' => 'psicologo',
            'Psiquiatría General' => 'psiquiatra',
            'Psiquiatria General' => 'psiquiatra',
            'Psicología' => 'psicologo',
            'Psicologia' => 'psicologo',
            'Nutrición' => 'nutricionista',
            'Nutricion' => 'nutricionista',
            'Psicólogo' => 'psicologo',
            'Psiquiatra' => 'psiquiatra',
            'Nutricionista' => 'nutricionista'
        ];

        $profesionales = Profesional::all();
        $actualizados = 0;

        foreach ($profesionales as $profesional) {
            $original = $profesional->especialidad_principal;
            $normalizada = $mapeoEspecialidades[$original] ?? $original;

            if ($original !== $normalizada) {
                $profesional->update(['especialidad_principal' => $normalizada]);
                $this->line("✓ {$original} -> {$normalizada}");
                $actualizados++;
            }
        }

        $this->info("Especialidades normalizadas: {$actualizados}");
    }

    /**
     * Versión PostgreSQL-compatible para reparar profesionales
     */
    private function repararProfesionalesSinPalabrasClavePostgres()
    {
        $this->info('Reparando profesionales sin palabras clave (PostgreSQL)...');

        // Consulta PostgreSQL-compatible
        $profesionalesReparar = Profesional::where(function($query) {
            // Para PostgreSQL, manejamos los JSON como texto
            $query->where(DB::raw('palabras_clave_especialidad::text'), '=', '""[]""')
                  ->orWhere(DB::raw('palabras_clave_especialidad::text'), '=', '"[]"')
                  ->orWhere(DB::raw('palabras_clave_especialidad::text'), '=', '[]')
                  ->orWhere(DB::raw('palabras_clave_especialidad::text'), '=', '')
                  ->orWhereNull('palabras_clave_especialidad')
                  ->orWhere(DB::raw('palabras_clave_especialidad::text'), 'LIKE', '%""%');
        })->get();

        $this->info("Profesionales a reparar: " . $profesionalesReparar->count());

        $palabrasPorEspecialidad = [
            'psicologo' => ['ansiedad', 'depresión', 'estrés', 'familia', 'trabajo', 'pareja', 'crisis', 'trauma', 'duelo', 'autoestima'],
            'psiquiatra' => ['medicamento', 'diagnóstico', 'trastorno', 'psicosis', 'bipolar', 'hospital', 'urgencia', 'fármaco', 'esquizofrenia', 'tdah'],
            'nutricionista' => ['dieta', 'alimentación', 'peso', 'nutrición', 'ejercicio', 'obesidad', 'metabolismo', 'comida', 'adelgazar', 'salud']
        ];

        $reparados = 0;

        foreach ($profesionalesReparar as $profesional) {
            try {
                $palabras = $palabrasPorEspecialidad[$profesional->especialidad_principal] ?? ['ansiedad', 'depresión', 'estrés'];

                $profesional->update([
                    'palabras_clave_especialidad' => $palabras,
                    'disponibilidad_inmediata' => true,
                    'sintomas_atiende' => $this->generarSintomasDesdePalabrasClave($palabras)
                ]);

                $this->line("✓ {$profesional->especialidad_principal}: " . implode(', ', array_slice($palabras, 0, 3)) . '...');
                $reparados++;
                
            } catch (\Exception $e) {
                $this->error("Error reparando profesional {$profesional->id}: " . $e->getMessage());
            }
        }

        $this->info("Profesionales reparados: {$reparados}");
    }

    private function verificarPalabrasClaveSistema()
    {
        $this->info('Verificando palabras clave del sistema...');

        $palabrasBasicas = [
            ['palabra' => 'ansiedad', 'categoria' => 'ansiedad', 'nivel_alerta' => 'medio', 'peso_urgencia' => 0.8, 'especialidad_recomendada' => 'psicologo'],
            ['palabra' => 'depresión', 'categoria' => 'depresion', 'nivel_alerta' => 'medio', 'peso_urgencia' => 0.8, 'especialidad_recomendada' => 'psicologo'],
            ['palabra' => 'estrés', 'categoria' => 'estres', 'nivel_alerta' => 'medio', 'peso_urgencia' => 0.7, 'especialidad_recomendada' => 'psicologo'],
            ['palabra' => 'familia', 'categoria' => 'familia', 'nivel_alerta' => 'bajo', 'peso_urgencia' => 0.6, 'especialidad_recomendada' => 'psicologo'],
            ['palabra' => 'trabajo', 'categoria' => 'trabajo', 'nivel_alerta' => 'bajo', 'peso_urgencia' => 0.6, 'especialidad_recomendada' => 'psicologo'],
            ['palabra' => 'pareja', 'categoria' => 'pareja', 'nivel_alerta' => 'bajo', 'peso_urgencia' => 0.6, 'especialidad_recomendada' => 'psicologo'],
            ['palabra' => 'psicosis', 'categoria' => 'psicosis', 'nivel_alerta' => 'critico', 'peso_urgencia' => 1.0, 'especialidad_recomendada' => 'psiquiatra'],
            ['palabra' => 'suicidio', 'categoria' => 'suicida', 'nivel_alerta' => 'critico', 'peso_urgencia' => 1.0, 'especialidad_recomendada' => 'psiquiatra'],
            ['palabra' => 'medicamento', 'categoria' => 'tratamiento', 'nivel_alerta' => 'bajo', 'peso_urgencia' => 0.5, 'especialidad_recomendada' => 'psiquiatra'],
            ['palabra' => 'dieta', 'categoria' => 'alimentacion', 'nivel_alerta' => 'bajo', 'peso_urgencia' => 0.5, 'especialidad_recomendada' => 'nutricionista'],
            ['palabra' => 'alimentación', 'categoria' => 'alimentacion', 'nivel_alerta' => 'bajo', 'peso_urgencia' => 0.6, 'especialidad_recomendada' => 'nutricionista'],
            ['palabra' => 'peso', 'categoria' => 'alimentacion', 'nivel_alerta' => 'medio', 'peso_urgencia' => 0.7, 'especialidad_recomendada' => 'nutricionista'],
        ];

        $creadas = 0;

        foreach ($palabrasBasicas as $palabraData) {
            $existe = PalabraClave::where('palabra', $palabraData['palabra'])->first();
            
            if (!$existe) {
                try {
                    PalabraClave::create(array_merge($palabraData, [
                        'estado' => true,
                        'creado_por' => 1,
                        'descripcion' => 'Palabra clave básica del sistema'
                    ]));
                    $creadas++;
                    $this->line("✓ Palabra creada: {$palabraData['palabra']}");
                } catch (\Exception $e) {
                    $this->error("Error creando palabra {$palabraData['palabra']}: " . $e->getMessage());
                }
            }
        }

        $this->info("Palabras clave verificadas. Nuevas: {$creadas}");
    }

    private function crearProfesionalEmergenciaSiNecesario()
    {
        $profesionalesAprobados = Profesional::where('estado_verificacion', 'aprobado')->count();
        
        if ($profesionalesAprobados === 0) {
            $this->info('Creando profesional de emergencia...');
            $this->crearProfesionalEmergencia();
        } else {
            $this->info("Profesionales aprobados existentes: {$profesionalesAprobados}");
        }
    }

    private function crearProfesionalEmergencia()
    {
        try {
            // Verificar si ya existe el usuario de emergencia
            $usuarioExistente = Usuario::where('email', 'profesional.emergencia@psyconnect.com')->first();
            
            if ($usuarioExistente) {
                $this->info('Usuario de emergencia ya existe');
                return $usuarioExistente->profesional;
            }

            // Crear el usuario
            $usuario = Usuario::create([
                'nombre' => 'Profesional',
                'apellido' => 'Emergencia',
                'email' => 'profesional.emergencia@psyconnect.com',
                'contrasenia' => Hash::make('password123'),
                'tipo_usuario' => 'psicologo',
                'telefono' => '+54 376 000-0000'
            ]);

            // Crear el profesional
            $profesional = Profesional::create([
                'usuario_id' => $usuario->id,
                'especialidad_principal' => 'psicologo',
                'estado_verificacion' => 'aprobado',
                'disponibilidad_inmediata' => true,
                'anios_experiencia' => 5,
                'calificacion_promedio' => 4.5,
                'tiempo_respuesta_promedio_horas' => 24,
                'capacidad_pacientes' => 10,
                'palabras_clave_especialidad' => [
                    'ansiedad', 'depresión', 'estrés', 'crisis', 
                    'familia', 'trabajo', 'pareja', 'trauma'
                ],
                'sintomas_atiende' => [
                    'trastorno de ansiedad',
                    'depresión',
                    'estrés crónico',
                    'crisis emocional',
                    'problemas familiares',
                    'estrés laboral',
                    'problemas de pareja',
                    'trauma psicológico'
                ],
                'bio' => 'Profesional de emergencia del sistema. Especializado en primeros auxilios psicológicos y apoyo emocional inmediato.'
            ]);

            $this->info('✓ Profesional de emergencia creado: ' . $profesional->id);
            return $profesional;

        } catch (\Exception $e) {
            $this->error('Error al crear profesional de emergencia: ' . $e->getMessage());
            return null;
        }
    }

    private function generarSintomasDesdePalabrasClave(array $palabrasClave)
    {
        $mapeoSintomas = [
            'ansiedad' => 'trastorno de ansiedad',
            'depresión' => 'trastorno depresivo',
            'estrés' => 'estrés crónico',
            'familia' => 'problemas familiares',
            'trabajo' => 'estrés laboral',
            'pareja' => 'problemas de pareja',
            'crisis' => 'crisis emocional',
            'trauma' => 'trauma psicológico',
            'duelo' => 'proceso de duelo',
            'psicosis' => 'trastornos psicóticos',
            'medicamento' => 'tratamiento farmacológico',
            'dieta' => 'problemas alimenticios',
            'alimentación' => 'trastornos alimenticios',
            'peso' => 'problemas de peso'
        ];

        $sintomas = [];
        foreach ($palabrasClave as $palabra) {
            if (isset($mapeoSintomas[$palabra])) {
                $sintomas[] = $mapeoSintomas[$palabra];
            } else {
                $sintomas[] = $palabra;
            }
        }

        return array_unique($sintomas);
    }
}