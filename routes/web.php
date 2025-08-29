<?php

use App\Http\Controllers\Admin\StokMasuk\DaftarPenerimaanBarangController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Masterdata\JabatanController;
use App\Http\Controllers\Admin\Masterdata\UserController;
use App\Http\Controllers\Admin\Masterdata\MenuController;
use App\Http\Controllers\Admin\Masterdata\PermissionController;
use App\Http\Controllers\Admin\Masterdata\UomController;
use App\Http\Controllers\Admin\Masterdata\ItemController;
use App\Http\Controllers\Admin\Masterdata\ItemCategoryController;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware(['auth', 'permission'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::prefix('admin/masterdata')->name('admin.masterdata.')->group(function () {
        Route::resource('jabatans', JabatanController::class);
        Route::resource('users', UserController::class);
        Route::resource('warehouses', \App\Http\Controllers\Admin\Masterdata\WarehouseController::class);
        Route::resource('menus', MenuController::class);
        Route::resource('itemcategories', ItemCategoryController::class);
        Route::resource('uoms', UomController::class);
        Route::resource('items', ItemController::class);
        Route::post('items/check-sku', [ItemController::class, 'checkSkuUniqueness'])->name('items.checkSkuUniqueness');

        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('permissions/update', [PermissionController::class, 'update'])->name('permissions.update');
    });

    Route::prefix('admin/stok-masuk')->name('admin.stok-masuk.')->group(function () {

        Route::resource('daftar-penerimaan-barang', DaftarPenerimaanBarangController::class)->parameter('daftar-penerimaan-barang', 'stockInOrder');
    });

});


