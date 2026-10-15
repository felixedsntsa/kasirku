# 🎨 DESIGN.md — Panduan Desain KasirKu

> **Untuk siapa:** dibaca manusia **dan** AI coding agent (OpenCode) sebelum menyentuh UI apa pun.
> **Tujuan:** memastikan UI terlihat **dirancang dengan sengaja oleh manusia**, bukan hasil
> generate template AI. Dokumen ini mengikat: kalau ada konflik antara "kelihatan keren" dan
> aturan di sini, **aturan di sini menang.**

---

## 0. Konteks Dulu (baca sebelum marah karena "terlalu kaku")

KasirKu adalah **alat kerja operasional**, bukan landing page marketing.

| Ini BUKAN | Ini ADALAH |
|---|---|
| Website jualan / portofolio publik | Alat yang dipakai kasir 8 jam sehari |
| Sekali lihat, lalu ditutup | Dibuka terus, harus cepat dibaca |
| Menarik perhatian | Mengurangi beban kognitif |
| Banyak whitespace, hero besar | Padat, efisien, target klik jelas |

**Konsekuensinya:** desain yang benar untuk POS itu **tenang, padat, dan membosankan dengan cara yang bagus.** Kalau UI-nya "wow" secara visual, biasanya itu salah arah.

---

## 1. Lima Prinsip

1. **Tenang, bukan mencolok.** Warna aksen dipakai hemat. Mayoritas UI = netral (abu-abu).
2. **Hierarki lewat ukuran & bobot, bukan warna.** Judul besar ≠ berwarna. Judul besar = besar.
3. **Kepadatan yang disengaja.** Untuk tabel & POS: rapat tapi bernapas. Bukan kartu-kartu besar berjarak jauh.
4. **Konsistensi > kreativitas per-halaman.** Semua tombol sama. Semua tabel sama. Titik.
5. **Ikon & label jelas > dekorasi.** Tak ada emoji di UI produksi.

---

## 2. 🚫 Anti-Slop: Daftar Terlarang (INI BAGIAN TERPENTING)

Berikut ciri-ciri **"AI slop"** — hal yang HAMPIR SELALU muncul di output AI dan HARUS dihindari.
Kalau Anda (agent) merasa ingin melakukan salah satu dari ini, **jangan**.

### 2.1 Warna
- ❌ **Gradient ungu→biru / indigo→violet.** Ini tanda tangan #1 AI slop. Dilarang total.
- ❌ `bg-gradient-to-r from-purple-500 to-indigo-600` untuk tombol/header/hero.
- ❌ Palet "indigo-500" default Tailwind sebagai warna brand tanpa alasan (`#6366f1`).
  → *Catatan: `app.css` saat ini pakai ini. Lihat §3.1 untuk penggantinya.*
- ❌ Lebih dari **satu** warna aksen di luar warna status.
- ❌ Warna teks abu-abu tipis (`text-gray-400`) untuk teks penting (kontras gagal).
- ❌ Warna-warni tidak konsisten: tiap halaman beda aksen.

### 2.2 Bentuk & Bayangan
- ❌ Semua elemen `rounded-2xl` / `rounded-3xl`. Radius harus dari **skala** (§3.3), bukan seragam maksimal.
- ❌ `shadow-xl` / `shadow-2xl` di mana-mana. Shadow = sinyal kedalaman, bukan dekorasi. Maksimum `shadow-sm`.
- ❌ **Glassmorphism** (`backdrop-blur` + `bg-white/30`). Dilarang.
- ❌ Border + shadow + ring sekaligus di elemen yang sama (mubazir, terlihat amatir).

### 2.3 Tipografi
- ❌ Terlalu banyak ukuran font. Cukup **skala 5–6 langkah** (§3.2).
- ❌ `font-bold` di hampir semua teks. Bold itu untuk menegaskan, bukan default.
- ❌ `tracking-wide` + `uppercase` di banyak tempat.
- ❌ Mengganti font per halaman.

### 2.4 Konten & Ikon
- ❌ **Emoji sebagai ikon UI** (✅❌🔥✨📊 di tombol/heading). Pakai SVG/ikon konsisten.
  → *Pengecualian: dokumen markdown seperti file ini, boleh.*
- ❌ Teks placeholder norak: "Lorem ipsum", "Your amazing product", "Unlock your potential".
- ❌ Kalimat marketing di dalam aplikasi internal ("Kelola bisnis Anda dengan mudah!").
- ❌ Hero section besar dengan headline + subheadline + 2 tombol di aplikasi dalam.

### 2.5 Tata Letak
- ❌ Kartu statistik raksasa yang isinya satu angka + label, berjarak lebar.
- ❌ Terlalu banyak whitespace di halaman data (buang-buang layar kasir).
- ❌ Centering vertikal-horizontal untuk halaman aplikasi (itu untuk login saja).
- ❌ Layout "bento grid" asimetris tanpa alasan fungsional.

### 2.6 Interaksi
- ❌ Animasi berlebihan (bounce, float, pulse di elemen diam).
- ❌ Hover effect yang mengubah ukuran (`scale-110`) di tabel/tombol.
- ❌ Loading spinner tanpa teks di mana-mana.

---

## 3. Design Tokens (satu sumber kebenaran)

Semua token hidup di `resources/css/app.css` dalam blok `@theme`. **Jangan hardcode nilai** di Blade — pakai token/utility.

### 3.1 Warna

Ganti palet "indigo" dengan **netral hangat + satu aksen tegas**. Rekomendasi:

```css
@theme {
  /* Netral (mayoritas UI) — pakai ini untuk 90% tampilan */
  --color-ink-900: #14161a;   /* teks utama */
  --color-ink-700: #3a3f47;   /* teks sekunder */
  --color-ink-500: #6b7280;   /* teks muted / label */
  --color-line:    #e5e7eb;   /* border */
  --color-canvas:  #f7f7f8;   /* latar halaman */
  --color-surface: #ffffff;   /* latar kartu/tabel */

  /* Aksen TUNGGAL — pilih SATU, ganti nilai di bawah.
     Rekomendasi: teal (tenang, tidak "AI"). Alternatif: slate-blue gelap, atau amber tua. */
  --color-accent-600: #0f766e;  /* teal-700-ish, untuk tombol utama */
  --color-accent-700: #115e59;  /* hover */
  --color-accent-50:  #f0fdfa;  /* latar lembut */

  /* Status — pakai SEMANTIK, jangan asal warna */
  --color-success: #15803d;
  --color-warning: #b45309;
  --color-danger:  #b91c1c;
}
```

**Aturan pemakaian warna:**
- Latar halaman & kartu = netral. **Tidak ada gradient.**
- Aksen hanya untuk: tombol utama, link aktif, focus ring, 1–2 highlight.
- Warna status (hijau/kuning/merah) **hanya** untuk makna status, bukan hiasan.
- Rasio kontras teks minimum **4.5:1** (WCAG AA). Cek sebelum commit.

### 3.2 Tipografi

Satu keluarga font saja. Skala terbatas:

| Peran | Class | Dipakai untuk |
|---|---|---|
| Judul halaman | `text-xl font-semibold` | Satu per halaman |
| Sub-judul / section | `text-base font-semibold` | Header kartu |
| Teks body | `text-sm` | Mayoritas isi |
| Label / meta | `text-xs text-ink-500` | Keterangan, header tabel |
| Angka uang | `text-sm font-medium tabular-nums` | Harga, total |

- **`tabular-nums`** wajib untuk kolom angka/uang agar sejajar rapi.
- Bold (`font-semibold`) hemat — jangan semua teks tebal.
- Ukuran judul halaman: **maksimum `text-2xl`**, dan hanya di dashboard utama. Di halaman tabel/CRUD: `text-xl`.

### 3.3 Radius, Spacing, Shadow

```css
@theme {
  --radius-sm: 4px;    /* badge, tag kecil */
  --radius-md: 8px;    /* tombol, input, kartu — DEFAULT */
  --radius-lg: 12px;   /* panel besar, modal */
  /* Tidak ada radius > 12px. Tidak ada rounded-full kecuali avatar/badge pil. */
}
```

- **Spacing pakai skala 4px**: `2, 3, 4, 6, 8` (Tailwind). Padding kartu: `p-4` atau `p-5` — bukan `p-8`.
- **Shadow**: `shadow-sm` maksimum. Kebanyakan elemen: `border` saja, tanpa shadow.
- Gap antar elemen: `gap-4` / `gap-6` konsisten.

### 3.4 Motion
- Transisi hanya untuk `color`, `background-color`, `opacity` — durasi `150ms`.
- **Tidak ada** animasi masuk (fade-in, slide-up) di elemen list/tabel.
- Hover: perubahan warna lembut, **bukan** perubahan ukuran/posisi.

---

## 4. Pola per Layar

### 4.1 Dashboard (admin)
- **Bukan** deretan 4 kartu raksasa. Gunakan **strip ringkasan padat** di atas (angka + label kecil dalam satu baris).
- Grafik: tinggi terkendali (`h-64`), tanpa gridline norak, tanpa animasi.
- Angka penting: besar & tegas. Label: kecil & muted.
- **Jangan** ada emoji atau ilustrasi dekoratif.

### 4.2 Halaman Tabel (CRUD)
- Header tabel: `text-xs uppercase tracking-wide text-ink-500` (ini satu-satunya tempat uppercase diizinkan).
- Baris: `h-12`ish, padding konsisten, border bawah tipis.
- Aksi baris (edit/hapus): ikon kecil atau teks link — **jangan** tombol besar berwarna di tiap baris.
- Pagination: sederhana, tanpa shadow.
- Empty state: satu baris teks netral + satu tombol aksi. Bukan ilustrasi besar.
- Filter/pencarian: satu baris di atas tabel, rapi sejajar.

### 4.3 Form
- Label di atas input. Wajib.
- Error: teks merah kecil di bawah field + border merah. **Jangan** alert besar untuk error per-field.
- Lebar form terkendali (`max-w-xl`), jangan melebar penuh di layar besar.
- Tombol submit: satu tombol utama + satu tombol batal (secondary). Tidak lebih.
- Bintang wajib (`*`) konsisten untuk field required.

### 4.4 Halaman POS (kasir) — paling kritis
- **Prioritas: kecepatan.** Grid produk rapat, target klik besar, minim dekorasi.
- Panel keranjang: tetap (sticky) di kanan, lebar tetap.
- Angka total: jelas, `tabular-nums`, ukuran menonjol.
- Pencarian produk fokus otomatis.
- Warna: netral, dengan aksen hanya di tombol "Bayar".
- **Tidak ada** animasi saat menambah item — cukup item muncul.

### 4.5 Struk (print)
- Font monospace untuk perataan kolom.
- Lebar 58mm/80mm, CSS `@media print` menyembunyikan nav/tombol.
- Hitam-putih saja. Tanpa warna latar.

### 4.6 Login / Register
- Satu-satunya halaman yang boleh "centered" & sedikit lebih lapang.
- Tetap netral. **Tidak ada** gradient background. **Tidak ada** ilustrasi AI.
- Card sederhana, `max-w-sm`, border tipis.

---

## 5. Komponen (sumber kebenaran di `app.css`)

Komponen reusable **wajib** didefinisikan di `@layer components` `app.css`, lalu dipakai lewat class
pendek (`.btn`, `.card`, `.input`). **Jangan** menulis 20 class Tailwind inline di tiap Blade.

> `app.css` saat ini sudah punya sistem komponen yang baik (`.btn`, `.card`, `.table-base`,
> `.badge-*`). **Pertahankan strukturnya**, tapi sesuaikan token warna (§3.1) dan radius (§3.3).

Aturan komponen:
- Satu tombol utama per layar. Sisanya secondary/ghost.
- Badge: pil kecil, warna status. Bukan label besar.
- Kartu: border tipis + `shadow-sm` (opsional). Bukan `shadow-xl`.

---

## 6. Aksesibilitas (bukan opsional)

- Kontras teks ≥ 4.5:1.
- Semua input punya `<label>` yang benar-benar terhubung (`for`/`id`).
- Focus state jelas & terlihat (focus ring aksen).
- Ikon-only button wajib punya `aria-label`.
- Jangan mengandalkan warna saja untuk menyampaikan status (tambah teks/ikon).

---

## 7. Checklist Sebelum Commit UI

Centang semua. Kalau ada yang ❌, **perbaiki dulu.**

- [ ] Tidak ada gradient ungu/indigo/violet di mana pun.
- [ ] Tidak ada emoji sebagai ikon UI.
- [ ] Radius semua elemen dari skala (§3.3), tidak seragam maksimal.
- [ ] Shadow maksimum `shadow-sm`; kebanyakan elemen cuma `border`.
- [ ] Judul halaman ≤ `text-2xl`; satu judul per halaman.
- [ ] Warna aksen dipakai hemat (≤ 1 aksen + warna status).
- [ ] Kolom angka/uang pakai `tabular-nums`.
- [ ] Spacing konsisten (skala 4px), tidak ada `p-8`/`gap-10` sembarangan.
- [ ] Kontras teks lolos WCAG AA.
- [ ] Tidak ada teks marketing/placeholder di UI internal.
- [ ] Tombol utama per layar cuma satu.
- [ ] Tabel padat & rapi, aksi baris tidak berupa tombol besar berwarna.

---

## 8. Cara Mengukur "Sudah Tidak Slop"

Tiga tes cepat:

1. **Tes screenshot:** Ambil screenshot. Kalau warnanya "ungu-biru mengkilap", gagal.
2. **Tes desaturasi:** Buat grayscale. Kalau hierarki tetap terbaca (judul menonjol, aksi jelas),
   berarti desainnya bergantung pada struktur — bagus. Kalau jadi rata & tak jelas, berarti Anda
   terlalu bergantung pada warna → perbaiki hierarki.
3. **Tes "alat kerja":** Bayangkan dipakai 8 jam. Ada yang melelahkan mata? Terlalu ramai? Terlalu
   banyak klik? Perbaiki.

---

## 9. Yang WAJIB Dilakukan Agent Sebelum Menulis UI

1. Baca `docs/DESIGN.md` ini **dan** `AGENTS.md`.
2. Cek komponen yang sudah ada di `resources/css/app.css` — **pakai ulang**, jangan bikin baru.
3. Sebelum membuat halaman baru, lihat 1–2 halaman yang sudah ada agar pola konsisten.
4. Terapkan checklist §7 sebelum menyatakan tugas selesai.
5. Kalau butuh warna/ukuran baru, **tambahkan sebagai token** di `@theme` — jangan hardcode.

> **Kalau ragu antara "lebih menarik" dan "lebih sesuai aturan": pilih sesuai aturan.**
> Slop lahir dari keinginan menambah dekorasi yang tidak diminta.
