<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Storage extends Model
{
    protected $guarded = [];

    public function units(): HasMany
    {
        return $this->hasMany(StorageUnit::class);
    }
}
