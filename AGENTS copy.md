# AGENTS.md — KasirKu (POS & Inventaris)

Panduan proyek untuk OpenCode. Baca ini dulu sebelum mengerjakan apa pun.

## Ringkasan Proyek
Aplikasi **Point of Sale (POS) + Inventaris** berbasis **Laravel 13** (PHP 8.3+).
Tujuan: portofolio kecil–menengah. Dikembangkan di **Laragon** (Windows).

**Dokumen plan lengkap:** `docs/pos-inventaris-plan.md` — RUJUKAN UTAMA.
Selalu konsultasikan keputusan fitur & arsitektur ke file itu sebelum menulis kode.

## Prinsip Wajib (JANGAN DILANGGAR)

1. **Full manual / minim package.** JANGAN pakai starter kit atau package berikut:
   - ❌ Laravel Breeze / Jetstream / Fortify
   - ❌ spatie/laravel-permission
   - ❌ maatwebsite/excel, barryvdh/laravel-dompdf
   - ❌ Livewire
   - ✅ Gunakan fitur **bawaan Laravel**: `Auth`, `Hash`, Gate/Policy, `fputcsv()`, `window.print()`.
   - Package bawaan framework (composer default) tetap boleh.

2. **Auth hand-roll.** Login/register/logout ditulis sendiri:
   - Controller di `app/Http/Controllers/Auth/`
   - Gunakan `Auth::attempt()`, `Hash::make()`, session guard bawaan.
   - JANGAN simpan password plaintext. Selalu `Hash`.

3. **Role = kolom `role` di tabel `users`** (`admin` | `kasir`).
   - Otorisasi lewat middleware custom `EnsureRole` + Gate/Policy.
   - Daftarkan alias middleware di `bootstrap/app.php` (gaya Laravel 11+/13).
   - Contoh proteksi route: `->middleware(['auth', 'role:admin'])`.

4. **Stok TIDAK BOLEH diubah langsung.** Semua perubahan stok WAJIB lewat tabel
   `stock_movements` (type: in | out | adjust). Kolom `products.stock` hanya cache.
   - Buat `StockService` yang menangani penulisan movement + update cache stok.

5. **Transaksi penjualan WAJIB atomik.** Di dalam `SaleService::store()`:
   ```
   DB::beginTransaction();
   // create sale + sale_items
   // foreach item: Product::lockForUpdate()->find() → cek stok → kurangi + catat movement
   DB::commit();
   ```
   Tangani race condition. Stok tidak boleh minus.

## Stack
- Laravel 13, PHP 8.3+, MySQL (Laragon default: user `root`, password kosong)
- Blade + Tailwind (mulai dari CDN boleh) + Alpine.js (CDN)
- Chart.js via CDN untuk grafik
- Testing: **PHPUnit** (bawaan)

## Struktur & Konvensi Kode
- **Service layer**: `app/Services/SaleService.php`, `app/Services/StockService.php`
  — logic bisnis keluar dari Controller.
- **Form Request** untuk validasi (`app/Http/Requests/`).
- **Resource** untuk response API bila perlu.
- Controller tipis; jangan taruh logic bisnis berat di sana.
- Nama route & model ikut konvensi Laravel (PascalCase model, snake_case tabel).
- Selalu sertakan `@csrf` di setiap form POST/PUT/DELETE (Laravel 13 pakai `PreventRequestForgery`).

## ERD (ringkas)
```
users(id, name, email, password, role[admin|kasir], timestamps)
categories(id, name)
suppliers(id, name, phone, address)
products(id, category_id, supplier_id, sku, barcode, name, unit,
         cost_price, sell_price, stock, min_stock, is_active)
stock_movements(id, product_id, type[in|out|adjust], qty,
                reference_type, reference_id, note, user_id, created_at)
customers(id, name, phone)
sales(id, invoice_no, user_id, customer_id, subtotal, discount, tax,
      total, paid, change, payment_method, status[paid|void], created_at)
sale_items(id, sale_id, product_id, price, qty, subtotal)
```
Detail lengkap ada di `docs/pos-inventaris-plan.md`.

## Perintah Penting
```bash
php artisan migrate            # jalankan migration
php artisan migrate:fresh --seed   # reset + isi data demo
php artisan make:model Product -mcr  # model + migration + controller
php artisan test               # jalankan PHPUnit
php artisan route:list         # cek route
npm run dev                    # kalau pakai Vite (opsional)
```
> Di Laragon, aplikasi diakses di `http://kasirku.test` (virtual host otomatis).

## Roadmap (dari plan)
- **M1**: Setup + Auth hand-roll + Master Data (Produk/Kategori/Supplier)
- **M2**: Stock movements + POS + struk
- **M3**: Laporan + Dashboard (Chart.js) + export CSV
- **M4**: Otorisasi rapi (Gate/Policy) + testing + deploy + README

## Aturan untuk Agent
- Kerjakan **satu modul per sesi**, sesuai urutan roadmap. Jangan lompat.
- Sebelum menulis kode baru, cek dulu apakah pola serupa sudah ada di repo.
- Setiap selesai fitur, jalankan `php artisan test` bila ada test terkait.
- JANGAN menambah dependency tanpa persetujuan (ingat prinsip minim package).
- Kalau ragu soal arsitektur, rujuk `docs/pos-inventaris-plan.md`.
- Commit kecil & deskriptif, jangan satu commit raksasa.
