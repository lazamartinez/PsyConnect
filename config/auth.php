<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'usuarios', // Cambiar de 'users' a 'usuarios'
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'usuarios', // Cambiar de 'users' a 'usuarios'
        ],
    ],

    'providers' => [
        'usuarios' => [ // Cambiar de 'users' a 'usuarios'
            'driver' => 'eloquent',
            'model' => App\Models\Usuario::class, // Cambiar de User::class a Usuario::class
        ],
    ],

    'passwords' => [
        'usuarios' => [ // Cambiar de 'users' a 'usuarios'
            'provider' => 'usuarios', // Cambiar de 'users' a 'usuarios'
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];