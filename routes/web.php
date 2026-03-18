<?php

declare(strict_types=1);

use App\Http\Controllers\BookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\YearController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'home'])->name('home');

Route::get('/select_year', [YearController::class, 'selectYear'])->name('selectYear');
Route::post('/select_year', [YearController::class, 'pickYear'])->name('pickYear');

Route::prefix('livre')->group(function (): void {
    Route::get('', [BookController::class, 'index'])->name('book');
    Route::post('/lignes', [BookController::class, 'lines'])->name('lines');
    Route::match(['GET', 'POST'], '/lignes/{id}', [BookController::class, 'lineEdit'])->name('line');
    Route::get('/excel', [BookController::class, 'excel'])->name('excel');
    Route::get('/a_ventiler', [BookController::class, 'breakdown'])->name('toBreakdown');
});

Route::prefix('imports')->group(function (): void {
    Route::get('', [ImportController::class, 'home'])->name('imports');
    Route::post('/paypal', [ImportController::class, 'paypal'])->name('paypal');
    Route::post('/sg', [ImportController::class, 'sg'])->name('sg');
    Route::post('/sogecom', [ImportController::class, 'sogecom'])->name('sogecom');
    Route::post('/remises', [ImportController::class, 'checkDelivery'])->name('checkDelivery');
});
