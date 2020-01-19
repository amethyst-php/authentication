<?php

namespace Amethyst\Providers;

use Amethyst\Core\Support\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthenticationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/amethyst.authentication.php' => config_path('amethyst.authentication.php'),
        ], 'config');

        config(['auth.guards.api.driver' => 'passport']);
        config(['auth.guards.api.provider' => 'users']);
        config(['auth.providers.users.driver' => 'eloquent']);
        config(['auth.providers.users.model' => Config::get('amethyst.authentication.entity')]);

        $this->loadRoutes();

        Passport::routes();
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }

    /**
     * Register any application services.so.
     */
    public function register()
    {
        $this->app->register(\Laravel\Passport\PassportServiceProvider::class);
        $this->app->register(\Amethyst\Providers\ApiServiceProvider::class);
        $this->app->register(\Amethyst\Providers\UserServiceProvider::class);
        $this->mergeConfigFrom(__DIR__.'/../../config/amethyst.authentication.php', 'amethyst.authentication');
    }

    /**
     * Load routes.
     */
    public function loadRoutes()
    {
        $config = Config::get('amethyst.authentication.http.app.authentication');

        if (Arr::get($config, 'enabled')) {
            Router::group('app', Arr::get($config, 'router'), function ($router) use ($config) {
                $controller = Arr::get($config, 'controller');

                $router->post('/', ['as' => 'basic', 'uses' => $controller.'@signIn']);
                $router->post('/{name}', ['as' => 'provider', 'uses' => $controller.'@signInWithProvider']);
                //$router->post('/{name}/exchange_token', ['uses' => $controller.'@exchangeToken']);
            });
        }
    }
}
