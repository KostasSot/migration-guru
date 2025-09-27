<?php

namespace Nikelioum\MigrationGuru;

use Illuminate\Support\ServiceProvider;

class MigrationGuruServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // load routes
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'migration-guru');

        // publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/migration-guru'),
        ], 'migration-guru-views');
    }

    public function register()
    {
        //
    }
}
