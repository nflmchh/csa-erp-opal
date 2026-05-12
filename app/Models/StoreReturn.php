<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreReturn extends Model
{
    use SoftDeletes;

    const STATUS_LABELS = ['pending' => 'Menunggu', 'received' => 'Diterima', 'inspected' => 'Diinspeksi'];
    const STATUS_COLORS = [
        'pending'  => 'bg-yellow-100 text-yellow-700',
        'received' => 'bg-blue-100 text-blue-700',
        'inspected'=> 'bg-green-100 text-green-700',
    ];

    protected $fillable = [
        'return_no', 'store_id', 'warehouse_id', 'return_reason_id',
        'status', 'notes', 'inspection_notes',
        'received_at', 'received_by', 'inspected_at', 'inspected_by', 'created_by',
    ];

    protected $casts = [
        'received_at'  => 'datetime',
        'inspected_at' => 'datetime',
    ];

    public function store(): BelongsTo     { return $this->belongsTo(Store::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function reason(): BelongsTo    { return $this->belongsTo(ReturnReason::class, 'return_reason_id'); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function receiver(): BelongsTo  { return $this->belongsTo(User::class, 'received_by'); }
    public function inspector(): BelongsTo { return $this->belongsTo(User::class, 'inspected_by'); }
    public function items(): HasMany       { return $this->hasMany(StoreReturnItem::class); }

    public function isPending():   bool { return $this->status === 'pending'; }
    public function isReceived():  bool { return $this->status === 'received'; }
    public function isInspected(): bool { return $this->status === 'inspected'; }

    public function statusLabel(): string { return self::STATUS_LABELS[$this->status] ?? $this->status; }
    public function statusColor(): string { return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-600'; }
    public function totalQty():    int    { return $this->items->sum('qty_returned'); }
}
