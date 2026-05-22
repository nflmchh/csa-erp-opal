<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'product_variant_id', 'qty', 'unit_price', 'subtotal','reward_store',
        'reward_owner', 'is_ecer',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'is_ecer'    => 'boolean',
    ];

    public function sale(): BelongsTo    { return $this->belongsTo(Sale::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed(); }
}
