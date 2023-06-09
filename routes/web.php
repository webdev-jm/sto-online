<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ChannelController;

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

    Route::get('app-menu/{id}', [HomeController::class, 'appMenu'])->name('menu');

    // CHANNEL
    Route::group(['middleware' => 'permission:channel access'], function() {
        Route::get('channel', [ChannelController::class, 'index'])->name('channel.index');
        Route::get('channel/create', [ChannelController::class, 'create'])->name('channel.create')->middleware('permission:channel create');
        Route::post('channel', [ChannelController::class, 'store'])->name('channel.store')->middleware('permission:channel create');

        Route::get('channel/{id}', [ChannelController::class, 'show'])->name('channel.show');

        Route::get('channel/{id}/edit', [ChannelController::class, 'edit'])->name('channel.edit')->middleware('permission:channel edit');
        Route::post('channel/{id}', [ChannelController::class, 'update'])->name('channel.update')->middleware('permission:channel edit');
    });

    // AREA
    Route::group(['middleware' => 'permission:area access'], function() {
        Route::get('area', [AreaController::class, 'index'])->name('area.index');
        Route::get('area/create', [AreaController::class, 'create'])->name('area.create')->middleware('permission:area create');
        Route::post('area', [AreaController::class, 'store'])->name('area.store')->middleware('permission:area create');

        Route::get('area/{id}', [AreaController::class, 'show'])->name('area.show');

        Route::get('area/{id}/edit', [AreaController::class, 'edit'])->name('area.edit')->middleware('permission:area edit');
        Route::post('area/{id}', [AreaController::class, 'update'])->name('area.update')->middleware('permission:area edit');
    });

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
});
