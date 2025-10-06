<?php

namespace App\Policies;

use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    /**
     * Determina si el usuario tiene permisos de administrador.
     */
    public function admin(Usuario $usuario)
    {
        return $usuario->tipo_usuario === 'administrador';
    }
}
