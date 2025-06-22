<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use App\Services\Socialite\GoogleTasksProvider;
use App\Services\Socialite\MicrosoftProvider;

class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Socialite::extend('google-tasks', function ($app) {
            $config = $app['config']['services.google-tasks'];
            
            return new GoogleTasksProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );
        });

        Socialite::extend('google-email', function ($app) {
            $config = $app['config']['services.google-email'];
            
            return new \Laravel\Socialite\Two\GoogleProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );
        });

        Socialite::extend('microsoft', function ($app) {
            $config = $app['config']['services.microsoft'];
            
            return new MicrosoftProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );
        });
    }
}
