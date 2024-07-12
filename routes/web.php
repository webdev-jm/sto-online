<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\SalesmanController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SystemlogController;
use App\Http\Controllers\AccountBranchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\UploadTemplateController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Livewire\PurchaseOrder\Upload;

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

Route::get('/', [HomeController::class, 'index']);

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::group(['middleware' => 'auth'], function() {

    Route::get('generate-ubo/{account_id}/{branch_id}', [CustomerController::class, 'generateUBO']);
    
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/branches/{id}', [HomeController::class, 'branches'])->name('branches');
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile');

    Route::get('app-menu/{id}', [HomeController::class, 'appMenu'])->name('menu');

    // AJAX
    Route::post('account/ajax', [AccountBranchController::class, 'ajax'])->name('account.ajax');
    Route::get('account/get-ajax/{id}', [AccountBranchController::class, 'getAjax'])->name('account.get-ajax');
    Route::post('sms-account/ajax', [AccountController::class, 'smsAjax'])->name('sms-account.ajax');
    Route::get('sms-account/get-ajax/{id}', [AccountController::class, 'smsGetAjax'])->name('sms-account.get-ajax');

    // CUSTOMER UBO
    Route::group(['middleware' => 'permission:customer ubo access'], function() {
        Route::get('ubo-job', [CustomerController::class, 'uboJob'])->name('ubo-job.index');
    });

    // PURCHASE ORDER
    Route::group(['middleware' => 'permission:purchase order access'], function() {
        Route::get('purchase-order', [PurchaseOrderController::class, 'index'])->name('purchase-order.index');
        Route::get('purchase-order/upload', [PurchaseOrderController::class, 'upload'])->name('purchase-order.upload')->middleware('permission:purchase order upload');

        Route::get('purchase-order/{id}', [PurchaseOrderController::class, 'show'])->name('purchase-order.show');
    });

    // INVENTORIES
    Route::group(['middleware' => 'permission:inventory access'], function() {
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/create', [InventoryController::class, 'create'])->name('inventory.create')->middleware('permission:inventory upload');
        Route::post('inventory', [InventoryController::class, 'store'])->name('inventory.store')->middleware('permission:inventory upload');

        Route::get('inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');

        Route::get('inventory/{id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit')->middleware('permission:inventory edit');
        Route::post('inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update')->middleware('permission:inventory update');

        Route::get('inventory/{id}/restore', [InventoryController::class, 'restore'])->name('inventory.restore')->middleware('permission:inventory restore');
    });
    
    // SALES 
    Route::group(['middleware' => 'permission:sales access'], function() {
        Route::get('sales/dashboard', [SaleController::class, 'dashboard'])->name('sales.dashboard');
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');

        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create')->middleware('permission:sales upload');
        Route::post('sales/upload', [SaleController::class, 'store'])->name('sales.upload')->middleware('permission:sales upload');

        Route::get('sales/{id}', [SaleController::class, 'show'])->name('sales.show');

        Route::get('sales/{id}/edit', [SaleController::class, 'edit'])->name('sales.edit')->middleware('permission:sales edit');

        Route::get('sales/{id}/restore', [SaleController::class, 'restore'])->name('sales.restore')->middleware('permission:sales restore');

        Route::get('sales/{id}/export', [SaleController::class, 'export'])->name('sales.export');
    });

    // CUSTOMERS
    Route::group(['middleware' => 'permission:customer access'], function() {
        Route::get('customer', [CustomerController::class, 'index'])->name('customer.index');
        Route::get('customer/create', [CustomerController::class, 'create'])->name('customer.create')->middleware('permission:customer create');
        Route::post('customer', [CustomerController::class, 'store'])->name('customer.store')->middleware('permission:customer create');

        Route::get('customer/parked', [CustomerController::class, 'parked'])->name('customer.parked')->middleware('permission:customer parked');

        Route::get('customer/{id}', [CustomerController::class, 'show'])->name('customer.show');

        Route::get('customer/{id}/edit', [CustomerController::class, 'edit'])->name('customer.edit')->middleware('permission:customer edit');
        Route::post('customer/{id}', [CustomerController::class, 'update'])->name('customer.update')->middleware('permission:customer edit');

        Route::get('customer/{id}/restore', [CustomerController::class, 'restore'])->name('customer.restore')->middleware('permission:customer restore');

        Route::get('customer/{id}/validate', [CustomerController::class, 'validate_customer'])->name('customer.validate')->middleware('permission:customer parked validation');

        Route::get('customer/{id}/{ubo_id}/same-customer', [CustomerController::class, 'same_customer'])->name('customer.same-customer')->middleware('permission:customer parked validation');
        Route::get('customer/{id}/different-customer', [CustomerController::class, 'different_customer'])->name('customer.different-customer')->middleware('permission:customer parked validation');
    });

    // SALESMEN
    Route::group(['middleware' => 'permission:salesman access'], function() {
        Route::get('salesman', [SalesmanController::class, 'index'])->name('salesman.index');
        Route::get('salesman/create', [SalesmanController::class, 'create'])->name('salesman.create')->middleware('permission:salesman create');
        Route::post('salesman', [SalesmanController::class, 'store'])->name('salesman.store')->middleware('permission:salesman create');

        Route::get('salesman/{id}', [SalesmanController::class, 'show'])->name('salesman.show');

        Route::get('salesman/{id}/edit', [SalesmanController::class, 'edit'])->name('salesman.edit')->middleware('permission:salesman edit');
        Route::post('salesman/{id}', [SalesmanController::class, 'update'])->name('salesman.update')->middleware('permission:salesman edit');

        Route::get('salesman/{id}/restore', [SalesmanController::class, 'restore'])->name('salesman.restore')->middleware('permission:salesman restore');
    });

    // DISTRICT
    Route::group(['middleware' => 'permission:district access'], function() {
        Route::get('district', [DistrictController::class, 'index'])->name('district.index');
        Route::get('district/create', [DistrictController::class, 'create'])->name('district.create')->middleware('permission:district create');
        Route::post('district', [DistrictController::class, 'store'])->name('district.store')->middleware('permission:district create');
        Route::get('district/{id}', [DistrictController::class, 'show'])->name('district.show');
        Route::get('district/{id}/edit', [DistrictController::class, 'edit'])->name('district.edit')->middleware('permission:district edit');
        Route::post('district/{id}', [DistrictController::class, 'update'])->name('district.update')->middleware('permission:district edit');
        Route::get('district/{id}/restore', [DistrictController::class, 'restore'])->name('district.restore')->middleware('permission:district restore');
    });

    // CHANNEL
    Route::group(['middleware' => 'permission:channel access'], function() {
        Route::get('channel', [ChannelController::class, 'index'])->name('channel.index');
        Route::get('channel/create', [ChannelController::class, 'create'])->name('channel.create')->middleware('permission:channel create');
        Route::post('channel', [ChannelController::class, 'store'])->name('channel.store')->middleware('permission:channel create');

        Route::get('channel/{id}', [ChannelController::class, 'show'])->name('channel.show');

        Route::get('channel/{id}/edit', [ChannelController::class, 'edit'])->name('channel.edit')->middleware('permission:channel edit');
        Route::post('channel/{id}', [ChannelController::class, 'update'])->name('channel.update')->middleware('permission:channel edit');

        Route::get('channel/{id}/restore', [ChannelController::class, 'restore'])->name('channel.restore')->middleware('permission:channel restore');
    });

    // AREA
    Route::group(['middleware' => 'permission:area access'], function() {
        Route::get('area', [AreaController::class, 'index'])->name('area.index');
        Route::get('area/create', [AreaController::class, 'create'])->name('area.create')->middleware('permission:area create');
        Route::post('area', [AreaController::class, 'store'])->name('area.store')->middleware('permission:area create');

        Route::get('area/{id}', [AreaController::class, 'show'])->name('area.show');

        Route::get('area/{id}/edit', [AreaController::class, 'edit'])->name('area.edit')->middleware('permission:area edit');
        Route::post('area/{id}', [AreaController::class, 'update'])->name('area.update')->middleware('permission:area edit');

        Route::get('area/{id}/restore', [AreaController::class, 'restore'])->name('area.restore')->middleware('permission:area restore');
    });

    // LOCATION
    Route::group(['middleware' => 'permission:location access'], function() {
        Route::get('location', [LocationController::class, 'index'])->name('location.index');
        Route::get('location/create', [LocationController::class, 'create'])->name('location.create')->middleware('permission:location create');
        Route::post('location', [LocationController::class, 'store'])->name('location.store')->middleware('permission:location create');

        Route::get('location/{id}', [LocationController::class, 'show'])->name('location.show');

        Route::get('location/{id}/edit', [LocationController::class, 'edit'])->name('location.edit')->middleware('permission:location edit');
        Route::post('location/{id}', [LocationController::class, 'update'])->name('location.update')->middleware('permission:location edit');

        Route::get('location/{id}/restore', [LocationController::class, 'restore'])->name('location.restore')->middleware('permission:location restore');
    });

    // ACCOUNT
    Route::group(['middleware' => 'permission:account access'], function() {
        Route::get('account', [AccountController::class, 'index'])->name('account.index');
        Route::get('account/create', [AccountController::class, 'create'])->name('account.create')->middleware('permission:account create');
        Route::post('account', [AccountController::class, 'store'])->name('account.store')->middleware('permission:account create');
        Route::get('account/{id}', [AccountController::class, 'show'])->name('account.show');
        Route::get('account/{id}/edit', [AccountController::class, 'edit'])->name('account.edit')->middleware('permission:account edit');
        Route::post('account/{id}', [AccountController::class, 'update'])->name('account.update')->middleware('permission:account edit');

        Route::get('account/{id}/create-template', [AccountController::class, 'create_template'])->name('account.create-template');
    });

    // ACCOUNT BRANCH
    Route::group(['middleware' => 'permission:account branch access'], function() {
        Route::get('account-branch', [AccountBranchController::class, 'index'])->name('account-branch.index');
        Route::get('account-branch/create', [AccountBranchController::class, 'create'])->name('account-branch.create')->middleware('permission:account branch create');
        Route::post('account-branch', [AccountBranchController::class, 'store'])->name('account-branch.store')->middleware('permission:account branch create');

        Route::get('account-branch/{id}', [AccountBranchController::class, 'show'])->name('account-branch.show');

        Route::get('account-branch/{id}/generateToken', [AccountBranchController::class, 'generateToken'])->name('account-branch.generateToken')->middleware('permission:account branch generate token');

        Route::get('account-branch/{id}/edit', [AccountBranchController::class, 'edit'])->name('account-branch.edit')->middleware('permission:account branch edit');
        Route::post('account-branch/{id}', [AccountBranchController::class, 'update'])->name('account-branch.update')->middleware('permission:account branch edit');
    });

    // ROLES
    Route::group(['middleware' => 'permission:role access'], function() {
        Route::get('role', [RoleController::class, 'index'])->name('role.index');
        Route::get('role/add', [RoleController::class, 'create'])->name('role.create')->middleware('permission:role create');
        Route::post('role', [RoleController::class, 'store'])->name('role.store')->middleware('permission:role create');

        Route::get('role/{id}', [RoleController::class, 'show'])->name('role.show');

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

    // SYSTEMLOGS
    Route::group(['middleware' => 'permission:systemlog'], function() {
        Route::get('systemlog', [SystemlogController::class, 'index'])->name('systemlog');
    });

    // REPORTS
    Route::group(['middleware' => 'permission:report access'], function() {
        Route::get('report', [ReportController::class, 'index'])->name('report.index');
        Route::get('report/vmi', [ReportController::class, 'vmi_report'])->name('report.vmi')->middleware('permission:report vmi');
    });

    // TEMPLATES
    Route::group(['middleware' => 'permission:template access'], function() {
        Route::get('template', [UploadTemplateController::class, 'index'])->name('template.index');
        Route::get('template/create', [UploadTemplateController::class, 'create'])->name('template.create')->middleware('permission:template create');
        Route::post('template', [UploadTemplateController::class, 'store'])->name('template.store')->middleware('permission:template create');

        Route::get('template/{id}', [UploadTemplateController::class, 'show'])->name('template.show');

        Route::get('template/{id}/edit', [UploadTemplateController::class, 'edit'])->name('template.edit')->middleware('permission:template edit');
        Route::post('template/{id}', [UploadTemplateController::class, 'update'])->name('template.update')->middleware('permission:template edit');

        Route::get('template/{id}/restore', [UploadTemplateController::class, 'restore'])->name('template.restore')->middleware('permission:template restore');
    });

    // ERROR LOGS
    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->name('error-logs')->middleware('permission:error logs');
});
