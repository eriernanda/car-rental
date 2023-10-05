<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Route::prefix('login')->group(function () {
    Route::get('/', [AuthController::class, 'loginView']);
    Route::post('/', [AuthController::class, 'loginSubmit']);
});
Route::get('logout', [AuthController::class, 'logout']);

Route::middleware(['authenticate'])->group(function() {
    Route::prefix('rent')->group(function () {

        Route::middleware(['admin'])->group(function() {
            Route::get('/all', [RentController::class, 'rentView']);
            Route::post('/data', [RentController::class, 'rentAjaxData']);

            Route::get('/insert-rent', [RentController::class, 'addRentView']);
            Route::post('/add-rent', [RentController::class, 'addRentSubmit']);
            Route::post('/return', [RentController::class, 'returnRentSubmit']);

            Route::get('/export-excel', [RentController::class, 'exportExcel']);

            Route::get('/chart', [RentController::class, 'rentChart']);
        });

        Route::get('/approval', [RentController::class, 'approveView']);
        Route::post('/approval-data', [RentController::class, 'approveAjaxData']);
        Route::post('/approve', [RentController::class, 'approveSubmit']);

    });
});
