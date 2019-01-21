<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Entity
    |--------------------------------------------------------------------------
    |
    | Here you may configure the entity user used for authentication.
    |
    */
    'entity' => Railken\Amethyst\Concerns\Auth\User::class,

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
            'authentication' => [
                'enabled'    => true,
                'controller' => Railken\Amethyst\Http\Controllers\App\AuthController::class,
                'router'     => [
                    'as'     => 'auth.',
                    'prefix' => '/auth',
                ],
            ],
        ],
    ],
];
