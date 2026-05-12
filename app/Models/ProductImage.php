<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'color_id', 'path', 'is_primary', 'sort_order'];

    protected $casts = ['is_primary' => 'boolean'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function color(): BelongsTo   { return $this->belongsTo(Color::class); }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
