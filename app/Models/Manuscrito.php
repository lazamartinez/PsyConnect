<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Manuscrito extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'manuscritos';
    protected $primaryKey = 'id_manuscrito';
    public $incrementing = false; // UUIDs no son autoincrement
    protected $keyType = 'string';

    protected $fillable = [
        'paciente_id',
        'imagen_original',
        'imagen_procesada',
        'texto_digitalizado',
        'confianza_ocr',
        'fecha_captura',
        'fecha_procesamiento',
        'estado_procesamiento',
    ];

    protected $casts = [
        'fecha_captura' => 'datetime',
        'fecha_procesamiento' => 'datetime',
        'confianza_ocr' => 'decimal:2',
    ];

    // Relación con Paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id', 'id_paciente');
    }

    // Relación con Índice de Estado Anímico
    public function indiceEstadoAnimico()
    {
        return $this->hasOne(IndiceEstadoAnimico::class, 'manuscrito_id', 'id_manuscrito');
    }

    // Método para verificar si está procesado
    public function estaProcesado()
    {
        return $this->estado_procesamiento === 'procesado';
    }

    // Método para obtener la URL de la imagen
    public function obtenerUrlImagen()
    {
        return $this->imagen_original ? asset('storage/' . $this->imagen_original) : null;
    }

    // Método scope para manuscritos procesados
    public function scopeProcesados($query)
    {
        return $query->where('estado_procesamiento', 'procesado');
    }
}
