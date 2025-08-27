<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Masterdata\JabatanController;
use App\Http\Controllers\Admin\Masterdata\UserController;
use App\Http\Controllers\Admin\Masterdata\MenuController;
use App\Http\Controllers\Admin\Masterdata\PermissionController;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/', [LoginController::class, 'login']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware(['auth', 'permission'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('admin/jabatans', JabatanController::class);
    Route::resource('admin/users', UserController::class);
    Route::resource('admin/menus', MenuController::class);

    Route::get('/admin/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/admin/permissions/update', [PermissionController::class, 'update'])->name('permissions.update');
});
