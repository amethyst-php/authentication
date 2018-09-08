<?php

namespace Railken\LaraOre\Auth\Tests;

abstract class BaseTest extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $dotenv = new \Dotenv\Dotenv(__DIR__.'/..', '.env');
        $dotenv->load();

        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->artisan('passport:install');
        // $this->artisan('vendor:publish', ['--provider' => 'Railken\LaraOre\AuthServiceProvider', '--force' => true]);
        $this->artisan('lara-ore:user:install');
        $this->artisan('migrate');

        config(['ore.user.entity' => config('ore.auth.entity')]);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Railken\LaraOre\AuthenticationServiceProvider::class,
        ];
    }
}
