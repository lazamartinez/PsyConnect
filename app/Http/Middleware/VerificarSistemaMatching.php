<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Profesional;
use App\Models\PalabraClave;
use Illuminate\Support\Facades\Log;

class VerificarSistemaMatching
{
    public function handle(Request $request, Closure $next)
    {
        // Solo verificar en rutas relevantes y no en AJAX
        if ($this->debeVerificarSistema($request)) {
            $this->verificarYRepararAutomaticamente();
        }

        return $next($request);
    }

    private function debeVerificarSistema(Request $request)
    {
        return $request->is('dashboard') && 
               !$request->ajax() && 
               !$request->isMethod('post');
    }

    private function verificarYRepararAutomaticamente()
    {
        try {
            // Verificar si hay profesionales sin palabras clave
            $profesionalesProblematicos = Profesional::where(function($query) {
                $query->whereIn('palabras_clave_especialidad', ['""[]""', '[]', ''])
                      ->orWhereNull('palabras_clave_especialidad');
            })->count();

            // Verificar palabras clave del sistema
            $palabrasClaveCount = PalabraClave::where('estado', true)->count();

            if ($profesionalesProblematicos > 0 || $palabrasClaveCount < 5) {
                Log::info('Sistema de matching requiere reparación automática');
                $this->ejecutarReparacionRapida();
            }

        } catch (\Exception $e) {
            Log::error('Error en verificación automática: ' . $e->getMessage());
        }
    }

    private function ejecutarReparacionRapida()
    {
        // Reparación rápida - solo lo esencial
        $this->repararProfesionalesCriticos();
        $this->crearPalabrasClaveBasicas();
    }

    private function repararProfesionalesCriticos()
    {
        $profesionales = Profesional::where(function($query) {
            $query->whereIn('palabras_clave_especialidad', ['""[]""', '[]', ''])
                  ->orWhereNull('palabras_clave_especialidad');
        })->get();

        $palabrasBasicas = [
            'psicologo' => ['ansiedad', 'depresión', 'estrés'],
            'psiquiatra' => ['medicamento', 'diagnóstico', 'trastorno'],
            'nutricionista' => ['dieta', 'alimentación', 'peso']
        ];

        foreach ($profesionales as $profesional) {
            $palabras = $palabrasBasicas[$profesional->especialidad_principal] ?? ['ansiedad', 'depresión', 'estrés'];
            $profesional->update([
                'palabras_clave_especialidad' => $palabras,
                'disponibilidad_inmediata' => true
            ]);
        }
    }

    private function crearPalabrasClaveBasicas()
    {
        $palabrasBasicas = [
            ['palabra' => 'ansiedad', 'especialidad_recomendada' => 'psicologo'],
            ['palabra' => 'depresión', 'especialidad_recomendada' => 'psicologo'],
            ['palabra' => 'estrés', 'especialidad_recomendada' => 'psicologo'],
        ];

        foreach ($palabrasBasicas as $palabraData) {
            PalabraClave::firstOrCreate(
                ['palabra' => $palabraData['palabra']],
                array_merge($palabraData, [
                    'categoria' => 'general',
                    'nivel_alerta' => 'medio',
                    'peso_urgencia' => 0.7,
                    'estado' => true,
                    'creado_por' => 1
                ])
            );
        }
    }
}