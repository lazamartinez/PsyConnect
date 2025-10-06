<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Str;
use Carbon\Carbon;

class Profesional extends Model
{
    use HasFactory;

    protected $table = 'profesionales';
    protected $primaryKey = 'id_profesional';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'usuario_id',
        'especialidad_principal',
        'especialidades_secundarias',
        'matricula',
        'institucion',
        'estado_verificacion',
        'anios_experiencia',
        'bio',
        'modalidad_trabajo',
        'idiomas',
        'formacion_academica',
        'certificaciones',
        'calificacion_promedio',
        'palabras_clave_especialidad',
        'sintomas_atiende',
        'enfoque_terapeutico',
        'poblacion_objetivo',
        'disponibilidad_inmediata',
        'capacidad_pacientes',
        'tiempo_respuesta_promedio_horas',
        'fecha_aprobacion',
        'motivo_rechazo',
        'fecha_rechazo'
    ];

    protected $casts = [
        'especialidades_secundarias' => 'array',
        'idiomas' => 'array',
        'formacion_academica' => 'array',
        'certificaciones' => 'array',
        'palabras_clave_especialidad' => 'array',
        'sintomas_atiende' => 'array',
        'poblacion_objetivo' => 'array',
        'disponibilidad_inmediata' => 'boolean',
        'calificacion_promedio' => 'decimal:2',
        'anios_experiencia' => 'integer',
        'capacidad_pacientes' => 'integer',
        'tiempo_respuesta_promedio_horas' => 'integer',
        'fecha_aprobacion' => 'datetime',
        'fecha_rechazo' => 'datetime',
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_APROBADO = 'aprobado';
    const ESTADO_RECHAZADO = 'rechazado';

    // SCOPES
    public function scopePendientes($query)
    {
        return $query->where('estado_verificacion', self::ESTADO_PENDIENTE);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado_verificacion', self::ESTADO_APROBADO);
    }

    public function scopeRechazados($query)
    {
        return $query->where('estado_verificacion', self::ESTADO_RECHAZADO);
    }

    public function scopeConPalabrasClave($query, array $palabrasClave = [])
    {
        if (empty($palabrasClave)) return $query;

        return $query->where(function ($q) use ($palabrasClave) {
            foreach ($palabrasClave as $palabra) {
                $q->orWhereJsonContains('palabras_clave_especialidad', $palabra);
            }
        });
    }

    public function scopeConSintomas($query, array $sintomas = [])
    {
        if (empty($sintomas)) return $query;

        return $query->where(function ($q) use ($sintomas) {
            foreach ($sintomas as $sintoma) {
                $q->orWhereJsonContains('sintomas_atiende', $sintoma);
            }
        });
    }

    public function scopeDisponibles($query)
    {
        return $query->where('disponibilidad_inmediata', true)
            ->orWhere('tiempo_respuesta_promedio_horas', '<=', 72);
    }

    // ESTADOS
    public function estaAprobado() { return $this->estado_verificacion === self::ESTADO_APROBADO; }
    public function estaPendiente() { return $this->estado_verificacion === self::ESTADO_PENDIENTE; }
    public function estaRechazado() { return $this->estado_verificacion === self::ESTADO_RECHAZADO; }

    // RELACIONES
    public function usuario() { return $this->belongsTo(Usuario::class, 'usuario_id'); }

    public function clinicas()
    {
        return $this->belongsToMany(
            Clinica::class,
            'clinica_profesional'
        )->withPivot(['horario_trabajo', 'estado', 'fecha_ingreso'])
         ->withTimestamps();
    }

    public function pacientes()
    {
        return $this->belongsToMany(
            Paciente::class,
            'profesional_paciente',
            'profesional_id',
            'paciente_id'
        )->withPivot([
            'fecha_asignacion',
            'puntuacion_compatibilidad',
            'estado',
            'motivo_asignacion'
        ])->withTimestamps();
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'profesional_id');
    }

    public function triajesAsignados()
    {
        return $this->hasMany(TriajeInicial::class, 'profesional_asignado_id');
    }

    public function matchesPendientes()
    {
        return $this->triajesAsignados()->where('estado_triaje', 'pendiente');
    }

    public function sedePrincipal()
    {
        return $this->clinicas()->wherePivot('estado', 'activo')->first();
    }

    // PALABRAS CLAVE Y SINTOMAS
    public function getPalabrasClaveEspecialidadAttribute($value)
    {
        $palabras = is_array($value) ? $value : json_decode($value, true) ?? [];
        return collect($palabras)->filter()->map(fn($p) => mb_strtolower(trim($p)));
    }

    public function getSintomasAtiendeAttribute($value)
    {
        $sintomas = is_array($value) ? $value : json_decode($value, true) ?? [];
        return collect($sintomas)->filter()->map(fn($s) => mb_strtolower(trim($s)));
    }

    // MÉTODOS DE APROBACIÓN/RECHAZO
    public function aprobar()
    {
        $this->update([
            'estado_verificacion' => self::ESTADO_APROBADO,
            'fecha_aprobacion' => now(),
            'motivo_rechazo' => null,
            'fecha_rechazo' => null
        ]);
    }

    public function rechazar($motivo)
    {
        $this->update([
            'estado_verificacion' => self::ESTADO_RECHAZADO,
            'motivo_rechazo' => $motivo,
            'fecha_rechazo' => now(),
            'fecha_aprobacion' => null
        ]);
    }

    // MÉTODOS DE COMPATIBILIDAD
    public function calcularCompatibilidad($sintomasPaciente, $preferencias = [])
    {
        if (!$this->estaAprobado()) return 0;

        $puntuacion = 0;
        $puntuacion += $this->calcularCoincidenciaPalabras($sintomasPaciente) * 0.4;
        $puntuacion += $this->calcularPuntajeEspecialidad($sintomasPaciente) * 0.25;
        $puntuacion += $this->calcularPuntajeExperiencia() * 0.2;
        $puntuacion += $this->calcularPuntajeDisponibilidad() * 0.15;

        if (!empty($preferencias)) {
            $puntuacion += $this->calcularPuntajePreferencias($preferencias) * 0.1;
        }

        return min(100, round($puntuacion * 100, 2));
    }

    // MÉTODOS PLACEHOLDER PARA DASHBOARD
    public function compatibilidadPromedio()
    {
        $triajes = $this->triajesAsignados()->whereNotNull('confianza_asignacion')->get();
        if ($triajes->isEmpty()) return 0;
        return round($triajes->avg('confianza_asignacion'), 2);
    }

    public function nuevosPacientesEsteMes()
    {
        $start = Carbon::now()->startOfMonth();
        return $this->pacientes()->wherePivot('fecha_asignacion', '>=', $start)->count();
    }

    public function sesionesCompletadasEsteMes()
    {
        $start = Carbon::now()->startOfMonth();
        return $this->citas()
            ->where('fecha_cita', '>=', $start) // <- corregido
            ->where('estado', 'completada')
            ->count();
    }

    public function ingresosEsteMes()
    {
        $start = Carbon::now()->startOfMonth();
        return $this->citas()
            ->where('fecha_cita', '>=', $start); // <- corregido
    }

    // BOOT PARA UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_profesional)) {
                $model->id_profesional = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
