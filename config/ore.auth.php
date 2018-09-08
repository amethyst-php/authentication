<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Entity
    |--------------------------------------------------------------------------
    |
    | Here you may configure the entity user used for authentication
    |
    */
    'entity' => Railken\LaraOre\Concerns\Auth\User::class,

    /*
    |--------------------------------------------------------------------------
    | Http configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the routes
    |
    */
    'http' => [
        'app' => [
            'enabled'    => true,
            'controller' => Railken\LaraOre\Http\Controllers\App\AuthController::class,
            'router'     => [
                'prefix'      => '/auth',
            ],
        ],
    ],
];
