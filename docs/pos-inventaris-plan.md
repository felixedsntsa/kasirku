# 📋 Project Plan — Sistem POS & Inventaris (Laravel 13)

> **Tujuan:** Portofolio kecil–menengah yang menunjukkan penguasaan CRUD kompleks, relasi DB,
> transaksi atomik, role & permission, laporan, dan UI responsif.
> **Skala:** Single toko / single outlet. **Estimasi:** 3–4 minggu (part-time).
> **Dokumen ini disusun untuk Laravel 13 (rilis 17 Maret 2026, minimum PHP 8.3).**
>
> **Mode pengerjaan:** Dibangun **full manual / hand-roll** — auth, role, export, dan cetak
> dibuat sendiri tanpa starter kit (Breeze/Jetstream) dan minim package. Dikembangkan di
> **Laragon** (Windows).

---

## 1. Ringkasan Proyek

| Item | Keterangan |
|---|---|
| **Nama proyek** | KasirKu / Point of Sale + Inventory (bebas diganti) |
| **Fokus nilai jual** | Proses transaksi yang "benar" (stok berkurang atomik), laporan akurat, UX kasir cepat |
| **Skala** | Kecil–menengah (single toko / single outlet) |
| **Estimasi waktu** | 3–4 minggu (part-time) |
| **Pendekatan** | Full manual: hand-roll auth & role, minim dependency pihak ketiga |
| **Environment** | Laragon (Windows), PHP 8.3, MySQL/MariaDB bawaan Laragon |

---

## 2. Tech Stack (Laravel 13, minim package)

- **Backend:** Laravel 13 (PHP 8.3+ — **wajib**, 8.2 tidak didukung lagi)
- **Database:** MySQL/MariaDB bawaan Laragon (produksi bisa MySQL 8). *Untuk fitur vector opsional: PostgreSQL + `pgvector`*
- **Frontend:** Blade + Tailwind CSS (via Vite, atau CDN untuk mulai cepat) + Alpine.js (via CDN)
- **Interaktivitas:** Alpine + `fetch`/axios ke endpoint Laravel — **tanpa Livewire** (Livewire = package)
- **Auth & Role:** **Hand-roll sendiri** memakai fitur bawaan Laravel:
  - `Auth` facade + session guard + `Hash` (semua built-in, bukan package)
  - Login/Register/Logout = Controller + Form Request + Blade buatan sendiri
  - Role = kolom `role` di tabel `users` + **custom middleware** `EnsureRole` + **Gate/Policy** bawaan Laravel
- **Cetak struk:** **Render HTML → `window.print()`** (browser). Thermal printer (`escpos`) ditunda ke fase opsional.
- **Export laporan:** **Native PHP `fputcsv()`** untuk CSV (bisa dibuka Excel) + **print-to-PDF** dari browser. Tidak pakai `maatwebsite/excel` atau `dompdf`.
- **Grafik dashboard:** Chart.js via CDN
- **Testing:** **PHPUnit** (bawaan Laravel). Pest opsional, bukan wajib.
- **Deploy:** VPS + Nginx (PHP 8.3) atau Railway/Render untuk demo online

### Yang SENGAJA tidak dipakai (biar tetap "manual")
| Biasanya pakai | Diganti dengan |
|---|---|
| Laravel Breeze / Jetstream | Auth controller + Blade buatan sendiri |
| `spatie/laravel-permission` | Kolom `role` + middleware `EnsureRole` + Gate/Policy |
| `maatwebsite/excel` | `fputcsv()` native |
| `barryvdh/laravel-dompdf` | `window.print()` / CSS `@media print` |
| Livewire | Alpine.js + fetch API |

> **Nilai plus portofolio:** menulis auth & otorisasi sendiri (memahami session guard, hashing,
> middleware, Gate) jauh lebih menunjukkan pemahaman Laravel daripada sekadar `composer require`
> starter kit. Sebutkan ini di README sebagai keputusan teknis.

### Catatan khusus Laravel 13 yang relevan untuk proyek ini
- **PHP Attributes (opsional):** konfigurasi seperti `$table`, `$fillable`, `$casts` pada Model kini bisa ditulis sebagai atribut di atas class — kode lebih bersih. Tetap opsional, cara lama masih jalan.
- **`PreventRequestForgery`:** middleware CSRF berganti nama dari `VerifyCsrfToken` dan kini *origin-aware*. Karena Anda menulis form sendiri, pastikan `@csrf` selalu ada di setiap form POST/PUT/DELETE.
- **`Queue::route()`:** routing queue/job terpusat di satu service provider — berguna jika nanti menambah job (mis. kirim struk email, sinkron stok).
- **`Cache::touch()`:** perpanjang TTL cache tanpa fetch ulang — relevan untuk cache laporan yang berat.
- **AI SDK (opsional, pembeda):** bila mau fitur berbasis AI (prediksi stok menipis / ringkasan penjualan), kini tersedia first-party tanpa paket pihak ketiga. **Bukan prioritas MVP.**

---

## 3. Fitur (MoSCoW)

### 🔴 Must-have (MVP)
- **Auth hand-roll**: register, login, logout (Admin & Kasir)
- **Otorisasi**: middleware `EnsureRole` (kasir tidak bisa buka halaman admin)
- Master data: Produk, Kategori, Supplier, Pelanggan (opsional)
- Manajemen stok: stok masuk (pembelian), stok keluar, stok opname
- **Transaksi kasir (POS):** cari produk, keranjang, hitung total, diskon, bayar, kembalian
- Cetak/simpan struk (HTML print)
- Laporan: penjualan harian/bulanan, produk terlaris, stok menipis

### 🟡 Should-have
- Dashboard dengan grafik penjualan (Chart.js)
- Peringatan stok minimum
- Export laporan CSV (native)
- Barcode (scan / generate)
- Riwayat transaksi + detail + void/refund

### 🟢 Could-have (pembeda portofolio)
- Multi-outlet
- Hutang/piutang supplier
- Diskon per-produk & voucher
- Audit log aktivitas
- (Opsional, memanfaatkan Laravel 13) Fitur AI: ringkasan penjualan / prediksi restock

---

## 4. Rancangan Database (ERD inti)

```
users (id, name, email, password, role[admin|kasir], timestamps)
       ← role cukup kolom string/enum; TIDAK pakai tabel permission (spatie)

categories (id, name)
suppliers (id, name, phone, address)
products (id, category_id, supplier_id, sku, barcode, name, unit,
          cost_price, sell_price, stock, min_stock, is_active)

stock_movements (id, product_id, type[in|out|adjust], qty,
                 reference_type, reference_id, note, user_id, created_at)
                 ← SATU tabel untuk semua pergerakan stok (penting!)

customers (id, name, phone)                         ← opsional
sales (id, invoice_no, user_id, customer_id,
       subtotal, discount, tax, total, paid, change,
       payment_method, status[paid|void], created_at)
sale_items (id, sale_id, product_id, price, qty, subtotal)
```

> **Catatan role:** untuk skala kecil, kolom `role` (string/enum) sudah cukup dan paling "manual".
> Kalau nanti butuh role dinamis, baru buat tabel `roles` + `role_user` sendiri — tetap tanpa package.

### Poin arsitektur penting (yang bikin Anda terlihat paham)
- **Stok jangan diupdate langsung** di banyak tempat. Semua perubahan stok lewat **`stock_movements`** + event/observer, sehingga stok selalu bisa diaudit. Kolom `products.stock` hanya cache.
- **Transaksi penjualan wajib pakai DB transaction + `lockForUpdate()`** supaya tidak ada race condition saat 2 kasir menjual produk sama. Ini nilai plus besar saat ditanya interviewer.

---

## 5. Struktur & Pola Kode

- **Service layer:** `app/Services/SaleService.php`, `StockService.php` — logic bisnis keluar dari Controller.
- **Form Request** untuk validasi, **Resource** untuk response.
- **Repository** opsional (jangan over-engineer untuk skala kecil).
- Route dipisah: `routes/web.php` (UI), API bila perlu.

### Struktur hand-roll auth & otorisasi
```
app/Http/Controllers/Auth/
    LoginController.php      (showLoginForm, login, logout)
    RegisterController.php   (showRegistrationForm, register)
app/Http/Middleware/
    EnsureRole.php           (cek $request->user()->role, mis. 'admin')
app/Providers/
    AuthServiceProvider.php  (definisikan Gate, mis. Gate::define('manage-products', ...))
app/Models/User.php          (relasi, helper isAdmin())
```
- Daftarkan alias middleware `role` di `bootstrap/app.php` (Laravel 13 pakai gaya baru).
- Lindungi route: `->middleware(['auth', 'role:admin'])`.
- Gunakan `Gate`/`Policy` untuk aturan halus (mis. kasir hanya boleh void transaksinya sendiri).

---

## 6. Alur Utama Transaksi (contoh flow)

```
Kasir pilih produk → tambah ke cart (Alpine state / session)
  → klik Bayar
  → SaleService::store():
      DB::beginTransaction()
        create sale + sale_items
        foreach item: lockForUpdate(product) → cek stok
          → kurangi stok + catat stock_movements (type=out)
      commit()
  → redirect ke halaman struk (HTML, siap window.print())
```

---

## 7. Roadmap (3–4 minggu, part-time)

| Minggu | Fokus | Deliverable |
|---|---|---|
| **M1** | Setup Laragon + **Auth hand-roll** + Master Data | Project jalan di `kasirku.test`, register/login/logout, middleware role, CRUD Produk/Kategori/Supplier |
| **M2** | Stok + POS inti | Stock movements, halaman kasir, transaksi + struk (print) |
| **M3** | Laporan + Dashboard | Laporan penjualan, grafik Chart.js, export CSV, peringatan stok |
| **M4** | Polish + Deploy | Otorisasi rapi (Gate/Policy), testing PHPUnit, seed data, deploy demo + README |

**Milestone check:**
1. ✅ App bisa register/login & kelola produk (auth buatan sendiri)
2. ✅ Bisa transaksi & cetak struk
3. ✅ Laporan akurat & stok konsisten
4. ✅ Online + dokumentasi portofolio

---

## 8. Testing & Kualitas

- **PHPUnit** (bawaan): feature test transaksi mengurangi stok dengan benar, stok tidak boleh minus, kasir tidak bisa akses route admin.
- **Seeder + factory** untuk demo data (bikin recruiter bisa coba langsung).
- `.env.example` lengkap + README dengan screenshot & akun demo.

---

## 9. Packaging untuk Portofolio

- **README:** deskripsi, fitur, screenshot/GIF, cara install, akun demo, link live.
- **Demo online** (Railway/Render free tier) + akun `admin@gmail.com` / `kasirku@gmail.com`.
- **Commit history rapi** (bukan 1 commit "selesai").
- Sorot 2–3 **keputusan teknis**, mis.:
  - Auth & otorisasi ditulis manual (memahami session guard, hashing, middleware, Gate)
  - Semua pergerakan stok lewat `stock_movements` (auditable)
  - Penanganan race condition dengan DB transaction + `lockForUpdate()`

---

## 10. Setup di Laragon (Langkah Pertama)

### A. Persiapan Laragon
1. Buka Laragon → **Start All** (Apache/Nginx + MySQL menyala).
2. Pastikan versi PHP = **8.3+**: klik kanan tray Laragon → **PHP → Version → 8.3.x**.
   Verifikasi di terminal Laragon: `php -v`.

### B. Membuat project Laravel 13
Pilih salah satu:
```bash
# Opsi 1 — terminal (di folder C:\laragon\www)
composer create-project laravel/laravel kasirku

# Opsi 2 — lewat GUI Laragon
# Klik kanan → Quick app → Laravel → beri nama "kasirku"
```
> `laravel new` hanya jalan bila Laravel Installer sudah di-`composer global require`. Di Laragon lebih praktis pakai `composer create-project`.

### C. Database
1. Buka **HeidiSQL** / **phpMyAdmin** dari Laragon, buat database `kasirku`.
2. Edit `.env`:
   ```
   APP_URL=http://kasirku.test
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kasirku
   DB_USERNAME=root
   DB_PASSWORD=        # Laragon default: kosong
   ```

### D. Akses aplikasi
- Laragon otomatis membuat virtual host → buka `http://kasirku.test`
- Tidak perlu `php artisan serve` (tapi tetap bisa kalau mau).

### E. Urutan pengerjaan
1. Setup Laragon + buat project + konfigurasi `.env` (langkah A–D)
2. **Buat migration** sesuai ERD di atas (mulai dari `users`, `products`, `stock_movements`)
3. **Hand-roll auth**: controller login/register/logout + Blade + middleware `role`
4. Bangun master data (Produk/Kategori/Supplier) sebelum POS
5. Lanjut ke modul stok & POS (M2)

### F. Tips Laragon
- **Terminal Laragon** sudah punya `php`/`composer` di PATH — pakai itu agar versi PHP konsisten.
- Kalau `kasirku.test` tidak resolve, jalankan **Laragon → Reload** atau cek `Menu → Preferences → Auto virtual hosts`.
- Untuk Tailwind/Alpine: bisa mulai dari **CDN** dulu (cepat, tanpa build), pindah ke Vite saat mau produksi.
- Simpan project di `C:\laragon\www\kasirku` agar virtual host otomatis jalan.

---

*Dibuat: rencana proyek portofolio POS & Inventaris berbasis Laravel 13 — mode full manual (hand-roll auth), dikembangkan di Laragon.*
