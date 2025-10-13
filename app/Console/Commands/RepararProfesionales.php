<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Profesional;
use Illuminate\Support\Facades\Log;

class RepararProfesionales extends Command
{
    protected $signature = 'sistema:reparar-profesionales';
    protected $description = 'Repara profesionales sin palabras clave asignadas';

    public function handle()
    {
        $this->info('🔧 Reparando profesionales sin palabras clave...');

        $profesionales = Profesional::where('estado_verificacion', 'aprobado')->get();
        $reparados = 0;

        foreach ($profesionales as $profesional) {
            $palabrasActuales = $this->obtenerPalabrasClaveProfesional($profesional);
            
            if (empty($palabrasActuales)) {
                $palabrasBasicas = $this->generarPalabrasClaveBasicas($profesional->especialidad_principal);
                
                $profesional->update([
                    'palabras_clave_especialidad' => $palabrasBasicas
                ]);
                
                $this->info("✅ Profesional {$profesional->id} reparado con palabras: " . implode(', ', $palabrasBasicas));
                $reparados++;
            }
        }

        $this->info("🎯 Reparación completada: {$reparados} profesionales reparados");
        return 0;
    }

    private function obtenerPalabrasClaveProfesional($profesional)
    {
        $palabras = $profesional->palabras_clave_especialidad;
        
        if (is_string($palabras)) {
            $decoded = json_decode($palabras, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($palabras) ? $palabras : [];
    }

    private function generarPalabrasClaveBasicas($especialidad)
    {
        $palabrasPorEspecialidad = [
            'psicologo' => ['ansiedad', 'depresión', 'estrés', 'familia', 'pareja', 'trabajo', 'trauma', 'autoestima'],
            'psiquiatra' => ['medicamento', 'diagnóstico', 'trastorno', 'urgencia', 'hospital', 'psicosis', 'bipolar'],
            'nutricionista' => ['dieta', 'alimentación', 'peso', 'comida', 'nutrición', 'ejercicio', 'metabolismo']
        ];

        return $palabrasPorEspecialidad[$especialidad] ?? ['ansiedad', 'depresión', 'estrés'];
    }
}