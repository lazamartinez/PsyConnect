<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Especialidad;

class PalabraClave extends Model
{
    use HasFactory;

    protected $table = 'palabras_clave';
    protected $primaryKey = 'id_palabra_clave';

    protected $fillable = [
        'palabra',
        'categoria',
        'nivel_alerta',
        'peso_urgencia',
        'especialidad_recomendada',
        'especialidad_id',
        'sinonimos',
        'descripcion',
        'estado',
        'creado_por',
        'version_configuracion'
    ];

    protected $casts = [
        'peso_urgencia' => 'decimal:3',
        'estado' => 'boolean',
        'sinonimos' => 'array'
    ];

    // Niveles de alerta disponibles
    const ALERTA_CRITICO = 'critico';
    const ALERTA_ALTO = 'alto';
    const ALERTA_MEDIO = 'medio';
    const ALERTA_BAJO = 'bajo';
    const ALERTA_ESTABLE = 'estable';

    // Categorías predefinidas
    const CATEGORIA_FAMILIA = 'familia';
    const CATEGORIA_PAREJA = 'pareja';
    const CATEGORIA_TRABAJO = 'trabajo';
    const CATEGORIA_ANSIEDAD = 'ansiedad';
    const CATEGORIA_DEPRESION = 'depresion';
    const CATEGORIA_TRAUMA = 'trauma';
    const CATEGORIA_ALIMENTACION = 'alimentacion';
    const CATEGORIA_PSICOSIS = 'psicosis';
    const CATEGORIA_SUICIDA = 'suicida';
    const CATEGORIA_ADICCION = 'adiccion';
    const CATEGORIA_SUENO = 'sueno';
    const CATEGORIA_IRA = 'ira';
    const CATEGORIA_DUELO = 'duelo';

    public static function obtenerNivelesAlerta()
    {
        return [
            self::ALERTA_CRITICO => 'Crítico',
            self::ALERTA_ALTO => 'Alto',
            self::ALERTA_MEDIO => 'Medio',
            self::ALERTA_BAJO => 'Bajo',
            self::ALERTA_ESTABLE => 'Estable'
        ];
    }

    public static function obtenerCategorias()
    {
        return [
            self::CATEGORIA_FAMILIA => 'Familia',
            self::CATEGORIA_PAREJA => 'Pareja',
            self::CATEGORIA_TRABAJO => 'Trabajo',
            self::CATEGORIA_ANSIEDAD => 'Ansiedad',
            self::CATEGORIA_DEPRESION => 'Depresión',
            self::CATEGORIA_TRAUMA => 'Trauma',
            self::CATEGORIA_ALIMENTACION => 'Alimentación',
            self::CATEGORIA_PSICOSIS => 'Psicosis',
            self::CATEGORIA_SUICIDA => 'Ideación Suicida',
            self::CATEGORIA_ADICCION => 'Adicción',
            self::CATEGORIA_SUENO => 'Sueño',
            self::CATEGORIA_IRA => 'Ira',
            self::CATEGORIA_DUELO => 'Duelo'
        ];
    }

    public static function obtenerEspecialidades()
    {
        return [
            'psicologo' => 'Psicólogo',
            'psiquiatra' => 'Psiquiatra',
            'nutricionista' => 'Nutricionista'
        ];
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', true);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorNivelAlerta($query, $nivel)
    {
        return $query->where('nivel_alerta', $nivel);
    }

    public function scopePorEspecialidad($query, $especialidad)
    {
        return $query->where('especialidad_recomendada', $especialidad);
    }

    public function scopeCriticas($query)
    {
        return $query->where('nivel_alerta', self::ALERTA_CRITICO);
    }

    public function especialidad()
    {
        return $this->belongsTo(\App\Models\Especialidad::class, 'especialidad_id', 'id_especialidad');
    }

    // Relaciones
    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    // Métodos de utilidad
    public function esCritica()
    {
        return $this->nivel_alerta === self::ALERTA_CRITICO;
    }

    public function getColorAlertaAttribute()
    {
        return match ($this->nivel_alerta) {
            self::ALERTA_CRITICO => 'bg-red-100 text-red-800 border-red-300',
            self::ALERTA_ALTO => 'bg-orange-100 text-orange-800 border-orange-300',
            self::ALERTA_MEDIO => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            self::ALERTA_BAJO => 'bg-blue-100 text-blue-800 border-blue-300',
            self::ALERTA_ESTABLE => 'bg-green-100 text-green-800 border-green-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300'
        };
    }

    public function getIconoAlertaAttribute()
    {
        return match ($this->nivel_alerta) {
            self::ALERTA_CRITICO => 'fas fa-exclamation-triangle',
            self::ALERTA_ALTO => 'fas fa-exclamation-circle',
            self::ALERTA_MEDIO => 'fas fa-info-circle',
            self::ALERTA_BAJO => 'fas fa-info',
            self::ALERTA_ESTABLE => 'fas fa-check-circle',
            default => 'fas fa-circle'
        };
    }

    public function obtenerSinonimosParaBusqueda(): array
    {
        $sinonimos = $this->sinonimos ?? [];
        array_unshift($sinonimos, $this->palabra);

        return $sinonimos;
    }

    public function actualizarDesdeConfiguracion(array $configuracion): bool
    {
        return $this->update([
            'nivel_alerta' => $configuracion['nivel_alerta'] ?? $this->nivel_alerta,
            'peso_urgencia' => $configuracion['peso_urgencia'] ?? $this->peso_urgencia,
            'especialidad_recomendada' => $configuracion['especialidad_recomendada'] ?? $this->especialidad_recomendada,
            'version_configuracion' => now()->timestamp
        ]);
    }

    public static function importarDesdeArray(array $palabrasConfiguracion): array
    {
        $resultados = [];

        foreach ($palabrasConfiguracion as $config) {
            $palabra = self::firstOrNew(['palabra' => $config['palabra']]);

            $palabra->fill([
                'categoria' => $config['categoria'],
                'nivel_alerta' => $config['nivel_alerta'],
                'peso_urgencia' => $config['peso_urgencia'],
                'especialidad_recomendada' => $config['especialidad_recomendada'],
                'sinonimos' => $config['sinonimos'] ?? [],
                'descripcion' => $config['descripcion'] ?? null,
                'estado' => $config['estado'] ?? true,
                'version_configuracion' => now()->timestamp
            ]);

            if ($palabra->save()) {
                $resultados[] = $palabra;
            }
        }

        return $resultados;
    }

    public static function obtenerEstadisticas()
    {
        return [
            'total_palabras' => self::count(),
            'palabras_activas' => self::activas()->count(),
            'por_categoria' => self::activas()
                ->select('categoria', DB::raw('count(*) as total'))
                ->groupBy('categoria')
                ->get()
                ->pluck('total', 'categoria'),
            'por_especialidad' => self::activas()
                ->select('especialidad_recomendada', DB::raw('count(*) as total'))
                ->groupBy('especialidad_recomendada')
                ->get()
                ->pluck('total', 'especialidad_recomendada'),
            'palabras_criticas' => self::criticas()->count()
        ];
    }
}
