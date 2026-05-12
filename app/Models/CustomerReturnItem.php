<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReturnItem extends Model
{
    protected $fillable = [
        'customer_return_id', 'product_variant_id', 'qty', 'unit_price', 'subtotal', 'condition',
    ];

    protected $casts = ['unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function customerReturn(): BelongsTo { return $this->belongsTo(CustomerReturn::class); }
    public function variant(): BelongsTo        { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
