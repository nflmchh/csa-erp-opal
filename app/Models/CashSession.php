<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    protected $fillable = [
        'store_id', 'user_id', 'status',
        'opening_amount', 'closing_amount', 'expected_amount',
        'notes', 'opened_at', 'closed_at',
    ];

    protected $casts = [
        'opened_at'      => 'datetime',
        'closed_at'      => 'datetime',
        'opening_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
        'expected_amount'=> 'decimal:2',
    ];

    public function store(): BelongsTo { return $this->belongsTo(Store::class); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
    public function sales(): HasMany   { return $this->hasMany(Sale::class); }

    public function isOpen(): bool   { return $this->status === 'open'; }
    public function isClosed(): bool { return $this->status === 'closed'; }

    public function totalSales(): float  { return (float) $this->sales->sum('total_amount'); }
    public function totalTransactions(): int { return $this->sales->count(); }
}
