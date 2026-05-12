<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreReturnItem extends Model
{
    protected $fillable = [
        'store_return_id', 'product_variant_id',
        'qty_returned', 'qty_good', 'qty_damaged', 'item_notes',
    ];

    public function storeReturn(): BelongsTo { return $this->belongsTo(StoreReturn::class); }
    public function variant(): BelongsTo     { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
