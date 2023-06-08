<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventorySalesController;

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
Route::get('/', [HomeController::class, 'index'])->name('home');

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile');

    // ROLES
    Route::group(['middleware' => 'permission:role access'], function() {
        Route::get('role', [RoleController::class, 'index'])->name('role.index');
        Route::get('role/add', [RoleController::class, 'create'])->name('role.create')->middleware('permission:role create');
        Route::post('role', [RoleController::class, 'store'])->name('role.store')->middleware('permission:role create');

        Route::get('role/{id}/edit', [RoleController::class, 'edit'])->name('role.edit')->middleware('permission:role edit');
        Route::post('role/{id}', [RoleController::class, 'update'])->name('role.update')->middleware('permission:role edit');
    });

    // USERS
    Route::group(['middleware' => 'permission:user access'], function() {
        Route::get('user', [UserController::class, 'index'])->name('user.index');
        Route::get('user/create', [UserController::class, 'create'])->name('user.create')->middleware('permission:user create');
        Route::post('user', [UserController::class, 'store'])->name('user.store')->middleware('permission:user create');

        Route::get('user/{id}', [UserController::class, 'show'])->name('user.show');

        Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware('permission:user edit');
        Route::post('user/{id}', [UserController::class, 'update'])->name('user.update')->middleware('permission:user edit');
    });

    // INVENTORY SALES
    Route::group(['middleware' => 'permission:inventory sales access'], function() {
        Route::get('inventory-sales/{id}/branches', [InventorySalesController::class, 'branches'])->name('inventory-sales.branches');
        Route::get('inventory-sales/{id}/index', [InventorySalesController::class, 'index'])->name('inventory-sales.index');
    });
});
