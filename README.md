<p align="center"><img src="public/favicon.svg" width="72" alt="KasirKu"></p>

<h1 align="center">KasirKu</h1>

<p align="center">
  <strong>Point of Sale &amp; Inventaris</strong> — Laravel 13, ditulis manual tanpa template admin,
  tanpa package otorisasi eksternal.
</p>

<p align="center">
  <a href="#fitur">Fitur</a> ·
  <a href="#tech-stack">Tech Stack</a> ·
  <a href="#cara-menjalankan">Cara Menjalankan</a> ·
  <a href="#akun-demo">Akun Demo</a> ·
  <a href="#3-keputusan-teknis">Keputusan Teknis</a> ·
  <a href="#pengujian">Pengujian</a>
</p>

---

<!--
  === SCREENSHOT (isi setelah benar-benar ada file-nya) ======================
  Ambil screenshot dari aplikasi yang berjalan, simpan di docs/screenshots/,
  lalu hapus tanda komentar di bawah ini.

  Saran 4 shot: dashboard admin, halaman POS (keranjang terisi), struk cetak,
  riwayat transaksi. Rekomendasi lebar 1400px, PNG, tanpa data sensitif.

<p align="center">
  <img src="docs/screenshots/dashboard.png" width="700" alt="Dashboard admin" style="border-radius:8px; border:1px solid #e5e7eb;">
  <br>
  <em>Dashboard admin — omzet, grafik penjualan, dan produk stok menipis.</em>
</p>
  ===========================================================================
-->

<!-- Tambahkan bila tersedia:
<p align="center">Live demo: <a href="https://demo.example.com">https://demo.example.com</a> · akun di bawah.</p>
-->

## Ringkasan

KasirKu adalah aplikasi POS (Point of Sale) untuk toko kecil-menengah: kasir berjualan lewat
halaman POS, stok berkurang otomatis dan tercatat jejaknya, admin memantau omzet dan
kesehatan inventaris dari dashboard. Dibangun sebagai portofolio yang menunjukkan
penguasaan Laravel "polos" — bukan glue antar package.

**Yang membedakan:** seluruh auth, otorisasi, alur stok, dan transaksi ditulis sendiri
memakai fitur bawaan Laravel. Tidak ada Breeze, Spatie Permission, Livewire, atau
maatwebsite/excel. Total dependency runtime: **hanya `laravel/framework` dan `laravel/tinker`.**

## Fitur

### Kasir (POS)
- **Halaman kasir cepat** — pencarian produk (nama/SKU/barcode) via JSON, keranjang Alpine.js,
  ubah qty, diskon & pajak, hitung kembalian otomatis.
- **Struk thermal 58/80 mm** — HTML + CSS `@media print`, cetak langsung dari browser tanpa package PDF.
- **Riwayat transaksi** — lihat/cetak ulang struk, void transaksi (stok kembali otomatis).

### Admin
- **Master data** — CRUD Kategori, Supplier, Produk (SKU unik, barcode, harga beli/jual, stok minimum).
- **Manajemen stok** — stok masuk, stok opname, dan riwayat lengkap pergerakan stok (audit trail).
- **Laporan penjualan** — rekap per rentang tanggal, produk terlaris, stok menipis + **export CSV**.
- **Dashboard** — omzet hari ini, grafik penjualan 7/30 hari (Chart.js), 5 produk terlaris.
- **Kelola kasir** — CRUD akun kasir (admin dikelola lewat halaman profil).
- **Import/Export produk via CSV** — deteksi delimiter `;`/`,`, validasi per baris, laporan error per nomor baris.

### Keduanya
- **Profil** — ubah nama & password (password selalu di-hash).
- **Otorisasi berbasis role** — `admin` | `kasir`, middleware `role` + Gate/Policy per-object.

## Tech Stack

| Lapis | Teknologi |
|---|---|
| Backend | Laravel 13, PHP 8.3+ |
| Database | MySQL (Laragon); test memakai SQLite `:memory:` |
| Frontend | Blade + Tailwind CSS 4 (via Vite) |
| Interaksi | Alpine.js (CDN) — keranjang POS |
| Grafik | Chart.js (CDN) — dashboard |
| Cetak | HTML + `window.print()` (tanpa dompdf/snappy) |
| Test | PHPUnit (feature test) |

> Filosofi: **minim package**. Setiap kali tergoda memasang package, tanya dulu apakah
> fitur bawaan Laravel bisa melakukannya — biasanya bisa.

## Cara Menjalankan

Dikembangkan di **Laragon** (Windows). Prasyarat: PHP 8.3+, Composer, Node.js, MySQL.

```bash
# 1. Clone
git clone https://github.com/felixedsntsa/kasirku.git
cd kasirku

# 2. Setup otomatis (install + .env + key + migrate + npm build)
composer setup

# 3. Jalankan
composer dev          # atau: php artisan serve + npm run dev secara terpisah
```

Buka `http://kasirku.test` (atau `http://127.0.0.1:8000`).

**Konfigurasi manual** bila tidak memakai Laragon — sesuaikan `.env`:

```env
APP_URL=http://kasirku.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasirku
DB_USERNAME=root
DB_PASSWORD=
```

Data awal hanya 2 akun (database bersih — kategori, produk, dan transaksi diisi lewat aplikasi):

```bash
php artisan migrate:fresh --seed
```

## Akun Demo

| Role | Email | Password |
|---|---|---|
| Admin | `admin@gmail.com` | `admin123` |
| Kasir | `kasirku@gmail.com` | `kasirku123` |

## 3 Keputusan Teknis

### 1. Auth & otorisasi ditulis sendiri, bukan package

Login cukup `Auth::attempt()` + `Hash::make()` + session guard — tiga baris yang sudah
disediakan framework. Role hanya kolom `role` di tabel `users` (`admin` | `kasir`),
dijaga middleware `EnsureRole` + `Gate::policy()`:

```php
// routes/web.php
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(...);
```

Keuntungannya: tidak ada abstraksi yang perlu dipelajari orang baru, tidak ada upgrade
Spatie yang memecah aplikasi, dan logika otorisasi bisa dibaca utuh dalam satu file.
Otorisasi juga diperinci per-object lewat Policy — mis. kasir hanya boleh void
transaksi miliknya sendiri:

```php
// SalePolicy
public function void(User $user, Sale $sale): bool
{
    return $user->isAdmin() || $sale->user_id === $user->id;
}
```

### 2. Stok tidak pernah diupdate langsung — selalu lewat jejak audit

Kolom `products.stock` hanyalah **cache**. Satu-satunya jalur perubahan stok adalah
`StockService` yang selalu menulis dua hal: mengupdate kolom, **dan** mencatat baris di
tabel `stock_movements` (`in` | `out` | `adjust`, plus pelaku & referensi).

```php
$stockService->recordOut($product, $qty, 'Penjualan '.$sale->invoice_no, $sale, $user);
```

Konsekuensinya: setiap angka stok bisa dijelaskan asal-usulnya. Selisih opname tinggal
dibaca dari riwayat, bukan ditebak. Prinsip yang sama dipakai di import CSV & stok awal
produk baru — tidak ada stok yang "muncul tanpa sejarah".

### 3. Transaksi penjualan atomik dengan `lockForUpdate()`

Checkout adalah bagian paling rawan race condition: dua kasir menjual produk yang sama
bersamaan bisa membaca stok yang sama lalu mengurangi dobel sampai minus. Solusinya —
satu `DB::transaction()` yang mengunci baris produk:

```php
DB::transaction(function () use ($cart, $payment, $user) {
    // ... create Sale + SaleItem
    $product = Product::lockForUpdate()->find($line['product_id']); // baris terkunci

    if ($product->stock < $qty) {
        throw new InsufficientStockException($product, $qty, $product->stock);
    }
    // ... kurangi stok via StockService, commit di akhir
});
```

Exception apa pun (stok kurang, bayar kurang) melempar **rollback total** — tidak ada
sale setengah jadi, tidak ada stok berkurang sebagian. Void juga atomik: status di-set
`void` dan seluruh stok item dikembalikan dalam satu transaksi, dengan lock yang sama
mencegah void dobel menggandakan stok.

## Pengujian

```bash
composer test
```

53 feature test (206 assertion) menutup:

- **Akses** — guest redirect ke login; kasir ditolak (403) di semua route admin.
- **Stok** — setiap perubahan menghasilkan `stock_movements`; stok tidak pernah minus.
- **Transaksi** — checkout mengurangi stok dengan benar; stok kurang = gagal total & stok tak berubah.
- **Void** — mengembalikan stok; void dobel ditolak; void kasir lain ditolak (policy).
- **Policy** — struk & void hanya untuk pemilik transaksi atau admin.
- **CRUD & CSV** — admin bisa CRUD produk; import CSV valid masuk, baris error dilaporkan.
- **Render** — smoke test semua halaman (menangkap error kompilasi Blade).

## Struktur Proyek

```
app/
├── Exceptions/        InsufficientStockException
├── Http/
│   ├── Controllers/   Admin/, Kasir/, Auth/ — controller tipis
│   ├── Middleware/     EnsureRole (role:admin|kasir)
│   └── Requests/       Form Request per aksi (validasi + authorize)
├── Models/             Product, Sale, SaleItem, StockMovement, Category, Supplier, User
├── Policies/           ProductPolicy, SalePolicy (otorisasi per-object)
└── Services/           StockService, SaleService (logika bisnis & jaminan atomik)
```

Dokumen desain & keputusan arsitektur ada di [`docs/`](docs/).

## Lisensi

MIT — silakan dipelajari, dimodifikasi, dan dipakai.
