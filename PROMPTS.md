# 🎯 Urutan Prompt untuk OpenCode — KasirKu (POS & Inventaris)

> **Cara pakai:** jalankan prompt **satu per satu**, berurutan. Selesai satu prompt →
> verifikasi hasilnya → baru lanjut ke prompt berikutnya. Jangan gabung beberapa tugas
> dalam satu prompt.
>
> **Sebelum mulai:** pastikan `AGENTS.md`, `opencode.json`, dan `docs/pos-inventaris-plan.md`
> sudah ada di root project. Konteks proyek dimuat otomatis setiap sesi — jadi prompt di
> bawah cukup berupa **perintah tugas**, bukan penjelasan ulang proyek.

---

## ⚙️ FASE 0 — Persiapan (sebelum prompt apa pun)

Jalankan di terminal Laragon / VSCode, di folder `C:\laragon\www\kasirku`:

```bash
composer create-project laravel/laravel kasirku
# (copy AGENTS.md, opencode.json, docs/ ke dalam folder kasirku)
php artisan migrate
```

Lalu di OpenCode, **opsional** jalankan `/init` sekali untuk menyesuaikan `AGENTS.md`
dengan struktur skeleton yang baru dibuat.

**Prompt #0 — Orientasi:**
```
Baca AGENTS.md dan docs/pos-inventaris-plan.md. Jangan tulis kode apa pun dulu.
Ringkas: (1) prinsip wajib proyek, (2) package yang dilarang, (3) urutan milestone M1–M4,
(4) apa deliverable M1. Tunggu konfirmasi saya sebelum lanjut.
```
> Ini memastikan OpenCode benar-benar sudah "paham" sebelum menulis kode.

---

## 🔴 M1 — Setup + Auth Hand-roll + Master Data

### Prompt 1.1 — Konfigurasi & struktur dasar
```
Setup dasar proyek:
1. Sesuaikan .env untuk Laragon: APP_URL=http://kasirku.test, DB mysql
   (host 127.0.0.1, port 3306, database kasirku, user root, password kosong).
2. Buat file .env.example yang bersih tanpa kredensial asli.
3. Buat struktur folder app/Services/ (kosong dulu, siap diisi).
Jangan tambah dependency baru. Tunjukkan file yang kamu ubah.
```

### Prompt 1.2 — Migration & Model User (dengan role)
```
Bikin migration & model untuk tabel users dengan kolom tambahan `role`
(enum/string: 'admin' | 'kasir', default 'kasir').
- Edit migration bawaan users, jangan buat tabel baru.
- Di app/Models/User.php: tambahkan 'role' ke $fillable dan helper isAdmin(): bool.
- Update UserFactory supaya default role = 'kasir', dan sediakan state admin().
Jangan pakai Spatie atau package permission apa pun.
```

### Prompt 1.3 — Auth hand-roll (controller + route)
```
Bikin auth hand-roll memakai fitur bawaan Laravel (Auth facade, Hash, session guard):
1. app/Http/Controllers/Auth/LoginController.php → showLoginForm(), login(), logout()
2. app/Http/Controllers/Auth/RegisterController.php → showRegistrationForm(), register()
3. Form Request untuk validasi login & register (app/Http/Requests/Auth/).
4. Daftarkan route di routes/web.php (guest middleware untuk login/register,
   auth untuk logout).
Aturan: password WAJIB di-hash pakai Hash. login pakai Auth::attempt() + regenerate session.
Setelah login sukses, redirect sesuai role (admin → dashboard admin, kasir → halaman kasir).
Jangan pakai Breeze/Fortify/Jetstream.
```

### Prompt 1.4 — Blade auth
```
Bikin view Blade untuk auth, pakai Tailwind (boleh via CDN dulu):
- resources/views/auth/login.blade.php
- resources/views/auth/register.blade.php
- resources/views/layouts/app.blade.php (layout dasar + slot)
Setiap form WAJIB ada @csrf. Tampilkan pesan error validasi.
Tambahkan sedikit styling yang rapi dan responsif.
```

### Prompt 1.5 — Middleware role
```
Bikin middleware custom untuk otorisasi berbasis role:
1. app/Http/Middleware/EnsureRole.php → cek $request->user()->role.
   Kalau tidak sesuai → abort(403).
2. Daftarkan alias middleware 'role' di bootstrap/app.php (gaya Laravel 11+/13).
3. Buat route group contoh: /admin/* pakai ['auth','role:admin'],
   dan /kasir/* pakai ['auth','role:kasir'].
Jelaskan singkat cara kerja registrasinya di Laravel 13.
```

### Prompt 1.6 — CRUD Master Data (Kategori)
```
Bikin CRUD Kategori (pola contoh yang akan dipakai modul lain):
- Migration + Model Category.
- Controller resource (index, create, store, edit, update, destroy).
- Form Request untuk validasi (nama wajib, unik).
- Blade views: index (tabel + pagination), create, edit.
- Route resource, dilindungi middleware ['auth','role:admin'].
Jalankan `php artisan migrate` dan pastikan tidak error.
```

### Prompt 1.7 — CRUD Master Data (Supplier & Produk)
```
Ikuti pola CRUD Kategori tadi, buat:
1. Supplier: name, phone, address.
2. Product: category_id (FK), supplier_id (FK nullable), sku (unik), barcode (nullable),
   name, unit, cost_price, sell_price, stock (default 0), min_stock (default 0), is_active.
   - Relasi: Product belongsTo Category & Supplier.
   - Validasi angka untuk harga & stok.
   - Tampilkan nama kategori/supplier di tabel index (eager load, hindari N+1).
Semua route dilindungi middleware role admin.
```

### ✅ Verifikasi M1 (jalankan di OpenCode)
```
Verifikasi M1:
1. Jalankan `php artisan migrate:fresh --seed` — pastikan sukses.
2. Jalankan `php artisan route:list` — tunjukkan route auth, admin, kasir.
3. Cek: apakah ada tempat yang menyimpan password tanpa Hash? 
4. Cek: apakah ada route admin yang belum dilindungi middleware role?
Laporkan temuan, jangan ubah kode dulu.
```

---

## 🟡 M2 — Stock Movements + POS + Struk

### Prompt 2.1 — Tabel stock_movements + StockService
```
Bikin sistem pergerakan stok (INI INTI, ikuti aturan AGENTS.md):
1. Migration + Model StockMovement: product_id, type (enum in|out|adjust), qty,
   reference_type (nullable), reference_id (nullable), note (nullable), user_id, timestamps.
   - Relasi ke Product & User.
2. app/Services/StockService.php dengan method:
   - recordIn(Product $p, int $qty, ...): tambah stok + catat movement type=in
   - recordOut(Product $p, int $qty, ...): kurangi stok + catat movement type=out
   - adjust(Product $p, int $newQty, ...): set stok ke nilai baru + catat movement type=adjust
   Semua method WAJIB: (a) update products.stock (cache), (b) tulis StockMovement,
   (c) tolak kalau stok jadi minus.
Jangan update products.stock langsung di tempat lain.
```

### Prompt 2.2 — UI Stok Masuk / Opname
```
Bikin halaman manajemen stok (role admin):
1. Form "Stok Masuk": pilih produk, input qty, catatan → panggil StockService::recordIn().
2. Form "Stok Opname": pilih produk, input stok fisik → StockService::adjust().
3. Halaman riwayat stock_movements: tabel dengan filter produk & tipe, pagination.
Semua lewat StockService, jangan sentuh kolom stock langsung.
```

### Prompt 2.3 — POS: halaman kasir + keranjang
```
Bikin halaman kasir (POS) memakai Alpine.js (via CDN) + fetch ke endpoint Laravel:
1. Halaman /kasir: input cari produk (by nama/barcode), daftar produk, tombol tambah ke keranjang.
2. Keranjang: state Alpine (array), bisa ubah qty & hapus item, hitung subtotal otomatis.
3. Endpoint GET /kasir/produk?q=... untuk pencarian produk (JSON).
4. Tampilkan total, input diskon, input bayar, hitung kembalian.
Jangan pakai Livewire. Fokus UX cepat untuk kasir.
```

### Prompt 2.4 — SaleService (transaksi atomik) ⚠️ PALING PENTING
```
Bikin proses simpan transaksi penjualan — WAJIB atomik (ikuti AGENTS.md):
1. Migration + Model: Sale (invoice_no, user_id, customer_id nullable, subtotal, discount,
   tax, total, paid, change, payment_method, status enum paid|void, timestamps) dan
   SaleItem (sale_id, product_id, price, qty, subtotal).
2. app/Services/SaleService.php::store(array $cart, array $payment): 
   - DB::beginTransaction()
   - generate invoice_no unik (mis. INV-YYYYMMDD-0001)
   - create Sale + SaleItem
   - foreach item: Product::lockForUpdate()->find() → cek stok → kurangi + catat StockMovement (type=out)
   - DB::commit(), tangkap exception → DB::rollBack() + lempar error yang jelas
   - TOLAK transaksi kalau stok kurang (jangan sampai stok minus)
3. Controller + route POST /kasir/checkout yang memanggil SaleService.
Jelaskan di komentar kenapa pakai lockForUpdate().
```

### Prompt 2.5 — Halaman struk
```
Bikin halaman struk setelah checkout:
- Route GET /kasir/struk/{sale} menampilkan detail Sale + SaleItem.
- Layout struk ramah printer (CSS @media print: sembunyikan tombol/nav, ukuran 58mm/80mm).
- Tombol "Cetak" memanggil window.print().
- Data: nama toko, invoice_no, tanggal, kasir, item, total, bayar, kembalian.
Tanpa package PDF.
```

### ✅ Verifikasi M2
```
Verifikasi M2:
1. Tulis feature test: transaksi mengurangi stok dengan benar (stok awal 10, jual 3 → stok 7,
   dan ada StockMovement type=out qty=3).
2. Tulis feature test: transaksi dengan stok kurang harus GAGAL dan stok tidak berubah.
3. Jalankan `php artisan test`. Laporkan hasilnya.
```

---

## 🟢 M3 — Laporan + Dashboard

### Prompt 3.1 — Laporan penjualan
```
Bikin modul laporan (role admin):
1. Laporan penjualan per rentang tanggal: total transaksi, total omzet, jumlah item terjual.
2. Laporan produk terlaris (group by product, sum qty, urut desc, top 10).
3. Laporan stok menipis: produk dengan stock <= min_stock.
Gunakan query Eloquent/Query Builder yang efisien (hindari N+1). Halaman Blade dengan filter tanggal.
```

### Prompt 3.2 — Export CSV (native)
```
Tambahkan export CSV pada laporan penjualan memakai fputcsv() native PHP:
- Route GET /admin/laporan/penjualan/export (parameter tanggal ikut).
- Set header Content-Type: text/csv dan Content-Disposition attachment.
- Nama file menyertakan rentang tanggal.
JANGAN pakai maatwebsite/excel.
```

### Prompt 3.3 — Dashboard + grafik
```
Bikin dashboard admin dengan Chart.js (via CDN):
1. Kartu ringkasan: omzet hari ini, jumlah transaksi hari ini, produk stok menipis.
2. Grafik garis: penjualan 7/30 hari terakhir.
3. Grafik/daftar: 5 produk terlaris.
Bikin endpoint JSON ringan untuk data grafik (mis. /admin/dashboard/chart).
```

### ✅ Verifikasi M3
```
Verifikasi M3:
1. Cek angka laporan cocok dengan data di database (buat 3-5 transaksi uji).
2. Pastikan file CSV bisa dibuka di Excel dan datanya benar.
3. Cek query laporan tidak N+1 (aktifkan query log bila perlu).
Laporkan temuan.
```

---

## 🔵 M4 — Otorisasi Rapi, Testing, Deploy, README

### Prompt 4.1 — Gate & Policy
```
Rapikan otorisasi memakai Gate/Policy bawaan Laravel:
1. Policy ProductPolicy & SalePolicy (mis. kasir hanya boleh void transaksinya sendiri,
   admin boleh semua).
2. Definisikan Gate untuk aksi administratif di AuthServiceProvider.
3. Terapkan authorize()/can() di controller & tombol Blade (mis. @can).
Tanpa package.
```

### Prompt 4.2 — Seeder & Factory demo
```
Bikin seeder + factory untuk data demo (agar orang bisa langsung coba):
- 1 user admin (admin@demo.com) & 1 kasir (kasir@demo.com), password: password
- 10 kategori, 5 supplier, 50 produk dengan stok acak
- 30 transaksi penjualan tersebar 30 hari terakhir (via SaleService agar stok konsisten)
Jalankan `php artisan migrate:fresh --seed` dan pastikan sukses.
```

### Prompt 4.3 — Feature test menyeluruh
```
Tambah feature test (PHPUnit):
1. Guest tidak bisa akses /admin (redirect ke login).
2. Kasir tidak bisa akses route admin (403).
3. Admin bisa CRUD produk.
4. Transaksi gagal bila stok kurang (stok tidak berubah).
5. StockMovement tercatat setiap perubahan stok.
Jalankan `php artisan test` sampai semua hijau.
```

### Prompt 4.4 — README portofolio
```
Tulis README.md yang menjual untuk portofolio, berisi:
- Judul + deskripsi singkat + screenshot/GIF placeholder
- Fitur utama
- Tech stack (Laravel 13, full manual/minim package)
- Cara install di Laragon (langkah jelas)
- Akun demo (admin@demo.com / kasir@demo.com, password: password)
- 3 keputusan teknis: (1) auth & otorisasi hand-roll, (2) stock_movements auditable,
  (3) transaksi atomik dengan lockForUpdate()
- Link demo (placeholder)
Bahasanya menarik tapi jujur.
```

### ✅ Verifikasi M4 (final)
```
Verifikasi akhir:
1. `php artisan test` — semua hijau.
2. `php artisan migrate:fresh --seed` — sukses, akun demo bisa login.
3. Cek tidak ada dependency terlarang di composer.json.
4. Cek .env tidak ter-commit (ada di .gitignore).
Laporkan ringkasan kesiapan deploy.
```

---

## 🧯 Kalau OpenCode Melenceng

| Masalah | Cara perbaiki |
|---|---|
| Pakai Breeze/Spatie/Livewire | "Ingat AGENTS.md: dilarang pakai package itu. Ganti dengan fitur bawaan Laravel." |
| Update `products.stock` langsung | "Semua perubahan stok harus lewat StockService + StockMovement. Perbaiki." |
| Transaksi tanpa lockForUpdate | "Bungkus dengan DB transaction dan pakai lockForUpdate(). Lihat AGENTS.md." |
| Lupa @csrf di form | "Setiap form POST/PUT/DELETE wajib @csrf (Laravel 13 pakai PreventRequestForgery)." |
| Masalah berulang | **Tambahkan aturan baru ke AGENTS.md**, jangan cuma koreksi di chat. |

---

## 💡 Tips Umum

- **Satu tugas per prompt.** Verifikasi sebelum lanjut.
- Pakai agent **Plan** (read-only) untuk diskusi desain, **Build** untuk menulis kode.
- Untuk fitur rumit, minta OpenCode **jelaskan rencananya dulu** sebelum menulis kode.
- Commit git tiap selesai satu prompt: `git commit -m "feat(auth): login/register/logout hand-roll"`.
- Kalau konteks mulai kacau, mulai sesi baru — `AGENTS.md` akan memuat ulang konteks proyek.
