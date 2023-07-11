<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StoController;

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

Route::group(['middleware' => 'api.token'], function() {
    Route::get('sales', [SaleController::class, 'index']);

    // STO
    Route::get('sto', [StoController::class, 'index']);
});
