<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndiceEstadoAnimico extends Model
{
    use HasFactory;

    protected $table = 'indices_estado_animico';
    protected $primaryKey = 'id_iea';
    public $timestamps = true;

    protected $fillable = [
        'paciente_id',
        'manuscrito_id',
        'valor_numerico',
        'categoria_emotional',
        'confiabilidad_analisis',
        'fecha_calculo',
        'emocion_principal',
        'intensidad_principal',
        'resumen_analisis',
    ];

    protected $casts = [
        'valor_numerico' => 'float',
        'confiabilidad_analisis' => 'float',
        'intensidad_principal' => 'float',
        'fecha_calculo' => 'datetime',
    ];

    // Relación con Paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    // Relación con Manuscrito
    public function manuscrito()
    {
        return $this->belongsTo(Manuscrito::class, 'manuscrito_id');
    }

    // Descripción de la categoría
    public function obtenerDescripcionCategoria(): string
    {
        return match($this->categoria_emotional) {
            'muy_bajo' => 'Estado emocional que requiere atención inmediata',
            'bajo' => 'Momento difícil que necesita cuidado',
            'neutral' => 'Estado equilibrado y estable',
            'alto' => 'Bienestar emocional positivo',
            'muy_alto' => 'Excelente estado de ánimo y vitalidad',
            default => 'Estado en evaluación'
        };
    }

    // Color según categoría
    public function obtenerColorCategoria(): string
    {
        return match($this->categoria_emotional) {
            'muy_bajo' => '#DC2626',
            'bajo' => '#D97706',
            'neutral' => '#059669',
            'alto' => '#2563EB',
            'muy_alto' => '#7C3AED',
            default => '#6B7280'
        };
    }
}
