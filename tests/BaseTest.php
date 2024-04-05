<?php

namespace Amethyst\Tests;

abstract class BaseTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->artisan('passport:install');
        $this->artisan('amethyst:user:install');

        config(['amethyst.user.entity' => config('amethyst.authentication.entity')]);
        app('amethyst')->boot();
        app('eloquent.mapper')->boot();

    }

    protected function getPackageProviders($app)
    {
        return [
            \Amethyst\Providers\AuthenticationServiceProvider::class,
        ];
    }
}
