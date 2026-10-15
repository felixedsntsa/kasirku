<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

/**
 * Dilempar saat stok produk tidak mencukupi untuk dikurangi.
 * Transaksi pembatalan; perubahan stok tidak jadi diterapkan.
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly Product $product,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(sprintf(
            'Stok "%s" tidak mencukupi: diminta %d, tersedia %d.',
            $this->product->name,
            $this->requested,
            $this->available,
        ));
    }
}
