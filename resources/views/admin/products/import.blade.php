@extends('layouts.admin')

@section('title', 'Import CSV Produk — KasirKu')
@section('heading', 'Import CSV Produk')

@section('content')
    <div class="mx-auto max-w-2xl">
        <p class="page-subtitle mb-6">
            Unggah data produk massal dari file CSV. Baris valid langsung diimpor; baris bermasalah dilaporkan per nomor baris.
        </p>

        <div class="card">
            <div class="card-body">
                {{-- Format kolom --}}
                <div class="mb-6 rounded-md border border-line bg-canvas px-4 py-3 text-sm">
                    <p class="font-semibold text-slate-900">Format kolom (urut)</p>
                    <p class="mt-1 text-ink-500">
                        SKU; Nama; Kategori; Supplier; Barcode; Satuan; Harga Beli; Harga Jual; Stok; Stok Minimum; Status
                    </p>
                    <ul class="mt-2 list-disc space-y-0.5 pl-5 text-xs text-ink-500">
                        <li><strong>SKU</strong>, <strong>Nama</strong> &amp; <strong>Kategori</strong> wajib; SKU tidak boleh sudah ada di database.</li>
                        <li>Kategori/supplier yang belum terdaftar dibuat otomatis dari nama di CSV.</li>
                        <li>Angka polos tanpa pemisah (mis. <code class="font-mono">15000</code>); <strong>Status</strong> = <code class="font-mono">aktif</code>/<code class="font-mono">nonaktif</code> (kosong = aktif).</li>
                        <li>Pemisah kolom <code class="font-mono">;</code> (template) atau <code class="font-mono">,</code> — keduanya dikenali.</li>
                        <li>Stok awal tercatat sebagai stok masuk pertama (riwayat stok tetap utuh).</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('admin.produk.import.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label for="file" class="label">File CSV <span class="text-danger">*</span></label>
                        <input id="file" type="file" name="file" accept=".csv,.txt" required
                               class="input @error('file') input-error @enderror" />
                        <p class="mt-1.5 text-xs text-ink-500">Maks 2 MB. Belum punya file? Unduh dulu lewat tombol "Export CSV" di halaman produk.</p>
                        @error('file') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 border-t border-line pt-4">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="arrow-up" class="size-4" />
                            Import Sekarang
                        </button>
                        <a href="{{ route('admin.produk.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
