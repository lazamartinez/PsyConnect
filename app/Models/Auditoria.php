<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'tipo_accion',
        'entidad_afectada',
        'id_entidad_afectada',
        'fecha_hora_accion',
        'ip_origen',
        'user_agent',
        'dispositivo',
        'ubicacion_aproximada',
        'resultado_accion',
        'detalles_accion',
        'cambios_realizados',
        'nivel_severidad',
        'requiere_seguimiento',
        'ticket_seguimiento'
    ];

    protected $casts = [
        'fecha_hora_accion' => 'datetime',
        'cambios_realizados' => 'array',
        'requiere_seguimiento' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}