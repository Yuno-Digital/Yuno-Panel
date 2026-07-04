<?php

namespace App\Providers;

use App\Support\Installer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Before installation the database (sessions/cache tables) may not exist
        // yet, so fall back to file/array drivers so the installer can run.
        if (! Installer::isInstalled()) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'array',
                'queue.default' => 'sync',
            ]);
        }
    }
}
