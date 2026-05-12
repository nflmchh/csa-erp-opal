<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundItem extends Model
{
    protected $fillable = ['inbound_id', 'product_variant_id', 'qty', 'unit_cost'];

    protected $casts = ['unit_cost' => 'decimal:2'];

    public function inbound(): BelongsTo  { return $this->belongsTo(Inbound::class); }
    public function variant(): BelongsTo  { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
