<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Stock extends Model
{
    protected $fillable = ['product_variant_id', 'location_type', 'location_id', 'qty'];

    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
    public function location(): MorphTo  { return $this->morphTo(); }
}
