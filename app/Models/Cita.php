<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';
    protected $primaryKey = 'id_cita';

    protected $fillable = [
        'paciente_id',
        'profesional_id',
        'clinica_id',
        'fecha_cita',
        'hora_cita',
        'duracion_minutos',
        'tipo_consulta',
        'estado', // programada, confirmada, completada, cancelada
        'motivo_consulta',
        'notas',
        'recordatorio_enviado',
        'modalidad' // presencial, virtual
    ];

    protected $casts = [
        'fecha_cita' => 'date',
        'recordatorio_enviado' => 'boolean'
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class, 'clinica_id');
    }

    // Scopes
    public function scopeProgramadas($query)
    {
        return $query->where('estado', 'programada');
    }

    public function scopeParaHoy($query)
    {
        return $query->whereDate('fecha_cita', today());
    }

    public function scopeDelProfesional($query, $profesionalId)
    {
        return $query->where('profesional_id', $profesionalId);
    }
}