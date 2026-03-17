<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes will be migrated from Slim in Phase 2.
| See app/routes.php for the current Slim route definitions.
|
*/

Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
});
