<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This value is the prefix used for all Migration Guru routes.
    | You can change it to fit your application.
    |
    */

    'route_prefix' => env('MIGRATION_GURU_PREFIX', 'migration-guru'),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Define which middleware should be applied to Migration Guru routes.
    | For example, you can use 'auth' to require authentication.
    |
    */

    'middleware' => ['web'], // or ['web', 'auth']

];
