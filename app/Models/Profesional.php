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
        'especialidad_id',
        'especialidad_principal',
        'especialidades_secundarias',
        'subespecialidades',
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
        'subespecialidades' => 'array',
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

    public function scopePorEspecialidad($query, $especialidadId)
    {
        return $query->where('especialidad_id', $especialidadId);
    }

    public function scopePorEspecialidadPrincipal($query, $especialidad)
    {
        return $query->where('especialidad_principal', $especialidad);
    }

    // ESTADOS
    public function estaAprobado()
    {
        return $this->estado_verificacion === self::ESTADO_APROBADO;
    }

    public function estaPendiente()
    {
        return $this->estado_verificacion === self::ESTADO_PENDIENTE;
    }

    public function estaRechazado()
    {
        return $this->estado_verificacion === self::ESTADO_RECHAZADO;
    }

    public function configuracionesSintomas()
    {
        return $this->hasMany(ConfiguracionProfesionalSintoma::class, 'profesional_id');
    }

    // RELACIONES
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class,'especialidad_principal', 'nombre');
    }

    public function clinicas()
    {
        return $this->belongsToMany(
            Clinica::class,
            'clinica_profesional',
            'profesional_id',
            'clinica_id'
        )->withPivot(['horario_trabajo', 'estado', 'fecha_ingreso'])
            ->withTimestamps();
    }

    public function pacientes()
    {
        return $this->belongsToMany(
            Paciente::class,
            'paciente_profesional',
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
        return $this->pacientes()->wherePivot('estado', 'pendiente');
    }

    public function sedePrincipal()
    {
        return $this->clinicas()->wherePivot('estado', 'activo')->first();
    }

    // PALABRAS CLAVE Y SINTOMAS - MEJORADOS
    public function getPalabrasClaveEspecialidadAttribute($value)
    {
        $palabras = is_array($value) ? $value : json_decode($value, true) ?? [];
        return collect($palabras)->filter()->map(fn($p) => mb_strtolower(trim($p)))->toArray();
    }

    public function setPalabrasClaveEspecialidadAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['palabras_clave_especialidad'] = json_encode(
                collect($value)->filter()->map(fn($p) => mb_strtolower(trim($p)))->toArray()
            );
        } else {
            $this->attributes['palabras_clave_especialidad'] = $value;
        }
    }

    public function getSintomasAtiendeAttribute($value)
    {
        $sintomas = is_array($value) ? $value : json_decode($value, true) ?? [];
        return collect($sintomas)->filter()->map(fn($s) => mb_strtolower(trim($s)))->toArray();
    }

    public function setSintomasAtiendeAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['sintomas_atiende'] = json_encode(
                collect($value)->filter()->map(fn($s) => mb_strtolower(trim($s)))->toArray()
            );
        } else {
            $this->attributes['sintomas_atiende'] = $value;
        }
    }

    // MÉTODOS DE APROBACIÓN/RECHAZO
    public function aprobar()
    {
        $this->update([
            'estado_verificacion' => self::ESTADO_APROBADO,
            'fecha_aprobacion' => now(),
            'motivo_rechazo' => null,
            'fecha_rechazo' => null,
            'disponibilidad_inmediata' => true // Activar disponibilidad al aprobar
        ]);
    }

    public function rechazar($motivo)
    {
        $this->update([
            'estado_verificacion' => self::ESTADO_RECHAZADO,
            'motivo_rechazo' => $motivo,
            'fecha_rechazo' => now(),
            'fecha_aprobacion' => null,
            'disponibilidad_inmediata' => false // Desactivar disponibilidad al rechazar
        ]);
    }

    // MÉTODOS DE COMPATIBILIDAD MEJORADOS
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

    private function calcularCoincidenciaPalabras($sintomasPaciente)
    {
        $palabrasClaveProfesional = $this->obtenerPalabrasClaveCompletas();
        $palabrasClavePaciente = $sintomasPaciente['palabras_clave_encontradas'] ?? [];

        if (empty($palabrasClaveProfesional) || empty($palabrasClavePaciente)) {
            return 0.1;
        }

        $coincidencias = 0;
        $palabrasPaciente = collect($palabrasClavePaciente)->pluck('palabra')->toArray();

        foreach ($palabrasClaveProfesional as $palabraProfesional) {
            if (in_array($palabraProfesional, $palabrasPaciente)) {
                $coincidencias++;
            }
        }

        return $coincidencias / count($palabrasClaveProfesional);
    }

    private function calcularPuntajeEspecialidad($sintomasPaciente)
    {
        // Si tiene especialidad parametrizable, usar esa lógica
        if ($this->especialidad) {
            return $this->especialidad->esCompatibleConPaciente($sintomasPaciente) ? 1.0 : 0.5;
        }

        // Lógica de respaldo para especialidad_principal
        $especialidadRecomendada = $sintomasPaciente['especialidad_recomendada'] ?? 'psicologo';
        return $this->especialidad_principal === $especialidadRecomendada ? 1.0 : 0.3;
    }

    private function calcularPuntajeExperiencia()
    {
        $puntaje = 0.5;

        if ($this->anios_experiencia >= 10) $puntaje += 0.3;
        elseif ($this->anios_experiencia >= 5) $puntaje += 0.2;
        elseif ($this->anios_experiencia >= 2) $puntaje += 0.1;

        if ($this->calificacion_promedio >= 4.5) $puntaje += 0.2;
        elseif ($this->calificacion_promedio >= 4.0) $puntaje += 0.1;

        if (!empty($this->certificaciones)) {
            $puntaje += min(0.2, count($this->certificaciones) * 0.05);
        }

        return min(1, $puntaje);
    }

    private function calcularPuntajeDisponibilidad()
    {
        if ($this->disponibilidad_inmediata) return 1.0;
        if ($this->tiempo_respuesta_promedio_horas <= 24) return 0.8;
        if ($this->tiempo_respuesta_promedio_horas <= 72) return 0.5;
        return 0.2;
    }

    private function calcularPuntajePreferencias($preferencias)
    {
        $puntaje = 0;

        if (isset($preferencias['modalidad']) && $preferencias['modalidad'] === $this->modalidad_trabajo) {
            $puntaje += 0.5;
        }

        if (isset($preferencias['idioma']) && in_array($preferencias['idioma'], $this->idiomas ?? [])) {
            $puntaje += 0.3;
        }

        if (isset($preferencias['enfoque']) && $preferencias['enfoque'] === $this->enfoque_terapeutico) {
            $puntaje += 0.2;
        }

        return min(1, $puntaje);
    }

    // NUEVOS MÉTODOS PARA ESPECIALIDADES PARAMETRIZABLES
    public function obtenerPalabrasClaveCompletas()
    {
        $palabrasPropias = $this->palabras_clave_especialidad ?? [];

        // Agregar palabras clave de la especialidad parametrizable
        if ($this->especialidad) {
            $palabrasEspecialidad = $this->especialidad->obtenerPalabrasClaveRecomendadas()->pluck('palabra')->toArray();
            $palabrasPropias = array_merge($palabrasPropias, $palabrasEspecialidad);
        }

        return array_unique($palabrasPropias);
    }

    public function actualizarDesdeSolicitud($datos)
    {
        $this->update([
            'especialidad_principal' => $datos['especialidad_principal'],
            'matricula' => $datos['matricula'] ?? null,
            'institucion' => $datos['institucion'] ?? null,
            'anios_experiencia' => $datos['anios_experiencia'] ?? 0,
            'bio' => $datos['bio'] ?? null
        ]);

        // Asignar especialidad parametrizada si existe
        if (isset($datos['especialidad_id'])) {
            $this->especialidad_id = $datos['especialidad_id'];
            $this->save();
        }
    }

    public function tieneCapacidad()
    {
        if ($this->capacidad_pacientes === null) {
            return true;
        }

        $pacientesActivos = $this->pacientes()->wherePivot('estado', 'activo')->count();
        return $pacientesActivos < $this->capacidad_pacientes;
    }

    public function actualizarPalabrasClave($palabrasClave)
    {
        $this->update([
            'palabras_clave_especialidad' => $palabrasClave,
            'sintomas_atiende' => $this->generarSintomasDesdePalabrasClave($palabrasClave)
        ]);
    }

    private function generarSintomasDesdePalabrasClave(array $palabrasClave)
    {
        $mapeoSintomas = [
            'ansiedad' => ['trastorno de ansiedad', 'crisis de ansiedad', 'ansiedad generalizada'],
            'depresión' => ['trastorno depresivo', 'episodio depresivo', 'depresión mayor'],
            'estrés' => ['estrés crónico', 'síntomas de estrés', 'agotamiento'],
            'trauma' => ['trauma psicológico', 'estrés postraumático', 'eventos traumáticos'],
            'familia' => ['problemas familiares', 'conflictos parentales', 'relaciones familiares'],
            'pareja' => ['problemas de pareja', 'conflictos de relación', 'crisis matrimonial'],
            'trabajo' => ['estrés laboral', 'problemas laborales', 'ambiente laboral'],
            'duelo' => ['proceso de duelo', 'pérdida', 'afrontamiento del duelo'],
            'autoestima' => ['baja autoestima', 'problemas de autoconcepto', 'falta de confianza'],
            'fobia' => ['trastorno fóbico', 'miedos específicos', 'evitación fóbica']
        ];

        $sintomas = [];
        foreach ($palabrasClave as $palabra) {
            if (isset($mapeoSintomas[$palabra])) {
                $sintomas = array_merge($sintomas, $mapeoSintomas[$palabra]);
            } else {
                // Si no hay mapeo específico, usar la palabra como síntoma general
                $sintomas[] = $palabra;
            }
        }

        return array_unique($sintomas);
    }

    // MÉTODOS PLACEHOLDER PARA DASHBOARD - CORREGIDOS
    public function compatibilidadPromedio()
    {
        $pacientes = $this->pacientes()->wherePivot('puntuacion_compatibilidad', '>', 0)->get();
        if ($pacientes->isEmpty()) return 0;
        return round($pacientes->avg('pivot.puntuacion_compatibilidad'), 2);
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
            ->where('fecha_cita', '>=', $start)
            ->where('estado', 'completada')
            ->count();
    }

    public function ingresosEsteMes()
    {
        $start = Carbon::now()->startOfMonth();
        return $this->citas()
            ->where('fecha_cita', '>=', $start)
            ->where('estado', 'completada')
            ->sum('monto');
    }

    // MÉTODOS DE CONFIGURACIÓN DE DISPONIBILIDAD
    public function activarDisponibilidad()
    {
        $this->update(['disponibilidad_inmediata' => true]);
    }

    public function desactivarDisponibilidad()
    {
        $this->update(['disponibilidad_inmediata' => false]);
    }

    public function toggleDisponibilidad()
    {
        $this->update(['disponibilidad_inmediata' => !$this->disponibilidad_inmediata]);
        return $this->disponibilidad_inmediata;
    }

    // MÉTODOS DE VALIDACIÓN
    public function puedeRecibirPacientes()
    {
        return $this->estaAprobado() &&
            $this->disponibilidad_inmediata &&
            $this->tieneCapacidad();
    }

    // BOOT PARA UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_profesional)) {
                $model->id_profesional = (string) \Illuminate\Support\Str::uuid();
            }

            // Establecer valores por defecto
            if (empty($model->estado_verificacion)) {
                $model->estado_verificacion = self::ESTADO_PENDIENTE;
            }

            if (empty($model->disponibilidad_inmediata)) {
                $model->disponibilidad_inmediata = false;
            }
        });

        static::updating(function ($model) {
            // Cuando se aprueba un profesional, activar disponibilidad por defecto
            if (
                $model->isDirty('estado_verificacion') &&
                $model->estado_verificacion === self::ESTADO_APROBADO
            ) {
                $model->disponibilidad_inmediata = true;
            }
        });
    }
}
