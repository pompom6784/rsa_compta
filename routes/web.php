<?php

declare(strict_types=1);

use App\Http\Controllers\YearController;
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

Route::get('/select_year', [YearController::class, 'selectYear'])->name('selectYear');
Route::post('/select_year', [YearController::class, 'pickYear'])->name('pickYear');

