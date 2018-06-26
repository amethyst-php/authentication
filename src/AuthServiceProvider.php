<?php

namespace Railken\LaraOre;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\RouteRegistrar;
use Railken\LaraOre\Api\Support\Router;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/ore.auth.php' => config_path('ore.auth.php'),
        ], 'config');

        config(['auth.guards.api.driver' => 'passport']);
        config(['auth.guards.api.provider' => 'users']);
        config(['auth.providers.users.driver' => 'eloquent']);
        config(['auth.providers.users.model' => Config::get('ore.auth.entity')]);

        $this->loadRoutes();

        $callback = function ($router) {
            $router->all();
        };

        $options = array_merge([
            'namespace' => '\Laravel\Passport\Http\Controllers',
            'prefix'    => 'api/v1/oauth',
        ], []);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\Laravel\Passport\PassportServiceProvider::class);
        $this->app->register(\Railken\LaraOre\ApiServiceProvider::class);
        $this->app->register(\Railken\LaraOre\UserServiceProvider::class);
        $this->mergeConfigFrom(__DIR__.'/../config/ore.auth.php', 'ore.auth');
    }

    /**
     * Load routes.
     */
    public function loadRoutes()
    {
        Router::group(Config::get('ore.auth.http.router'), function ($router) {
            $controller = Config::get('ore.auth.http.controller');

            $router->post('/sign-in', ['uses' => $controller.'@signIn']);
            $router->post('/provider/{name}/access_token', ['uses' => $controller.'@accessToken']);
            $router->post('/provider/{name}/exchange_token', ['uses' => $controller.'@exchangeToken']);
        });
    }
}
