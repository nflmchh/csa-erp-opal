<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentItem extends Model
{
    protected $fillable = ['shipment_id', 'product_variant_id', 'qty_sent', 'qty_received'];

    public function shipment(): BelongsTo { return $this->belongsTo(Shipment::class); }
    public function variant(): BelongsTo  { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
