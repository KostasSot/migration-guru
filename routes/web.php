<?php

use Illuminate\Support\Facades\Route;
use Nikelioum\MigrationGuru\Http\Controllers\MigrationGuruController;

$prefix = config('migration-guru.route_prefix', 'migration-guru');
$middleware = config('migration-guru.middleware', ['web']);

Route::group([
    'middleware' => $middleware,
    'prefix' => $prefix,
], function () {
    Route::get('/', [MigrationGuruController::class, 'index'])->name('migration-guru.index');
    Route::get('/create', [MigrationGuruController::class, 'create'])->name('migration-guru.create');
    Route::post('/create', [MigrationGuruController::class, 'store'])->name('migration-guru.store');
    Route::post('/run', [MigrationGuruController::class, 'run'])->name('migration-guru.run');
    Route::post('/migrate-all', [MigrationGuruController::class, 'migrateAll'])->name('migration-guru.migrateAll');
    Route::post('/fresh', [MigrationGuruController::class, 'fresh'])->name('migration-guru.fresh');
    Route::post('/delete', [MigrationGuruController::class, 'delete'])->name('migration-guru.delete');
    Route::post('/bulk-run', [MigrationGuruController::class, 'bulkRun'])->name('migration-guru.bulkRun');
    Route::post('/bulk-delete', [MigrationGuruController::class, 'bulkDelete'])->name('migration-guru.bulkDelete');
    Route::get('/edit/{file}', [MigrationGuruController::class, 'edit'])->name('migration-guru.edit');
    Route::post('/update/{file}', [MigrationGuruController::class, 'update'])->name('migration-guru.update');
    Route::post('/lint', [MigrationGuruController::class, 'lint'])->name('migration-guru.lint');
    Route::get('/history', [MigrationGuruController::class, 'history'])->name('migration-guru.history');

    //Seeder Routes
    Route::post('/fresh-seed', [MigrationGuruController::class, 'freshSeed'])->name('migration-guru.freshSeed');
    Route::get('/seeders/create', [MigrationGuruController::class, 'createSeeder'])->name('migration-guru.seeders.create');
    Route::post('/seeders/create', [MigrationGuruController::class, 'storeSeeder'])->name('migration-guru.seeders.store');

    //Manually seeding routes
    Route::get('/records/create', [MigrationGuruController::class, 'createRecord'])->name('migration-guru.records.create');
    Route::post('/records/create', [MigrationGuruController::class, 'storeRecord'])->name('migration-guru.records.store');

     // Model Creation Routes
    Route::get('/models/create', [MigrationGuruController::class, 'createModel'])->name('migration-guru.models.create');
    Route::post('/models/create', [MigrationGuruController::class, 'storeModel'])->name('migration-guru.models.store');

    // NEW All-in-One Resource Generator Routes
    Route::get('/resource/create', [MigrationGuruController::class, 'createResource'])->name('migration-guru.resource.create');
    Route::post('/resource/create', [MigrationGuruController::class, 'storeResource'])->name('migration-guru.resource.store');

    // Database Viewer Routes
    Route::get('/tables', [MigrationGuruController::class, 'showTables'])->name('migration-guru.tables.index');
    Route::get('/tables/{table}', [MigrationGuruController::class, 'viewTable'])->name('migration-guru.tables.view');
});

