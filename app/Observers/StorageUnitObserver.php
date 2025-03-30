<?php

namespace App\Observers;

use App\Livewire\Pages\Inventory;
use App\Models\StorageUnit;

class StorageUnitObserver
{
    public function creating(StorageUnit $model): void
    {
        $this->calculateCapacity($model);
    }

    public function updating(StorageUnit $model): void
    {
        $this->calculateCapacity($model);

    }

    private function calculateCapacity(StorageUnit $model): void
    {
        // calculate the storage capacity
        $model->capacity = $model->length * $model->width * $model->height;

        // get the items that are stored in this storage unit
        $numberOfItems = \App\Models\Inventory::query()
            ->where('remarks', $model->identifier)
            ->sum('quantity');

        $model->number_of_items = $numberOfItems;

        // capacity_percentage


        // calculate the available capacity
        $model->available_capacity = $model->capacity - $model->used_capacity;
    }
}
