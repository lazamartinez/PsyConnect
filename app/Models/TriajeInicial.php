<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TriajeInicial extends Model
{
    use HasFactory;

    protected $table = 'triajes_iniciales';
    protected $primaryKey = 'id_triaje';

    protected $fillable = [
        'paciente_id',
        'descripcion_sintomatologia',
        'analisis_sintomatologia',
        'especialidad_recomendada',
        'profesional_asignado_id',
        'nivel_urgencia',
        'fecha_triaje',
        'estado_triaje',
        'confianza_asignacion',
        'configuracion_utilizada'
    ];

    protected $casts = [
        'analisis_sintomatologia' => 'array',
        'configuracion_utilizada' => 'array',
        'fecha_triaje' => 'datetime',
        'confianza_asignacion' => 'decimal:2' // CORRECCIÓN: Cambiar a decimal
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function profesionalAsignado()
    {
        return $this->belongsTo(Profesional::class, 'profesional_asignado_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado_triaje', 'pendiente');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado_triaje', 'completado');
    }

    public function scopePorUrgencia($query, $nivelUrgencia)
    {
        return $query->where('nivel_urgencia', $nivelUrgencia);
    }

    // Métodos de utilidad
    public function esUrgente()
    {
        return in_array($this->nivel_urgencia, ['alto', 'critico']);
    }

    public function tieneProfesionalAsignado()
    {
        return !is_null($this->profesional_asignado_id);
    }

    public function obtenerResumenAnalisis()
    {
        $analisis = $this->analisis_sintomatologia ?? [];
        
        if (empty($analisis)) {
            return 'Sin análisis disponible';
        }

        $sintomas = $analisis['sintomas_detectados'] ?? [];
        $palabrasClave = $analisis['palabras_clave_encontradas'] ?? [];
        
        $resumen = sprintf(
            "Nivel de urgencia: %s. %d síntomas detectados. %d palabras clave encontradas.",
            $this->nivel_urgencia,
            count($sintomas),
            count($palabrasClave)
        );

        if (!empty($sintomas)) {
            $resumen .= " Síntomas: " . implode(', ', $sintomas);
        }

        return $resumen;
    }
}