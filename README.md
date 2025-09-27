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


5. Visit `http://your-app.test/migration-guru`


