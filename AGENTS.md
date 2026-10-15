# AGENTS.md — KasirKu (POS & Inventaris)

Laravel 13 (PHP 8.3+) — aplikasi POS + inventaris, portofolio **hand-roll / minim package**.
Dikembangkan di Laragon (Windows): app di `http://kasirku.test`, MySQL database `kasirku` (user `root`, password kosong).

**Rujukan utama:**
- `docs/pos-inventaris-plan.md` — arsitektur, ERD, keputusan teknis (dimuat otomatis tiap sesi via `opencode.json`)
- `docs/DESIGN.md` — **panduan desain UI (WAJIB dibaca sebelum menyentuh Blade/CSS apa pun).**
- `PROMPTS.md` — urutan tugas per milestone (M1–M4), jalankan satu per satu

**Status (Okt 2026):** masih skeleton Laravel bawaan — belum ada auth, Services, atau modul POS.

## Prinsip wajib (JANGAN dilanggar)

1. **Minim package.** Dilarang: Breeze/Jetstream/Fortify, `spatie/laravel-permission`,
   `maatwebsite/excel`, `barryvdh/laravel-dompdf`, Livewire. Pakai fitur bawaan Laravel:
   `Auth`/`Hash`/Gate/Policy, `fputcsv()`, `window.print()`. Jangan tambah dependency tanpa persetujuan user.
2. **Auth ditulis sendiri** (`Auth::attempt()` + `Hash::make()` + session guard) di
   `app/Http/Controllers/Auth/`. Tidak ada password plaintext.
3. **Role = kolom `role` di tabel `users`** (`admin` | `kasir`) + middleware `EnsureRole` +
   Gate/Policy. Alias middleware `role` didaftarkan di `bootstrap/app.php` (`->withMiddleware()`, gaya Laravel 11+).
   Proteksi route: `->middleware(['auth', 'role:admin'])`.
4. **Stok tidak boleh diupdate langsung.** Semua perubahan lewat `StockService` +
   tabel `stock_movements` (`type`: in|out|adjust). Kolom `products.stock` hanya cache.
5. **Transaksi penjualan wajib atomik** di `SaleService::store()`: `DB::beginTransaction()` →
   create sale + sale_items → per item `lockForUpdate()` pada product → cek stok → kurangi +
   catat movement → `commit()`. Stok tidak boleh minus; transaksi gagal bila stok kurang.
6. **Selalu `@csrf`** di form POST/PUT/DELETE (Laravel 13 memakai `PreventRequestForgery`).

## Perintah

```bash
composer setup                              # install + .env + key:generate + migrate + npm install --ignore-scripts + build
composer dev                                 # php artisan dev (server + queue + vite concurrently)
composer test                                # config:clear lalu php artisan test
php artisan test --filter=SaleServiceTest    # satu test class/method
./vendor/bin/pint                           # format PHP (laravel/pint sudah terpasang)
php artisan migrate:fresh --seed             # reset DB + data demo
```

## Environment & gotchas

- `.env` masih bawaan skeleton (**sqlite**) — sesuaikan ke MySQL Laragon bila belum (lihat plan §10C).
- **Test jalan di sqlite `:memory:`** (`phpunit.xml`) — migration harus kompatibel SQLite
  (hindari fitur MySQL-only; enum cukup `string` + validasi).
- `.npmrc` berisi `ignore-scripts=true` — instalasi npm wajib `--ignore-scripts` (sudah di `composer setup`).
- Frontend: Blade + Tailwind 4 via Vite (`resources/css/app.css`, `resources/js/app.js`);
  Alpine.js & Chart.js via **CDN**. Font di-fetch plugin `bunny` di `vite.config.js` (butuh internet saat build).
- Struk cetak: HTML + CSS `@media print` (58mm/80mm) + `window.print()` — tanpa package PDF.

## Gotcha nulis test (Laravel 13 + phpunit.xml)

- `assertSee()` **meng-escape default** — untuk cek atribut HTML (`name="x"`) atau teks dengan
  tanda kutip, pakai `assertSee('name="x"', false)`.
- **Session driver `array` tidak persist antar `$this->get()` terpisah dalam satu test** —
  flash data (errors, old input) hilang. Untuk test "error validasi tampil di halaman":
  `$this->from('/login')->followingRedirects()->post(...)->assertSee(...)`.
- Komentar Blade harus `{{--` murni — satu karakter nyasar di dalamnya merusak kompilasi jadi echo.

## Konvensi kode

- Service layer di `app/Services/` (`StockService`, `SaleService`) — logika bisnis keluar dari controller; controller tipis.
- Form Request untuk validasi (`app/Http/Requests/`); eager load relasi di tabel index (hindari N+1).
- ERD lengkap & field tiap tabel: `docs/pos-inventaris-plan.md` §4.

## Konvensi UI (anti "AI slop")

**WAJIB baca `docs/DESIGN.md` sebelum menulis/mengubah Blade atau CSS apa pun.** Ringkas:
- Dilarang: gradient ungu/indigo, emoji sebagai ikon, `rounded-2xl`/`shadow-xl` seragam,
  glassmorphism, animasi berlebihan, teks marketing di UI internal.
- Warna: mayoritas netral; **satu** aksen tegas (bukan indigo `#6366f1` default). Warna status
  hanya untuk makna status. Aksen dipakai hemat.
- Radius dari skala (4/8/12px); shadow maksimum `shadow-sm`; spacing skala 4px.
- Angka/uang: `tabular-nums`. Judul halaman ≤ `text-2xl`.
- Pakai komponen reusable di `@layer components` `app.css` (`.btn`, `.card`, `.input`,
  `.table-base`, `.badge-*`) — jangan tulis 20 class Tailwind inline berulang.
- Token baru → tambahkan di `@theme` `app.css`, jangan hardcode di Blade.
- Sebelum menandai tugas UI selesai: jalankan checklist `docs/DESIGN.md` §7.
- Kalau ragu antara "lebih menarik" vs "sesuai aturan": **pilih sesuai aturan.**

## Alur kerja

- Milestone: M1 auth+master data → M2 stok+POS+struk → M3 laporan+dashboard → M4 policy+testing+deploy (plan §7).
- Kerjakan satu prompt/modul per sesi, verifikasi dulu sebelum lanjut; commit kecil & deskriptif.
- Verifikasi milestone: `php artisan migrate:fresh --seed` sukses + `php artisan test` hijau +
  cek route admin terproteksi `role`.
- Data awal **hanya 2 akun** (DB bersih, isi diisi admin lewat aplikasi):
  `admin@gmail.com` / `admin123` (admin) & `kasirku@gmail.com` / `kasirku123` (kasir).
