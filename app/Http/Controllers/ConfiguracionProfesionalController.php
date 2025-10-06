<?php

namespace App\Http\Controllers;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConfiguracionProfesionalController extends Controller
{
    // Catálogo de palabras clave predefinidas
    private $catalogoPalabrasClave = [
        'familia' => [
            'problemas_familiares',
            'conflictos_parentales', 
            'relaciones_familiares',
            'problemas_padres',
            'problemas_hijos',
            'crianza',
            'educacion_hijos'
        ],
        'pareja' => [
            'problemas_pareja',
            'relaciones_amorosas',
            'conflictos_pareja',
            'matrimonio',
            'divorcio',
            'separacion'
        ],
        'trabajo' => [
            'estres_laboral',
            'problemas_laborales',
            'ambiente_laboral',
            'desempleo',
            'cambio_laboral',
            'conflictos_superiores',
            'conflictos_compañeros'
        ],
        'ansiedad' => [
            'trastorno_ansiedad',
            'crisis_ansiedad',
            'ansiedad_generalizada',
            'ataque_panico',
            'angustia'
        ],
        'depresion' => [
            'trastorno_depresivo',
            'episodio_depresivo',
            'depresion_mayor',
            'tristeza_profunda',
            'desesperanza'
        ],
        'trauma' => [
            'trauma_psicologico',
            'estres_postraumatico',
            'eventos_traumaticos',
            'abus',
            'accidente'
        ],
        'alimentacion' => [
            'trastornos_alimenticios',
            'anorexia',
            'bulimia',
            'obesidad',
            'relacion_comida'
        ],
        'suicida' => [
            'ideacion_suicida',
            'pensamientos_suicidas',
            'crisis_suicida'
        ],
        'psicosis' => [
            'psicosis',
            'alucinaciones',
            'delirios',
            'esquizofrenia'
        ]
    ];

    public function mostrarConfiguracion()
    {
        $profesional = Profesional::where('usuario_id', Auth::id())->firstOrFail();
        
        if (!$profesional->estaAprobado()) {
            return redirect()->route('dashboard')
                ->with('error', 'Debes ser aprobado por el administrador para configurar tus palabras clave.');
        }

        return view('profesional.configuracion-palabras-clave', [
            'profesional' => $profesional,
            'catalogo' => $this->catalogoPalabrasClave
        ]);
    }

    public function actualizarPalabrasClave(Request $request)
    {
        $profesional = Profesional::where('usuario_id', Auth::id())->firstOrFail();

        if (!$profesional->estaAprobado()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado: debes ser aprobado por el administrador.'
            ], 403);
        }

        $request->validate([
            'palabras_clave' => 'required|array|min:1',
            'palabras_clave.*' => 'string|max:50'
        ]);

        try {
            $palabrasClave = $request->palabras_clave;
            $sintomasAtiende = $this->generarSintomasDesdePalabrasClave($palabrasClave);

            $profesional->update([
                'palabras_clave_especialidad' => $palabrasClave,
                'sintomas_atiende' => $sintomasAtiende
            ]);

            Log::info("Palabras clave actualizadas para profesional {$profesional->id}", [
                'palabras_clave' => $palabrasClave,
                'sintomas_atiende' => $sintomasAtiende
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Palabras clave actualizadas exitosamente',
                'palabras_clave' => $palabrasClave,
                'sintomas_atiende' => $sintomasAtiende
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar palabras clave: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las palabras clave: ' . $e->getMessage()
            ], 500);
        }
    }

    private function obtenerTodasPalabras()
    {
        $todas = [];
        foreach ($this->catalogoPalabrasClave as $categoria => $palabras) {
            $todas = array_merge($todas, $palabras);
        }
        return $todas;
    }

    private function generarSintomasDesdePalabrasClave(array $palabrasClave)
    {
        $sintomas = [];
        
        foreach ($palabrasClave as $palabra) {
            // Buscar la categoría de la palabra clave
            foreach ($this->catalogoPalabrasClave as $categoria => $palabras) {
                if (in_array($palabra, $palabras)) {
                    $sintomas[] = $this->mapearPalabraASintoma($palabra, $categoria);
                    break;
                }
            }
        }

        return array_unique($sintomas);
    }

    private function mapearPalabraASintoma($palabra, $categoria)
    {
        $mapeo = [
            'problemas_familiares' => 'Problemas en relaciones familiares',
            'conflictos_parentales' => 'Conflictos con padres',
            'problemas_hijos' => 'Problemas con hijos',
            'problemas_pareja' => 'Problemas de pareja',
            'divorcio' => 'Proceso de divorcio',
            'estres_laboral' => 'Estrés laboral',
            'desempleo' => 'Problemas de empleo',
            'trastorno_ansiedad' => 'Trastorno de ansiedad',
            'ataque_panico' => 'Ataques de pánico',
            'depresion_mayor' => 'Depresión mayor',
            'ideacion_suicida' => 'Ideación suicida',
            'psicosis' => 'Cuadros psicóticos'
        ];

        return $mapeo[$palabra] ?? str_replace('_', ' ', $palabra);
    }
}