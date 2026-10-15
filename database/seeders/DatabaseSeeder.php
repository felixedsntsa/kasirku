<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed aplikasi: HANYA dua akun inti — database bersih,
     * data isi (kategori, produk, stok, transaksi) diisi admin lewat aplikasi.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        User::factory()->create([
            'name' => 'Kasir',
            'email' => 'kasirku@gmail.com',
            'password' => Hash::make('kasirku123'),
        ]);
    }
}
