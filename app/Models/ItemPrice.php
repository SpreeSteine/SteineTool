<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPrice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price_detail' => 'array',
        'last_synced_at' => 'datetime',
    ];
}
