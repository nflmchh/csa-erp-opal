<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'title', 'description', 'expense_type', 'amount', 'receipt_path', 'expense_date', 
        'store_id', 'warehouse_id', 'created_by'
    ];

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}