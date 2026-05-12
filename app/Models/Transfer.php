<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use SoftDeletes;

    const STATUSES = ['pending', 'approved', 'rejected', 'shipped', 'received'];

    const STATUS_LABELS = [
        'pending'  => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'shipped'  => 'Dikirim',
        'received' => 'Diterima',
    ];

    const STATUS_COLORS = [
        'pending'  => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-blue-100 text-blue-700',
        'rejected' => 'bg-red-100 text-red-700',
        'shipped'  => 'bg-orange-100 text-orange-700',
        'received' => 'bg-green-100 text-green-700',
    ];

    protected $fillable = [
        'transfer_no', 'from_store_id', 'to_store_id', 'status', 'notes',
        'rejection_reason',
        'approved_at', 'approved_by',
        'rejected_at', 'rejected_by',
        'shipped_at', 'shipped_by',
        'received_at', 'received_by',
        'created_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'shipped_at'  => 'datetime',
        'received_at' => 'datetime',
    ];

    public function fromStore(): BelongsTo { return $this->belongsTo(Store::class, 'from_store_id'); }
    public function toStore(): BelongsTo   { return $this->belongsTo(Store::class, 'to_store_id'); }
    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo  { return $this->belongsTo(User::class, 'approved_by'); }
    public function rejecter(): BelongsTo  { return $this->belongsTo(User::class, 'rejected_by'); }
    public function shipper(): BelongsTo   { return $this->belongsTo(User::class, 'shipped_by'); }
    public function receiver(): BelongsTo  { return $this->belongsTo(User::class, 'received_by'); }
    public function items(): HasMany       { return $this->hasMany(TransferItem::class); }

    public function statusLabel(): string { return self::STATUS_LABELS[$this->status] ?? $this->status; }
    public function statusColor(): string { return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-600'; }

    public function isPending():  bool { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isShipped():  bool { return $this->status === 'shipped'; }
    public function isReceived(): bool { return $this->status === 'received'; }

    public function totalQtyRequested(): int { return $this->items->sum('qty_requested'); }
    public function totalQtySent():      int { return $this->items->sum('qty_sent'); }
    public function totalQtyReceived():  int { return $this->items->sum('qty_received'); }
}
