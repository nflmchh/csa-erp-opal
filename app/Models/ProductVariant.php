<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'color_id', 'size_id', 'product_image_id',
        'sku', 'price_adjustment', 'is_active',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function color(): BelongsTo   { return $this->belongsTo(Color::class); }
    public function size(): BelongsTo    { return $this->belongsTo(Size::class); }
    public function image(): BelongsTo   { return $this->belongsTo(ProductImage::class, 'product_image_id'); }
    public function stocks(): HasMany    { return $this->hasMany(Stock::class); }
    public function ledgers(): HasMany   { return $this->hasMany(StockLedger::class); }

    public function sellPrice(): float
    {
        return (float) $this->product->sell_price + (float) $this->price_adjustment;
    }

    public function retailPrice(): float
    {
        return (float) ($this->product->retail_price ?? $this->product->sell_price) + (float) $this->price_adjustment;
    }

    public function totalStock(): int
    {
        return $this->stocks->sum('qty');
    }

    public function stockAt(string $locationType, int $locationId): int
    {
        return $this->stocks
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->sum('qty');
    }
}
