<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth hand-roll: login/register hanya untuk guest, logout harus login.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Route group contoh per role — halaman asli menyusul (dashboard admin M3, halaman kasir M2).
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', fn () => 'Dashboard admin (placeholder)')->name('dashboard');

    // Master data — pola CRUD untuk modul lain (Supplier/Produk di Prompt 1.7).
    Route::resource('kategori', CategoryController::class)->except('show');
    Route::resource('supplier', SupplierController::class)->except('show');
    Route::resource('produk', ProductController::class)->except('show');
});

Route::prefix('kasir')->name('kasir.')->middleware(['auth', 'role:kasir'])->group(function () {
    Route::get('/', fn () => 'Halaman kasir (placeholder)')->name('home');
});
