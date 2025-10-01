<?php

namespace Nikelioum\MigrationGuru;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

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

        // Auto-create logs table if missing (no CLI required)
        $this->ensureMigrationGuruLogsTable();

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

    protected function ensureMigrationGuruLogsTable(): void
    {
        try {
            DB::connection()->getPdo(); // skip if DB not ready
            if (!Schema::hasTable('migration_guru_logs')) {
                Schema::create('migration_guru_logs', function (Blueprint $table) {
                    $table->id();
                    $table->string('action');                 // create, update, run, rollback, delete, migrate_all, fresh, bulk_run, bulk_delete, lint
                    $table->string('file')->nullable();
                    $table->string('migration_name')->nullable();
                    $table->string('status')->default('ok');  // ok|error
                    $table->text('message')->nullable();
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->string('ip')->nullable();
                    $table->timestamp('executed_at')->useCurrent();
                    $table->timestamps();
                    $table->index(['action', 'status', 'executed_at']);
                });
            }
        } catch (\Throwable $e) {
            // don't break UI if DB isn’t ready
        }
    }
}
