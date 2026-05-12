<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use SoftDeletes;

    const STATUSES = ['draft', 'prepared', 'packed', 'shipped', 'arrived', 'received'];

    const STATUS_LABELS = [
        'draft'    => 'Draft',
        'prepared' => 'Disiapkan',
        'packed'   => 'Dikemas',
        'shipped'  => 'Dikirim',
        'arrived'  => 'Tiba',
        'received' => 'Diterima',
    ];

    const STATUS_COLORS = [
        'draft'    => 'bg-gray-100 text-gray-600',
        'prepared' => 'bg-blue-100 text-blue-700',
        'packed'   => 'bg-indigo-100 text-indigo-700',
        'shipped'  => 'bg-orange-100 text-orange-700',
        'arrived'  => 'bg-yellow-100 text-yellow-700',
        'received' => 'bg-green-100 text-green-700',
    ];

    protected $fillable = [
        'shipment_no', 'warehouse_id', 'store_id', 'status', 'notes',
        'shipped_at', 'arrived_at', 'received_at',
        'shipped_by', 'received_by', 'created_by',
    ];

    protected $casts = [
        'shipped_at'  => 'datetime',
        'arrived_at'  => 'datetime',
        'received_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo  { return $this->belongsTo(Warehouse::class); }
    public function store(): BelongsTo      { return $this->belongsTo(Store::class); }
    public function creator(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }
    public function shipper(): BelongsTo    { return $this->belongsTo(User::class, 'shipped_by'); }
    public function receiver(): BelongsTo   { return $this->belongsTo(User::class, 'received_by'); }
    public function items(): HasMany        { return $this->hasMany(ShipmentItem::class); }

    public function statusLabel(): string { return self::STATUS_LABELS[$this->status] ?? $this->status; }
    public function statusColor(): string { return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-600'; }

    public function canTransitionTo(string $next): bool
    {
        $idx = array_search($this->status, self::STATUSES);
        $nxt = array_search($next, self::STATUSES);
        return $nxt === $idx + 1;
    }

    public function totalQtySent(): int     { return $this->items->sum('qty_sent'); }
    public function totalQtyReceived(): int { return $this->items->sum('qty_received'); }
}
