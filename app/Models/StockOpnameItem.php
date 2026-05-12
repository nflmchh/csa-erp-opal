<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id', 'product_variant_id',
        'qty_system', 'qty_actual', 'qty_difference',
    ];

    public function opname(): BelongsTo  { return $this->belongsTo(StockOpname::class, 'stock_opname_id'); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
