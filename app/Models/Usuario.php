<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'contrasenia', // Asegúrate que este campo existe
        'tipo_usuario',
        'telefono',
    ];

    protected $hidden = [
        'contrasenia', // Ocultar contraseña
        'remember_token',
    ];

    // ✅ IMPORTANTE: Especificar el campo de contraseña
    public function getAuthPassword()
    {
        return $this->contrasenia;
    }

    // ✅ Para la autenticación de Laravel
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relación con Paciente
    public function paciente()
    {
        return $this->hasOne(Paciente::class, 'usuario_id');
    }

    // Relación con Profesional
    public function profesional()
    {
        return $this->hasOne(Profesional::class, 'usuario_id');
    }

    // Métodos para verificar tipo de usuario
    public function esPaciente()
    {
        return $this->tipo_usuario === 'paciente';
    }

    public function esProfesional()
    {
        return in_array($this->tipo_usuario, ['psicologo', 'psiquiatra', 'nutricionista']);
    }
    public function esAdministrador()
    {
        return $this->tipo_usuario === 'administrador';
    }

    // Método para nombre completo
    public function obtenerNombreCompleto()
    {
        return $this->nombre . ' ' . $this->apellido;
    }
}