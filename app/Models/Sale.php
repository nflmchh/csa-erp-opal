<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'sale_no', 'cash_session_id', 'store_id', 'payment_method_id',
        'subtotal', 'discount_amount', 'total_amount',
        'amount_paid', 'change_amount', 'notes', 'created_by',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'amount_paid'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
    ];

    public function cashSession(): BelongsTo   { return $this->belongsTo(CashSession::class); }
    public function store(): BelongsTo         { return $this->belongsTo(Store::class); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class); }
    public function creator(): BelongsTo       { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany           { return $this->hasMany(SaleItem::class); }
}
