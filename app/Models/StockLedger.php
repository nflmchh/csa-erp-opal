<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockLedger extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_variant_id', 'location_type', 'location_id',
        'type', 'qty', 'qty_before', 'qty_after',
        'reference_type', 'reference_id', 'note', 'created_by',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function variant(): BelongsTo  { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
    public function location(): MorphTo   { return $this->morphTo(); }
    public function creator(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
    public function reference(): MorphTo  { return $this->morphTo('reference'); }
}
