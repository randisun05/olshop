<?php

namespace App\Models;

use App\Enums\StockAdjustmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_variant_id',
        'user_id',
        'type',
        'quantity_change',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockAdjustmentType::class,
            'created_at' => 'datetime',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(ProductVariant $variant, StockAdjustmentType $type, int $quantityChange, ?string $note = null, ?User $user = null): void
    {
        static::create([
            'product_variant_id' => $variant->id,
            'user_id' => $user?->id,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'note' => $note,
            'created_at' => now(),
        ]);
    }
}
