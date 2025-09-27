# Migration Guru (nikelioum/migration-guru)

A small Laravel 12 package that provides a web UI to:
- List migration files (`database/migrations`)
- Create a new migration file (simple scaffold)
- Run an individual migration file
- Run all migrations
- Run `migrate:fresh`

=== Quick notes ===
- **This package executes migration commands programmatically**. For safety, do NOT expose the routes publicly in production. Protect with `auth` middleware or restrict by IP.
- The package registers routes under `/migration-guru`.

## Install locally (recommended for testing)

1. Extract the zip into your Laravel application's `packages/nikelioum/migration-guru` folder:
   ```
   mkdir -p packages/nikelioum
   unzip /path/to/nikelioum-migration-guru.zip -d packages/nikelioum/migration-guru
   ```

2. Add a `path` repository to your application's `composer.json`:
   ```json
   "repositories": [
       {
           "type": "path",
           "url": "packages/nikelioum/migration-guru",
           "options": { "symlink": true }
       }
   ]
   ```

3. Require the package:
   ```
   composer require nikelioum/migration-guru*
   ```

   Composer should symlink or copy the package into `vendor/nikelioum/migration-guru` based on the `path` repository settings.

4. If your Laravel app does not auto-discover package providers, register the provider in `config/app.php`:
   ```php
   'providers' => [
       // ...
       Nikelioum\MigrationGuru\MigrationGuruServiceProvider::class,
   ];
   ```

5. (Optional) Publish views:
   ```
   php artisan vendor:publish --provider="Nikelioum\MigrationGuru\MigrationGuruServiceProvider" --tag="migration-guru-views"
   ```

6. Visit `http://your-app.test/migration-guru`

## Quick usage & tips
- Use the **Create migration** form to scaffold a migration. Check the "Generate create table scaffold" and provide a table name to get a basic `id()` + `timestamps()` schema.
- Use **Run** next to a migration to run that single file.
- Use **Run all migrations** or **Migrate Fresh** for broader operations.
- **Important:** If your app has no `migrations` table yet, running a single migration via the UI may fail until the migrations table exists. Use **Run all migrations** first or run `php artisan migrate` from CLI.

## Security
Protect the routes by editing `routes/web.php` in the package (or publish and edit) and adding middleware:
```php
Route::group(['middleware' => ['web','auth'], 'prefix' => 'migration-guru'], function () {
    // ...
});
```

---

Enjoy. If you want, I can wire this package to use your app's layout, add role-based protection, or enhance the migration editor with a code textarea.
