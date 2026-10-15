@extends('layouts.admin')

@section('title', 'Kasir (POS) — KasirKu')
@section('heading', 'Kasir (POS)')

@section('content')
    {{--
        POS Alpine.js — state keranjang di client, checkout via POST biasa (bukan fetch),
        supaya redirect ke halaman struk tetap natural.
        Harga & stok selalu divalidasi ulang di server (SaleService).
    --}}
    <div x-data="pos()" class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Kiri: cari + daftar produk --}}
        <div class="xl:col-span-2 space-y-4">
            <div class="card p-4">
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-ink-500" />
                    <input type="text" x-model="query" x-ref="search" @input.debounce.250ms="fetchProducts()"
                           placeholder="Cari nama produk, SKU, atau barcode…"
                           class="input pl-11 text-base" autocomplete="off" />
                </div>

                <div class="mt-3 flex flex-wrap gap-2" x-show="recent.length">
                    <template x-for="p in recent" :key="'r'+p.id">
                        <button type="button" @click="add(p)"
                                class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:border-accent-600/30 hover:bg-accent-50 hover:text-accent-700"
                                x-text="p.name"></button>
                    </template>
                </div>
            </div>

            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">Daftar Produk</h2>
                    <span class="text-xs text-ink-500" x-text="products.length + ' produk ditemukan'"></span>
                </div>

                <div class="max-h-[540px] overflow-y-auto divide-y divide-slate-100">
                    <template x-if="loading">
                        <div class="empty-state">Memuat produk…</div>
                    </template>

                    <template x-if="!loading && products.length === 0">
                        <div class="empty-state">Tidak ada produk yang cocok.</div>
                    </template>

                    <template x-for="p in products" :key="p.id">
                        <button type="button" @click="add(p)" :disabled="p.stock < 1"
                                class="flex w-full items-center gap-4 px-5 py-3 text-left transition-colors hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-md bg-accent-50 text-accent-600">
                                <x-icon name="box" class="size-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900" x-text="p.name"></p>
                                <p class="truncate font-mono text-xs text-ink-500">
                                    <span x-text="p.sku"></span> · stok <span x-text="p.stock"></span> <span x-text="p.unit"></span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold tabular-nums text-slate-900" x-text="rp(p.sell_price)"></p>
                                <p class="text-[11px] font-medium" :class="p.stock <= 3 ? 'text-rose-600' : 'text-ink-500'"
                                   x-text="p.stock <= 3 ? 'stok menipis' : 'tersedia'"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Kanan: keranjang + pembayaran --}}
        <div class="space-y-4">
            <form method="POST" action="{{ route('kasir.checkout') }}" class="card flex flex-col"
                  @submit="submitting = true">
                @csrf
                <div class="card-header flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">Keranjang</h2>
                    <button type="button" x-show="cart.length" @click="clear()"
                            class="text-xs font-medium text-rose-600 hover:text-rose-700">
                        Kosongkan
                    </button>
                </div>

                {{-- Item keranjang --}}
                <div class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                    <template x-if="cart.length === 0">
                        <div class="empty-state">Keranjang kosong.<br>Klik produk untuk menambahkan.</div>
                    </template>

                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="px-4 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-medium text-slate-900" x-text="item.name"></p>
                                <button type="button" @click="remove(index)" class="text-slate-500 hover:text-rose-600">
                                    <x-icon name="trash" class="size-4" />
                                </button>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center rounded-md border border-slate-200">
                                    <button type="button" @click="dec(index)" class="px-2.5 py-1 text-slate-500 hover:text-accent-600">−</button>
                                    <input type="number" :name="`items[${index}][product_id]`" :value="item.id" hidden />
                                    <input type="number" :name="`items[${index}][qty]`" :value="item.qty" readonly
                                           class="w-12 border-x border-slate-200 bg-transparent text-center text-sm font-semibold tabular-nums" />
                                    <button type="button" @click="inc(index)" class="px-2.5 py-1 text-slate-500 hover:text-accent-600">+</button>
                                </div>
                                <p class="text-sm font-bold tabular-nums text-slate-900" x-text="rp(item.qty * item.sell_price)"></p>
                            </div>
                            <p x-show="item.qty >= item.stock" class="mt-1 text-[11px] font-medium text-amber-700">
                                mencapai batas stok (<span x-text="item.stock"></span>)
                            </p>
                        </div>
                    </template>
                </div>

                {{-- Ringkasan & pembayaran --}}
                <div class="space-y-3 border-t border-slate-100 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-semibold tabular-nums" x-text="rp(subtotal)"></span>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-500">Diskon (Rp)</label>
                        <input type="number" name="discount" x-model.number="discount" min="0" step="100" placeholder="0"
                               class="input mt-1 py-2 text-sm" />
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-500">Metode Bayar</label>
                        <div class="mt-1 grid grid-cols-3 gap-2">
                            <template x-for="m in methods" :key="m.value">
                                <button type="button" @click="method = m.value"
                                        :class="method === m.value
                                            ? 'border-accent-600 bg-accent-50 text-accent-700 ring-1 ring-accent-600'
                                            : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                                        class="rounded-md border px-2 py-2 text-xs font-semibold transition"
                                        x-text="m.label"></button>
                            </template>
                        </div>
                        <input type="hidden" name="payment_method" :value="method" />
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-500">Uang Diterima (Rp)</label>
                        <input type="number" name="paid" x-model.number="paid" min="0" step="100" required
                               placeholder="0" class="input mt-1 py-2 text-sm" />
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="paid = Math.ceil(total)" class="btn btn-secondary btn-sm">Uang Pas</button>
                        <template x-for="d in quickCash" :key="d">
                            <button type="button" @click="paid = d" class="btn btn-secondary btn-sm" x-text="rp(d)"></button>
                        </template>
                    </div>

                    <div class="flex items-center justify-between rounded-md bg-slate-900 px-4 py-3 text-white">
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-400">Total</span>
                        <span class="text-xl font-bold tabular-nums" x-text="rp(total)"></span>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Kembalian</span>
                        <span class="font-bold tabular-nums" :class="change < 0 ? 'text-rose-700' : 'text-emerald-700'" x-text="rp(change)"></span>
                    </div>

                    <button type="submit" :disabled="cart.length === 0 || submitting || change < 0"
                            class="btn btn-primary w-full py-3 text-base">
                        <x-icon name="check" class="size-5" />
                        <span x-text="submitting ? 'Memproses…' : 'Bayar & Cetak Struk'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function pos() {
            return {
                query: '',
                products: [],
                recent: [],
                cart: [],
                loading: false,
                submitting: false,
                discount: 0,
                paid: null,
                method: 'cash',
                methods: [
                    { value: 'cash', label: 'Tunai' },
                    { value: 'qris', label: 'QRIS' },
                    { value: 'debit', label: 'Debit' },
                ],

                get subtotal() {
                    return this.cart.reduce((sum, i) => sum + i.sell_price * i.qty, 0);
                },
                get total() {
                    return Math.max(0, this.subtotal - (this.discount || 0));
                },
                get change() {
                    return (this.paid || 0) - this.total;
                },
                get quickCash() {
                    // Nominal cepat: pembulatan 10rb di atas total, maks 3 opsi.
                    const base = Math.ceil(this.total / 10000) * 10000;
                    return [base, base + 10000, base + 50000]
                        .filter((v, i, arr) => v >= this.total && arr.indexOf(v) === i)
                        .slice(0, 3);
                },

                rp(n) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
                },

                async fetchProducts() {
                    this.loading = true;
                    try {
                        const res = await fetch('{{ route('kasir.produk.search') }}?q=' + encodeURIComponent(this.query));
                        this.products = await res.json();
                    } finally {
                        this.loading = false;
                    }
                },

                add(p) {
                    if (p.stock < 1) return;
                    const existing = this.cart.find(i => i.id === p.id);
                    if (existing) {
                        if (existing.qty < p.stock) existing.qty++;
                    } else {
                        this.cart.push({ ...p, qty: 1 });
                    }
                    // Simpan sebagai "pencarian terakhir" (recents ringkas).
                    this.recent = [p, ...this.recent.filter(r => r.id !== p.id)].slice(0, 5);
                },

                inc(index) {
                    const item = this.cart[index];
                    if (item.qty < item.stock) item.qty++;
                },
                dec(index) {
                    const item = this.cart[index];
                    if (item.qty > 1) item.qty--; else this.remove(index);
                },
                remove(index) {
                    this.cart.splice(index, 1);
                },
                clear() {
                    this.cart = [];
                    this.discount = 0;
                    this.paid = null;
                },

                init() {
                    this.fetchProducts();
                    this.$el.addEventListener('submit', (e) => {
                        if (this.cart.length === 0) {
                            e.preventDefault();
                            this.submitting = false;
                            return;
                        }
                        if (this.discount > this.subtotal) {
                            e.preventDefault();
                            alert('Diskon tidak boleh melebihi subtotal.');
                            this.submitting = false;
                        }
                    });
                    this.$refs.search.focus();
                },
            };
        }
    </script>
@endsection
