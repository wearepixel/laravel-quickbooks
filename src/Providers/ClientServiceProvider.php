<?php

namespace Wearepixel\QuickBooks\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Wearepixel\QuickBooks\Client;

/**
 * Class ClientServiceProvider
 */
class ClientServiceProvider extends LaravelServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [Client::class];
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(Client::class, function (Application $app) {
            $token =
                $app->auth->user()->quickBooksToken ?:
                $app->auth
                    ->user()
                    ->quickBooksToken()
                    ->make();

            return new Client($app->config->get('quickbooks'), $token);
        });

        $this->app->alias(Client::class, 'QuickBooks');
    }
}
