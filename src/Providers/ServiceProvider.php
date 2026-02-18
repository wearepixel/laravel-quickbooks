<?php

namespace Wearepixel\QuickBooks\Providers;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Wearepixel\QuickBooks\Http\Controllers\Controller;
use Wearepixel\QuickBooks\Http\Middleware\Filter;

/**
 * Class ServiceProvider
 */
class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->registerMiddleware();

        $this->registerPublishes();

        $this->registerRoutes();

        $this->registerViews();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/quickbooks.php', 'quickbooks');
    }

    /**
     * Register the middleware
     *
     * If a route needs to have the QuickBooks client, then make sure that the user has linked their account.
     */
    public function registerMiddleware(): void
    {
        $this->app->router->aliasMiddleware('quickbooks', Filter::class);
    }

    /**
     * There are several resources that get published
     *
     * Only worry about telling the application about them if running in the console.
     */
    protected function registerPublishes(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

            $this->publishes(
                [
                    __DIR__.'/../../config/quickbooks.php' => config_path('quickbooks.php'),
                ],
                'quickbooks-config',
            );

            $this->publishes(
                [
                    __DIR__.'/../../database/migrations' => database_path('migrations'),
                ],
                'quickbooks-migrations',
            );

            $this->publishes(
                [
                    __DIR__.'/../../resources/views' => base_path(
                        'resources/views/vendor/quickbooks',
                    ),
                ],
                'quickbooks-views',
            );
        }
    }

    /**
     * Register the routes needed for the registration flow
     */
    protected function registerRoutes(): void
    {
        $config = $this->app->config->get('quickbooks.route');

        $this->app->router
            ->prefix($config['prefix'])
            ->as('quickbooks.')
            ->middleware($config['middleware']['default'])
            ->group(function () use ($config) {
                $this->app->router
                    ->get($config['paths']['connect'], [Controller::class, 'connect'])
                    ->middleware($config['middleware']['authenticated'])
                    ->name('connect');

                $this->app->router
                    ->delete($config['paths']['disconnect'], [Controller::class, 'disconnect'])
                    ->middleware($config['middleware']['authenticated'])
                    ->name('disconnect');

                $this->app->router
                    ->get($config['paths']['token'], [Controller::class, 'token'])
                    ->name('token');
            });
    }

    /**
     * Register the views
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'quickbooks');
    }
}
