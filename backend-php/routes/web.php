<?php

use Illuminate\Support\Facades\Route;

/**
 * Web routes file. API routes should live in routes/api.php.
 *
 * Keeping this file minimal avoids registering API endpoints
 * under the `web` middleware (which applies CSRF protection).
 */

// Example web route (adjust or remove as needed):
Route::get('/', function () {
    return response('OK');
});
