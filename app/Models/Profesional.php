<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesional extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'especialidad_principal',
        'especialidades_secundarias',
        'matricula',
        'institucion',
        'instituciones_secundarias',
        'horario_atencion',
        'estado_verificacion',
        'anios_experiencia',
        'bio',
        'tarifa_consulta',
        'modalidad_trabajo',
        'idiomas',
        'formacion_academica',
        'certificaciones',
        'calificacion_promedio'
    ];

    protected $casts = [
        'especialidades_secundarias' => 'array',
        'horario_atencion' => 'array',
        'idiomas' => 'array',
        'formacion_academica' => 'array',
        'certificaciones' => 'array',
        'calificacion_promedio' => 'decimal:2',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'paciente_profesional')
                    ->withPivot('fecha_asignacion', 'tipo_seguimiento')
                    ->withTimestamps();
    }

    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class);
    }

    public function sesiones()
    {
        return $this->hasMany(Sesion::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_principal', 'id');
    }

    // Scopes
    public function scopeVerificados($query)
    {
        return $query->where('estado_verificacion', 'verificado');
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estado_verificacion', 'verificado')
                    ->whereHas('usuario', function($q) {
                        $q->where('estado', 'activo');
                    });
    }

    // Métodos de utilidad
    public function getNombreCompletoAttribute()
    {
        return $this->usuario ? $this->usuario->nombre . ' ' . $this->usuario->apellido : null;
    }

    public function puedeAtenderPaciente($pacienteId)
    {
        return $this->pacientes()->where('paciente_id', $pacienteId)->exists();
    }

    public function getPacientesActivosAttribute()
    {
        return $this->pacientes()->whereHas('tratamientos', function($q) {
            $q->where('estado', 'activo');
        })->get();
    }
}