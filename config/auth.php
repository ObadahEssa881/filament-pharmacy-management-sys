<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],

        'pharmacy' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'supplier' => [
            'driver' => 'session',
            'provider' => 'suppliers',
        ],
        // 'filament' => [
        //     'driver' => 'session',
        //     'provider' => 'users', // or 'suppliers' if preferred
        // ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'suppliers' => [
            'driver' => 'eloquent',
            'model' => App\Models\Supplier::class,
        ],

        'nestjs' => [
            'driver' => 'nestjs',  
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
