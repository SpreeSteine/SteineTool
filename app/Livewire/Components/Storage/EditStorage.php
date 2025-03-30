<?php

namespace App\Livewire\Components\Storage;

use App\Models\Storage;
use App\Models\StorageUnit;
use Flux\Flux;
use Livewire\Component;

class EditStorage extends Component
{
    public Storage $storage;
    public string $identifier = '';
    public array $units = [];

    public function mount(Storage $storage)
    {
        $this->storage = $storage;
        $this->identifier = $storage->identifier;
        $this->units = $storage->units->map(function ($unit) {
            return [
                'id' => $unit->id,
                'identifier' => $unit->identifier,
                'length' => $unit->length,
                'width' => $unit->width,
                'height' => $unit->height,
                'capacity_percentage' => $unit->capacity > 0 ? ($unit->used_capacity / $unit->capacity * 100) : 0,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.components.storage.edit-storage');
    }

    public function addUnit()
    {
        $number = sprintf("%02d", count($this->units) + 1);
        $beforeUnit = $this->units[count($this->units) - 1] ?? null;

        $this->units[] = [
            'id' => null,
            'identifier' => $this->identifier . '-' . $number,
            'length' => $beforeUnit ? $beforeUnit['length'] : 0,
            'width' => $beforeUnit ? $beforeUnit['width'] : 0,
            'height' => $beforeUnit ? $beforeUnit['height'] : 0,
            'capacity_percentage' => 0,
        ];
    }

    public function removeUnit($index)
    {
        if (isset($this->units[$index]['id'])) {
            StorageUnit::find($this->units[$index]['id'])->delete();
        }
        unset($this->units[$index]);
        $this->units = array_values($this->units);
    }

    public function save()
    {
        $this->storage->update([
            'identifier' => $this->identifier,
        ]);

        foreach ($this->units as $unit) {
            if (isset($unit['id'])) {
                StorageUnit::find($unit['id'])->update([
                    'identifier' => $unit['identifier'],
                    'length' => $unit['length'],
                    'width' => $unit['width'],
                    'height' => $unit['height'],
                ]);
            } else {
                StorageUnit::create([
                    'storage_id' => $this->storage->id,
                    'identifier' => $unit['identifier'],
                    'length' => $unit['length'],
                    'width' => $unit['width'],
                    'height' => $unit['height'],
                ]);
            }
        }

        Flux::modals()->close();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Storage updated successfully.',
        ]);

        $this->dispatch('refreshStorageList');
    }

    public function getTrafficLightColor($percentage)
    {
        if ($percentage == 0) return 'green';
        if ($percentage > 90) return 'red';
        return 'yellow';
    }
}
