<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'indice_estado_animico_id',
        'tipo',
        'nivel_urgencia',
        'descripcion',
        'fecha_generacion',
        'fecha_resolucion',
        'estado',
        'acciones_tomadas',
        'prioridad',
        'canal_notificacion',
        'receptor_principal',
        'receptores_secundarios',
        'evidencia_generacion',
        'protocolo_activado',
        'seguimiento_requerido'
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'acciones_tomadas' => 'array',
        'receptores_secundarios' => 'array',
        'evidencia_generacion' => 'array',
        'seguimiento_requerido' => 'boolean',
        'prioridad' => 'integer',
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function indiceEstadoAnimico()
    {
        return $this->belongsTo(IndiceEstadoAnimico::class);
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'receptor_principal');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeCriticas($query)
    {
        return $query->where('nivel_urgencia', 'critico');
    }

    public function scopePorRangoFecha($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_generacion', [$desde, $hasta]);
    }

    public function scopeNoResueltas($query)
    {
        return $query->where('estado', '!=', 'resuelta');
    }

    // Métodos de utilidad
    public function esCritica()
    {
        return $this->nivel_urgencia === 'critico';
    }

    public function requiereAccionInmediata()
    {
        return in_array($this->nivel_urgencia, ['alto', 'critico']) && 
               $this->estado === 'pendiente';
    }

    public function marcarComoAtendida($acciones = [])
    {
        $this->update([
            'estado' => 'atendida',
            'fecha_resolucion' => now(),
            'acciones_tomadas' => array_merge($this->acciones_tomadas ?? [], $acciones)
        ]);
    }

    public function getColorUrgenciaAttribute()
    {
        return match($this->nivel_urgencia) {
            'bajo' => 'success',
            'medio' => 'warning',
            'alto' => 'orange',
            'critico' => 'danger',
            default => 'secondary'
        };
    }
}