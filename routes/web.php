<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Kasir\PosController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Root: tanpa landing page — tamu langsung ke login, yang sudah login ke area sesuai role.
Route::get('/', function (Request $request) {
    $user = $request->user();

    if ($user === null) {
        return redirect()->route('login');
    }

    return $user->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('kasir.home');
})->name('home');

// Auth hand-roll: login/register hanya untuk guest, logout harus login.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Profil sendiri — berlaku untuk admin & kasir.
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
});

// Area admin — semua route dilindungi role:admin.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart', [DashboardController::class, 'chart'])->name('dashboard.chart');

    Route::resource('kategori', CategoryController::class)->except('show');
    Route::resource('supplier', SupplierController::class)->except('show');

    // CSV produk — didaftarkan SEBELUM resource supaya tidak tertangkap {produk}.
    Route::get('/produk/export', [ProductController::class, 'export'])->name('produk.export');
    Route::get('/produk/import', [ProductController::class, 'importForm'])->name('produk.import');
    Route::post('/produk/import', [ProductController::class, 'import'])->name('produk.import.store');
    Route::resource('produk', ProductController::class)->except('show');

    // Manajemen akun kasir (hanya role kasir; admin dikelola lewat Profil).
    Route::resource('kasir', UserController::class)->parameters(['kasir' => 'user']);

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
    Route::post('/riwayat/{sale}/void', [PosController::class, 'void'])->name('riwayat.void');
});
