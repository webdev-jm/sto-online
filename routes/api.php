<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StoController;

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
Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::get('logout', [AuthController::class, 'logout']);
});

Route::group(['middleware' => 'api.token'], function() {
    Route::get('sales', [SaleController::class, 'index']);

    // STO
    Route::post('sto', [StoController::class, 'index']);
    // STO AREAS
    Route::post('sto-areas', [StoController::class, 'areas']);
});
