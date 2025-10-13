<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintomaEspecialidad extends Model
{
    use HasFactory;

    protected $table = 'sintomas_especialidad';
    protected $primaryKey = 'id_sintoma';

    protected $fillable = [
        'especialidad_id',
        'palabra_clave_id',
        'sintoma',
        'descripcion',
        'nivel_gravedad',
        'periodo_recomendado',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function palabraClave()
    {
        return $this->belongsTo(PalabraClave::class, 'palabra_clave_id');
    }

    public function configuracionesProfesionales()
    {
        return $this->hasMany(ConfiguracionProfesionalSintoma::class, 'sintoma_id');
    }
}