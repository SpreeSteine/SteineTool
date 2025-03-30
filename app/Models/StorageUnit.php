<?php

namespace App\Models;

use App\Observers\StorageUnitObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([StorageUnitObserver::class])]
class StorageUnit extends Model
{
    protected $guarded = [];

    public function storage(): BelongsTo
    {
        return $this->belongsTo(Storage::class);
    }
}
