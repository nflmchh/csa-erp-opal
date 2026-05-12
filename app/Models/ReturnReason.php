<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnReason extends Model
{
    protected $fillable = ['name', 'code', 'type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
