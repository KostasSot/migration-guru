<?php

namespace Nikelioum\MigrationGuru;

use Illuminate\Support\ServiceProvider;

class MigrationGuruServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load routes
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'migration-guru');

        // Publish config and views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/migration-guru'),
            __DIR__.'/../config/migration-guru.php' => config_path('migration-guru.php'),
        ], 'migration-guru');
    }

    public function register()
    {
        // Merge config to provide defaults
        $this->mergeConfigFrom(
            __DIR__.'/../config/migration-guru.php',
            'migration-guru'
        );
    }
}
