<?php

return [

    'entity' => Railken\LaraOre\Concerns\Auth\User::class,

    'http' => [

        /*
        |--------------------------------------------------------------------------
        | Class name controller
        |--------------------------------------------------------------------------
        |
        | Here you may define the controller that will handle all the requests
        |
        */
        'controller' => Railken\LaraOre\Http\Controllers\AuthController::class,

        /*
        |--------------------------------------------------------------------------
        | Router Options
        |--------------------------------------------------------------------------
        |
        | Here you may define all the options that will be used by the route group
        |
        */
        'router' => [
            'prefix'      => '/auth',
        ],
    ],
];
