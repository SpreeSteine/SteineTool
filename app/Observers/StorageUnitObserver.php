<?php

namespace App\Observers;

use App\Models\Inventory;
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
        $inventoryBuilder = Inventory::query()
            ->where('remarks', $model->identifier);

        $model->number_of_items = $inventoryBuilder
            ->sum('quantity');

        $volumeOfItems = 0;

        $inventoryBuilder->each(function (Inventory $inventory) use (&$volumeOfItems) {

            // get the item
            $item = $inventory->item;

            // calculate volume of the item
            $itemVolume = $item->package_dim_x
                * $item->package_dim_y
                * $item->package_dim_z;

            // calculate the used capacity
            $volumeOfItems += $itemVolume * $inventory->quantity;
        });

        // calculate the available capacity
        $model->available_capacity = $model->capacity - $volumeOfItems;

        // calculate the used capacity
        $model->capacity_percentage = $model->capacity > 0
            ? ($volumeOfItems / $model->capacity) * 100
            : 0;
    }
}
