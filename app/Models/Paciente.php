<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';
    protected $primaryKey = 'id_paciente';

    protected $fillable = [
        'usuario_id',
        'fecha_nacimiento',
        'genero',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Relación con Manuscritos
    public function manuscritos()
    {
        return $this->hasMany(Manuscrito::class, 'paciente_id');
    }

    // Relación con Índices de Estado Anímico
    public function indicesEstadoAnimico()
    {
        return $this->hasMany(IndiceEstadoAnimico::class, 'paciente_id');
    }

    // Método para calcular edad
    public function obtenerEdad()
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }

    // Método para obtener IEA más reciente
    public function ieaReciente()
    {
        return $this->indicesEstadoAnimico()
            ->orderBy('fecha_calculo', 'desc')
            ->first();
    }

    // Método para obtener tendencia emocional
    public function obtenerTendenciaEmocional($dias = 30)
    {
        return $this->indicesEstadoAnimico()
            ->where('fecha_calculo', '>=', now()->subDays($dias))
            ->orderBy('fecha_calculo')
            ->get()
            ->pluck('valor_numerico');
    }
}