<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Kasir\PosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Auth hand-roll: login/register hanya untuk guest, logout harus login.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Area admin — semua route dilindungi role:admin.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart', [DashboardController::class, 'chart'])->name('dashboard.chart');

    Route::resource('kategori', CategoryController::class)->except('show');
    Route::resource('supplier', SupplierController::class)->except('show');
    Route::resource('produk', ProductController::class)->except('show');

    // Manajemen stok — semua perubahan lewat StockService.
    Route::get('/stok', [StockMovementController::class, 'index'])->name('stok.index');
    Route::get('/stok/masuk', [StockMovementController::class, 'create'])->name('stok.create');
    Route::post('/stok/masuk', [StockMovementController::class, 'store'])->name('stok.store');
    Route::get('/stok/opname', [StockMovementController::class, 'opname'])->name('stok.opname');
    Route::post('/stok/opname', [StockMovementController::class, 'storeOpname'])->name('stok.opname.store');

    // Laporan + export CSV.
    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [ReportController::class, 'export'])->name('laporan.export');
});

// Area kasir (POS) — admin juga boleh mengakses (uji coba/demo).
Route::prefix('kasir')->name('kasir.')->middleware(['auth', 'role:admin,kasir'])->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('home');
    Route::get('/produk', [PosController::class, 'search'])->name('produk.search');
    Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
    Route::get('/struk/{sale}', [PosController::class, 'receipt'])->name('struk');
    Route::get('/riwayat', [PosController::class, 'history'])->name('riwayat');
});
