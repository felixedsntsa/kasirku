<?php

namespace App\Models;

use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'type', 'qty', 'reference_type', 'reference_id', 'note', 'user_id'])]
class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use HasFactory;

    public const string TYPE_IN = 'in';

    public const string TYPE_OUT = 'out';

    public const string TYPE_ADJUST = 'adjust';

    /** Riwayat stok bersifat append-only — updated_at tidak diperlukan (lihat ERD). */
    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi polymorphic ringkas untuk sumber referensi (mis. Sale).
     */
    public function reference(): BelongsTo
    {
        return $this->morphTo();
    }

    /**
     * Label tipe untuk ditampilkan di UI.
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_IN => 'Masuk',
            self::TYPE_OUT => 'Keluar',
            self::TYPE_ADJUST => 'Opname',
            default => $this->type,
        };
    }
}
