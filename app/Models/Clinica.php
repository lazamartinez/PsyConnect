<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinica extends Model
{
    use HasFactory;

    protected $table = 'clinicas';
    protected $primaryKey = 'id_clinica';

    protected $fillable = [
        'nombre',
        'direccion',
        'ciudad',
        'provincia',
        'pais',
        'codigo_postal',
        'telefono',
        'email',
        'coordenadas', // Para geolocalización
        'estado', // activa/inactiva/pendiente
        'horario_atencion',
        'servicios_especializados',
        'administrador_id' // Quién la habilitó
    ];

    protected $casts = [
        'coordenadas' => 'array',
        'horario_atencion' => 'array',
        'servicios_especializados' => 'array'
    ];

    // Relaciones
    public function administrador()
    {
        return $this->belongsTo(Usuario::class, 'administrador_id');
    }

    public function profesionales()
    {
        return $this->belongsToMany(Profesional::class, 'clinica_profesional', 'clinica_id', 'profesional_id')
                    ->withPivot('horario_trabajo', 'estado')
                    ->withTimestamps();
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'clinica_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopePorUbicacion($query, $ciudad, $provincia = null)
    {
        $query->where('ciudad', $ciudad);
        if ($provincia) {
            $query->where('provincia', $provincia);
        }
        return $query;
    }
}