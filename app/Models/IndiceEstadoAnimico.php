<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndiceEstadoAnimico extends Model
{
    use HasFactory;

    protected $table = 'indices_estado_animico';
    protected $primaryKey = 'id_indice';

    protected $fillable = [
        'paciente_id',
        'manuscrito_id',
        'valor_numerico',
        'categoria_emotional',
        'confiabilidad_analisis',
        'emocion_principal',
        'intensidad_principal',
        'resumen_analisis',
        'fecha_calculo'
    ];

    protected $casts = [
        'valor_numerico' => 'float',
        'confiabilidad_analisis' => 'float',
        'intensidad_principal' => 'float',
        'fecha_calculo' => 'datetime'
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function manuscrito()
    {
        return $this->belongsTo(Manuscrito::class, 'manuscrito_id');
    }
}