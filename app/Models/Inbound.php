<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inbound extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'warehouse_id', 'reference_no', 'supplier_name', 'notes',
        'status', 'received_at', 'received_by', 'created_by',
    ];

    protected $casts = ['received_at' => 'datetime'];

    public function warehouse(): BelongsTo  { return $this->belongsTo(Warehouse::class); }
    public function creator(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }
    public function receiver(): BelongsTo   { return $this->belongsTo(User::class, 'received_by'); }
    public function items(): HasMany        { return $this->hasMany(InboundItem::class); }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isReceived(): bool { return $this->status === 'received'; }

    public function totalQty(): int
    {
        return $this->items->sum('qty');
    }
}
