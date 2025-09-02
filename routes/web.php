<?php

use App\Http\Controllers\Admin\ManajemenStok\InventoryController;
use App\Http\Controllers\Admin\StokMasuk\DaftarPenerimaanBarangController;
use App\Http\Controllers\Admin\StokMasuk\PenerimaanTransferController;
use App\Http\Controllers\Admin\TransferGudang\BuatPermintaanTransferController;
use App\Http\Controllers\Admin\TransferGudang\PermintaanMasukController;
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
use App\Http\Controllers\Admin\ManajemenStok\KartuStokController;
use App\Http\Controllers\Admin\ManajemenStok\WarehouseStokController;

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
        Route::post('daftar-penerimaan-barang/{stockInOrder}/update-status', [DaftarPenerimaanBarangController::class, 'updateStatus'])->name('daftar-penerimaan-barang.updateStatus');

        Route::resource('penerimaan-transfer', PenerimaanTransferController::class)->only(['index', 'show'])->parameter('penerimaan-transfer', 'transferRequest');
        Route::post('penerimaan-transfer/{transferRequest}/update-status', [PenerimaanTransferController::class, 'updateStatus'])->name('penerimaan_transfer.updateStatus');
    });

    Route::prefix('admin/manajemen-stok')->name('admin.manajemenstok.')->group(function () {
        Route::get('kartu-stok', [KartuStokController::class, 'index'])->name('kartustok.index');
        Route::get('warehouse-stok', [WarehouseStokController::class, 'index'])->name('warehousestok.index');
        Route::get('master-stok', [InventoryController::class, 'index'])->name('masterstok.index');
    });

    Route::prefix('admin/transfer-gudang')->name('admin.transfergudang.')->group(function () {
        Route::resource('permintaan-terkirim', BuatPermintaanTransferController::class)->parameter('permintaan-terkirim', 'transferRequest');
        Route::resource('permintaan-masuk', PermintaanMasukController::class)->only(['index', 'show'])->parameter('permintaan-masuk', 'transferRequest');
        Route::post('permintaan-masuk/{transferRequest}/update-status', [PermintaanMasukController::class, 'updateStatus'])->name('permintaan-masuk.updateStatus');
        Route::post('calculate-item-values', [BuatPermintaanTransferController::class, 'calculateItemValues'])->name('calculate-item-values');
    });

});


