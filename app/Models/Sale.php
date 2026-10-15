<?php

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_no',
    'user_id',
    'customer_name',
    'subtotal',
    'discount',
    'tax',
    'total',
    'paid',
    'change',
    'payment_method',
    'status',
])]
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    public const string STATUS_PAID = 'paid';

    public const string STATUS_VOID = 'void';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'change' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Item yang terjual (harga di-snapshot, bukan referensi hidup ke produk).
     *
     * @return HasMany<SaleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Label metode bayar untuk ditampilkan.
     */
    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'debit' => 'Kartu Debit',
            default => $this->payment_method,
        };
    }
}
