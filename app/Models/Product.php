<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brand_id', 'category_id', 'product_type_id',
        'name', 'model_code', 'description',
        'base_price', 'sell_price', 'retail_price', 'is_active', 'created_by','reward_store',
        'reward_owner',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function brand(): BelongsTo      { return $this->belongsTo(Brand::class); }
    public function category(): BelongsTo   { return $this->belongsTo(Category::class); }
    public function productType(): BelongsTo { return $this->belongsTo(ProductType::class); }
    public function creator(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }
    public function variants(): HasMany     { return $this->hasMany(ProductVariant::class); }
    protected static function booted()
    {
        static::deleting(function ($product) {
            // Jika produk di-force delete, force delete juga variannya
            if ($product->isForceDeleting()) {
                $product->variants()->forceDelete();
            } else {
                // Jika produk di-soft delete, soft delete juga variannya
                $product->variants()->delete();
            }
        });
    }
    public function images(): HasMany       { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function stocks(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Stock::class, ProductVariant::class);
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    public function scopeActive($query)     { return $query->where('is_active', true); }
    public function scopeSearch($query, $term)
    {
        return $query->where(fn($q) =>
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('model_code', 'like', "%{$term}%")
        );
    }
}
