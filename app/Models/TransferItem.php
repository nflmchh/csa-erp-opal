<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferItem extends Model
{
    protected $fillable = [
        'transfer_id', 'product_variant_id',
        'qty_requested', 'qty_sent', 'qty_received',
    ];

    public function transfer(): BelongsTo { return $this->belongsTo(Transfer::class); }
    public function variant(): BelongsTo  { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
