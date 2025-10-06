<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\TriajeInicial;
use Illuminate\Support\Facades\Log;

class TriajeMatchingService
{
    private $palabrasCriticas = [
        'suicidio', 'matar', 'morir', 'acabar con todo', 'no quiero vivir',
        'psicosis', 'alucinacion', 'delirio', 'esquizofrenia', 'bipolar'
    ];

    public function procesarTriaje(Paciente $paciente, string $descripcionSintomatologia)
    {
        Log::info('=== INICIANDO TRIAJE AUTOMÁTICO ===');

        // 1. Detectar nivel de riesgo
        $nivelRiesgo = $this->evaluarRiesgo($descripcionSintomatologia);
        
        // 2. Determinar especialidad requerida
        $especialidadRequerida = $this->determinarEspecialidad($nivelRiesgo, $descripcionSintomatologia);
        
        // 3. Buscar profesional disponible
        $profesionalAsignado = $this->buscarProfesionalCompatible($especialidadRequerida, $descripcionSintomatologia);
        
        // 4. Guardar resultados
        return $this->guardarTriaje($paciente, $descripcionSintomatologia, $nivelRiesgo, $especialidadRequerida, $profesionalAsignado);
    }

    private function evaluarRiesgo(string $descripcion)
    {
        $texto = mb_strtolower($descripcion);
        
        foreach ($this->palabrasCriticas as $palabra) {
            if (str_contains($texto, $palabra)) {
                return 'critico';
            }
        }

        // Lógica adicional para evaluar riesgo medio/bajo
        $palabrasAlerta = ['ansiedad', 'depresión', 'crisis', 'urgencia', 'desesperado'];
        $coincidencias = 0;
        
        foreach ($palabrasAlerta as $palabra) {
            if (str_contains($texto, $palabra)) {
                $coincidencias++;
            }
        }

        return $coincidencias >= 2 ? 'alto' : ($coincidencias >= 1 ? 'medio' : 'bajo');
    }

    private function determinarEspecialidad(string $nivelRiesgo, string $descripcion)
    {
        // Si es crítico, va directo a psiquiatra
        if ($nivelRiesgo === 'critico') {
            return 'psiquiatra';
        }

        // Para otros casos, buscar coincidencias con palabras clave de profesionales
        $texto = mb_strtolower($descripcion);
        
        $profesionales = Profesional::where('estado_verificacion', 'aprobado')
            ->whereIn('especialidad_principal', ['psicologo', 'psiquiatra'])
            ->get();

        $mejorPuntaje = 0;
        $mejorEspecialidad = 'psicologo'; // Por defecto

        foreach ($profesionales as $profesional) {
            $puntaje = $this->calcularCoincidencia($profesional, $texto);
            
            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $mejorEspecialidad = $profesional->especialidad_principal;
            }
        }

        return $mejorEspecialidad;
    }

    private function calcularCoincidencia(Profesional $profesional, string $texto)
    {
        if (empty($profesional->palabras_clave_especialidad)) {
            return 0;
        }

        $coincidencias = 0;
        foreach ($profesional->palabras_clave_especialidad as $palabra) {
            if (str_contains($texto, $palabra)) {
                $coincidencias++;
            }
        }

        return $coincidencias;
    }

    private function buscarProfesionalCompatible(string $especialidad, string $descripcion)
    {
        return Profesional::where('especialidad_principal', $especialidad)
            ->where('estado_verificacion', 'aprobado')
            ->where('disponibilidad_inmediata', true)
            ->get()
            ->sortByDesc(function($profesional) use ($descripcion) {
                return $this->calcularCoincidencia($profesional, $descripcion);
            })
            ->first();
    }

    private function guardarTriaje(Paciente $paciente, string $descripcion, string $nivelRiesgo, string $especialidad, ?Profesional $profesional)
    {
        $triaje = TriajeInicial::create([
            'paciente_id' => $paciente->id,
            'descripcion_sintomatologia' => $descripcion,
            'especialidad_recomendada' => $especialidad,
            'profesional_asignado_id' => $profesional?->id,
            'nivel_urgencia' => $nivelRiesgo,
            'fecha_triaje' => now(),
            'estado_triaje' => $profesional ? 'completado' : 'pendiente'
        ]);

        // Si se asignó un profesional, crear la relación
        if ($profesional) {
            $paciente->profesionales()->attach($profesional->id, [
                'fecha_asignacion' => now(),
                'puntuacion_compatibilidad' => $this->calcularCoincidencia($profesional, $descripcion),
                'estado' => 'activo',
                'motivo_asignacion' => 'Triaje automático - ' . $nivelRiesgo
            ]);
        }

        return [
            'triaje' => $triaje,
            'profesional_asignado' => $profesional,
            'especialidad_recomendada' => $especialidad,
            'nivel_riesgo' => $nivelRiesgo
        ];
    }
}