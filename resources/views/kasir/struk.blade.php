{{--
    Struk penjualan — layout lebar 80mm, ramah printer thermal.
    Tombol cetak memanggil window.print(); CSS @media print menyembunyikan chrome UI.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $sale->invoice_no }} — KasirKu</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #f1f5f9; }

        /* Kertas struk 80mm */
        .receipt {
            width: 80mm;
            margin: 24px auto;
            background: #fff;
            padding: 6mm;
            font-family: ui-monospace, "Cascadia Code", Menlo, monospace;
            font-size: 12px;
            line-height: 1.5;
            color: #000;
            box-shadow: 0 1px 3px rgba(0,0,0,.12);
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: 700; }
        .muted { color: #555; font-size: 11px; }
        .dashed { border-top: 1px dashed #999; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .item-name { font-weight: 600; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .receipt {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            @page { size: 80mm auto; margin: 4mm; }
        }
    </style>
</head>
<body>
    {{-- Toolbar (tidak ikut tercetak) --}}
    <div class="no-print mx-auto flex max-w-xs flex-col items-center gap-3 py-6">
        <button onclick="window.print()" class="btn btn-primary w-full">
            <x-icon name="printer" class="size-5" />
            Cetak Struk
        </button>
        <div class="flex w-full gap-3">
            <a href="{{ route('kasir.home') }}" class="btn btn-secondary flex-1">Transaksi Baru</a>
            <a href="{{ route('kasir.riwayat') }}" class="btn btn-ghost flex-1">Riwayat</a>
        </div>
    </div>

    <div class="receipt">
        {{-- Kepala struk --}}
        <div class="center">
            <p class="bold" style="font-size: 15px;">KasirKu</p>
            <p class="muted">POS &amp; Inventaris</p>
        </div>

        <div class="dashed"></div>

        <div class="row"><span>No</span><span>{{ $sale->invoice_no }}</span></div>
        <div class="row"><span>Tanggal</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
        <div class="row"><span>Kasir</span><span>{{ $sale->user->name ?? '-' }}</span></div>
        @if ($sale->customer_name)
            <div class="row"><span>Pelanggan</span><span>{{ $sale->customer_name }}</span></div>
        @endif

        <div class="dashed"></div>

        {{-- Item --}}
        @foreach ($sale->items as $item)
            <div class="item-name">{{ $item->product->name ?? '(dihapus)' }}</div>
            <div class="row muted">
                <span>{{ $item->qty }} {{ $item->product->unit ?? '' }} × {{ number_format($item->price, 0, ',', '.') }}</span>
                <span class="bold" style="color:#000;">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
        @endforeach

        <div class="dashed"></div>

        {{-- Ringkasan --}}
        <div class="row"><span>Subtotal</span><span>{{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
        @if ((float) $sale->discount > 0)
            <div class="row"><span>Diskon</span><span>-{{ number_format($sale->discount, 0, ',', '.') }}</span></div>
        @endif
        @if ((float) $sale->tax > 0)
            <div class="row"><span>Pajak</span><span>{{ number_format($sale->tax, 0, ',', '.') }}</span></div>
        @endif
        <div class="row bold" style="font-size: 14px;"><span>TOTAL</span><span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span></div>
        <div class="row"><span>{{ $sale->paymentMethodLabel() }}</span><span>{{ number_format($sale->paid, 0, ',', '.') }}</span></div>
        <div class="row"><span>Kembalian</span><span>{{ number_format($sale->change, 0, ',', '.') }}</span></div>

        <div class="dashed"></div>

        <p class="center muted">Terima kasih sudah berbelanja.<br>Barang yang sudah dibeli tidak dapat ditukar.</p>
    </div>
</body>
</html>
