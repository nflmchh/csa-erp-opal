<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    const STATUS_LABELS = [
        'draft'     => 'Draft',
        'submitted' => 'Disubmit',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
    ];
    const STATUS_COLORS = [
        'draft'     => 'bg-gray-100 text-gray-600',
        'submitted' => 'bg-yellow-100 text-yellow-700',
        'approved'  => 'bg-green-100 text-green-700',
        'rejected'  => 'bg-red-100 text-red-700',
    ];

    protected $fillable = [
        'opname_no', 'location_type', 'location_id', 'status', 'notes', 'rejection_reason',
        'submitted_at', 'submitted_by', 'approved_at', 'approved_by',
        'rejected_at', 'rejected_by', 'created_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
    ];

    public function creator(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function submitter(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function approver(): BelongsTo  { return $this->belongsTo(User::class, 'approved_by'); }
    public function rejecter(): BelongsTo  { return $this->belongsTo(User::class, 'rejected_by'); }
    public function items(): HasMany       { return $this->hasMany(StockOpnameItem::class); }

    public function location(): ?object
    {
        if ($this->location_type === 'warehouse') {
            return Warehouse::find($this->location_id);
        }
        if ($this->location_type === 'store') {
            return Store::find($this->location_id);
        }
        return null;
    }

    public function locationName(): string
    {
        return $this->location()?->name ?? '—';
    }

    public function statusLabel(): string { return self::STATUS_LABELS[$this->status] ?? $this->status; }
    public function statusColor(): string { return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-600'; }

    public function isDraft():     bool { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isApproved():  bool { return $this->status === 'approved'; }
    public function isRejected():  bool { return $this->status === 'rejected'; }
}
