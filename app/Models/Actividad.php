<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'duracion_estimada',
        'frecuencia_recomendada',
        'dificultad',
        'recursos_necesarios',
        'puntos_asignados',
        'categoria',
        'objetivos_especificos',
        'instrucciones_paso_paso',
        'materiales_adjuntos',
        'adaptaciones_posibles',
        'evaluacion_cumplimiento'
    ];

    protected $casts = [
        'duracion_estimada' => 'integer',
        'puntos_asignados' => 'integer',
        'recursos_necesarios' => 'array',
        'objetivos_especificos' => 'array',
        'instrucciones_paso_paso' => 'array',
        'materiales_adjuntos' => 'array',
        'adaptaciones_posibles' => 'array',
        'evaluacion_cumplimiento' => 'array',
    ];

    // Relaciones
    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'actividad_paciente')
                    ->withPivot([
                        'fecha_asignacion', 
                        'fecha_vencimiento', 
                        'estado', 
                        'comentarios',
                        'fecha_completado',
                        'evidencia',
                        'puntos_obtenidos'
                    ])
                    ->withTimestamps();
    }

    public function tratamientos()
    {
        return $this->belongsToMany(Tratamiento::class, 'actividad_tratamiento')
                    ->withTimestamps();
    }

    public function validaciones()
    {
        return $this->hasMany(ValidacionActividad::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopePorDificultad($query, $dificultad)
    {
        return $query->where('dificultad', $dificultad);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    // Métodos de utilidad
    public function getTotalCompletadasAttribute()
    {
        return $this->pacientes()->wherePivot('estado', 'completada')->count();
    }

    public function getTasaCompletitudAttribute()
    {
        $totalAsignadas = $this->pacientes()->count();
        $totalCompletadas = $this->getTotalCompletadasAttribute();
        
        return $totalAsignadas > 0 ? ($totalCompletadas / $totalAsignadas) * 100 : 0;
    }

    public function esAdecuadaParaIEA($valorIEA)
    {
        return match($this->dificultad) {
            'principiante' => $valorIEA >= 0 && $valorIEA <= 100,
            'intermedio' => $valorIEA >= 30 && $valorIEA <= 80,
            'avanzado' => $valorIEA >= 50 && $valorIEA <= 100,
            default => true
        };
    }
}