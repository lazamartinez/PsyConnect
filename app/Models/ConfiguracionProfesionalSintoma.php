<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionProfesionalSintoma extends Model
{
    use HasFactory;

    protected $table = 'configuracion_profesional_sintomas';
    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'profesional_id',
        'sintoma_id',
        'periodo_activo',
        'fecha_inicio',
        'fecha_fin',
        'max_pacientes',
        'prioridad',
        'activo'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'activo' => 'boolean'
    ];

    // Relaciones
    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }

    public function sintoma()
    {
        return $this->belongsTo(SintomaEspecialidad::class, 'sintoma_id');
    }
}