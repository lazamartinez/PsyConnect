<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';
    protected $primaryKey = 'id_especialidad';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'rol_permitido', // 'psicologo', 'psiquiatra', 'nutricionista', 'general'
        'activo',
        'configuracion',
        'color',
        'icono'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'configuracion' => 'array'
    ];

    // Relaciones
    public function profesionales()
    {
        return $this->hasMany(Profesional::class, 'especialidad_id');
    }

    public function palabrasClave()
    {
        return $this->hasMany(PalabraClave::class, 'especialidad_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorRol($query, $rol)
    {
        return $query->where('rol_permitido', $rol)
                    ->orWhere('rol_permitido', 'general');
    }

    public function scopeParaPsicologo($query)
    {
        return $this->scopePorRol($query, 'psicologo');
    }

    public function scopeParaPsiquiatra($query)
    {
        return $this->scopePorRol($query, 'psiquiatra');
    }

    public function scopeParaNutricionista($query)
    {
        return $this->scopePorRol($query, 'nutricionista');
    }

    // Métodos estáticos para roles permitidos
    public static function obtenerRolesPermitidos()
    {
        return [
            'psicologo' => 'Psicólogo',
            'psiquiatra' => 'Psiquiatra', 
            'nutricionista' => 'Nutricionista',
            'general' => 'General/Múltiples Roles'
        ];
    }

    // Obtener especialidades base por rol
    public static function obtenerEspecialidadesBase()
    {
        return [
            // ESPECIALIDADES PARA PSICÓLOGOS
            [
                'nombre' => 'Psicología Clínica',
                'codigo' => 'psicologia_clinica',
                'descripcion' => 'Evaluación, diagnóstico y tratamiento de trastornos psicológicos',
                'rol_permitido' => 'psicologo',
                'color' => '#3B82F6',
                'icono' => 'fas fa-brain',
                'configuracion' => [
                    'puede_recetar_medicamentos' => false,
                    'requiere_derivacion_psiquiatra' => ['psicosis', 'ideacion_suicida'],
                    'nivel_urgencia_maximo' => 'alto'
                ]
            ],
            [
                'nombre' => 'Psicología Infantil',
                'codigo' => 'psicologia_infantil', 
                'descripcion' => 'Atención psicológica especializada en niños y adolescentes',
                'rol_permitido' => 'psicologo',
                'color' => '#8B5CF6',
                'icono' => 'fas fa-child',
                'configuracion' => [
                    'puede_recetar_medicamentos' => false,
                    'rango_edad_minimo' => 0,
                    'rango_edad_maximo' => 18
                ]
            ],
            [
                'nombre' => 'Terapia Familiar',
                'codigo' => 'terapia_familiar',
                'descripcion' => 'Terapia centrada en dinámicas y relaciones familiares',
                'rol_permitido' => 'psicologo',
                'color' => '#10B981',
                'icono' => 'fas fa-users',
                'configuracion' => [
                    'puede_recetar_medicamentos' => false,
                    'enfoque_grupal' => true
                ]
            ],

            // ESPECIALIDADES PARA PSIQUIATRAS
            [
                'nombre' => 'Psiquiatría General',
                'codigo' => 'psiquiatria_general',
                'descripcion' => 'Diagnóstico y tratamiento médico de trastornos mentales',
                'rol_permitido' => 'psiquiatra',
                'color' => '#EF4444',
                'icono' => 'fas fa-stethoscope',
                'configuracion' => [
                    'puede_recetar_medicamentos' => true,
                    'nivel_urgencia_maximo' => 'critico'
                ]
            ],
            [
                'nombre' => 'Psiquiatría Infantil',
                'codigo' => 'psiquiatria_infantil',
                'descripcion' => 'Diagnóstico y tratamiento de trastornos mentales en niños y adolescentes',
                'rol_permitido' => 'psiquiatra', 
                'color' => '#F59E0B',
                'icono' => 'fas fa-baby',
                'configuracion' => [
                    'puede_recetar_medicamentos' => true,
                    'rango_edad_minimo' => 0,
                    'rango_edad_maximo' => 18
                ]
            ],

            // ESPECIALIDADES PARA NUTRICIONISTAS
            [
                'nombre' => 'Nutrición Clínica',
                'codigo' => 'nutricion_clinica',
                'descripcion' => 'Evaluación y tratamiento de problemas nutricionales',
                'rol_permitido' => 'nutricionista',
                'color' => '#06B6D4',
                'icono' => 'fas fa-apple-alt',
                'configuracion' => [
                    'puede_recetar_medicamentos' => false,
                    'derivar_a_psicologo' => ['anorexia', 'bulimia']
                ]
            ],
            [
                'nombre' => 'Nutrición Deportiva',
                'codigo' => 'nutricion_deportiva',
                'descripcion' => 'Nutrición especializada para deportistas y actividad física',
                'rol_permitido' => 'nutricionista',
                'color' => '#84CC16',
                'icono' => 'fas fa-running',
                'configuracion' => [
                    'puede_recetar_medicamentos' => false,
                    'enfoque_rendimiento' => true
                ]
            ]
        ];
    }

    public static function inicializarEspecialidades()
    {
        foreach (self::obtenerEspecialidadesBase() as $especialidadData) {
            self::firstOrCreate(
                ['codigo' => $especialidadData['codigo']],
                $especialidadData
            );
        }
    }

    // Métodos de utilidad
    public function obtenerPalabrasClaveRecomendadas()
    {
        return $this->palabrasClave()->where('estado', true)->get();
    }

    public function esCompatibleConPaciente($analisisSintomas)
    {
        $config = $this->configuracion ?? [];

        // Verificar nivel de urgencia máximo
        if (isset($config['nivel_urgencia_maximo'])) {
            $niveles = ['bajo' => 1, 'medio' => 2, 'alto' => 3, 'critico' => 4];
            $urgenciaPaciente = $analisisSintomas['nivel_urgencia'] ?? 'bajo';
            if ($niveles[$urgenciaPaciente] > $niveles[$config['nivel_urgencia_maximo']]) {
                return false;
            }
        }

        return true;
    }

    public function getColorAttribute($value)
    {
        return $value ?? '#6B7280'; // Color por defecto
    }

    public function getIconoAttribute($value)
    {
        return $value ?? 'fas fa-user-md'; // Icono por defecto
    }
}