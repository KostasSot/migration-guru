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

    // NEW: history page
    Route::get('/history', [MigrationGuruController::class, 'history'])->name('migration-guru.history');
});

