<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StoController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SalesmanController;

use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum', 'role:api-users'])->group(function() {
    Route::get('logout', [AuthController::class, 'logout']);

    Route::group(['middleware' => 'permission:location access'], function() {
        Route::get('location', [LocationController::class, 'index']);
        Route::post('location/create', [LocationController::class, 'create'])->middleware('permission:location create');
        Route::get('location/{id}/get', [LocationController::class, 'show'])->middleware('permission:location access');
        Route::post('location/{id}/update', [LocationController::class, 'update'])->middleware('permission:location update');
    });

    Route::group(['middleware' => 'permission:salesman access'], function() {
        Route::get('salesman', [SalesmanController::class, 'index']);
        Route::post('salesman/create', [SalesmanController::class, 'create'])->middleware('permission:salesman create');
    });
});

Route::group(['middleware' => 'api.token'], function() {
    Route::get('sales', [SaleController::class, 'index']);

    // STO
    Route::post('sto', [StoController::class, 'index']);
    // STO AREAS
    Route::post('sto-areas', [StoController::class, 'areas']);
});
