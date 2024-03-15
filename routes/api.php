<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StoController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SalesmanController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\AccountBranchController;

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

    Route::get('branches', [AccountBranchController::class, 'index']);
    Route::get('branch/generateKey', [AccountBranchController::class, 'generateKey']);

    Route::group(['middleware' => 'permission:location access'], function() {
        Route::get('location', [LocationController::class, 'index']);
        Route::post('location/create', [LocationController::class, 'create'])->middleware('permission:location create');
        Route::get('location/{id}/get', [LocationController::class, 'show'])->middleware('permission:location access');
        Route::post('location/{id}/update', [LocationController::class, 'update'])->middleware('permission:location edit');
    });

    Route::group(['middleware' => 'permission:salesman access'], function() {
        Route::get('salesman', [SalesmanController::class, 'index']);
        Route::post('salesman/create', [SalesmanController::class, 'create'])->middleware('permission:salesman create');
        Route::get('salesman/{id}/get', [SalesmanController::class, 'show']);
        Route::post('salesman/{id}/update', [SalesmanController::class, 'update'])->middleware('permission:salesman edit');
    });

    Route::group(['middleware' => 'permission:area access'], function() {
        Route::get('area', [AreaController::class, 'index']);
        Route::post('area/create', [AreaController::class, 'create'])->middleware('permission:area create');
        Route::get('area/{id}/get', [AreaController::class, 'show']);
        Route::post('area/{id}/update', [AreaController::class, 'update'])->middleware('permission:area edit');
    });

    Route::group(['middleware' => 'permission:channel access'], function() {
        Route::get('channel', [ChannelController::class, 'index']);
        Route::post('channel/create', [ChannelController::class, 'create'])->middleware('permission:channel create');
        Route::get('channel/{id}/get', [ChannelController::class, 'show']);
        Route::post('channel/{id}/update', [ChannelController::class, 'update'])->middleware('permission:channel edit');
    });

    Route::group(['middleware' => 'permission:customer access'], function() {
        Route::get('customer', [CustomerController::class, 'index']);
        Route::post('customer/create', [CustomerController::class, 'create'])->middleware('permission:customer create');
        Route::get('customer/{id}/get', [CustomerController::class, 'show']);
        Route::post('customer/{id}/update', [CustomerController::class, 'update'])->middleware('permission:customer edit');
    });

    Route::group(['middleware' => 'permission:sales access'], function() {
        Route::get('sales', [SalesController::class, 'index']);
        Route::post('sales/create', [SalesController::class, 'create'])->middleware('permission:sales create');
        Route::get('sales/{id}/get', [SalesController::class, 'show']);
        Route::post('sales/{id}/update', [SalesController::class, 'update'])->middleware('permission:sales update');
    });

    Route::group(['middleware' => 'permission:inventory access'], function() {
        Route::get('inventory', [InventoryController::class, 'index']);
        Route::post('inventory/create', [InventoryController::class, 'create'])->middleware('permission:inventory create');
        Route::get('inventory/{id}/get', [InventoryController::class, 'show']);
        Route::post('inventory/{id}/update', [InventoryController::class, 'update'])->middleware('permission:inventory edit');
    });

});

// Route::group(['middleware' => 'api.token'], function() {
//     Route::get('sales', [SaleController::class, 'index']);

//     // STO
//     Route::post('sto', [StoController::class, 'index']);
//     // STO AREAS
//     Route::post('sto-areas', [StoController::class, 'areas']);
// });
