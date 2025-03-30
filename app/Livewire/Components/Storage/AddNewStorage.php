<?php

namespace App\Livewire\Components\Storage;

use App\Models\Storage;
use App\Models\StorageUnit;
use Flux\Flux;
use Livewire\Component;

class AddNewStorage extends Component
{
    public string $identifier = '';

    public array $units = [];

    public function render()
    {
        return view('livewire.components.storage.add-new-storage');
    }

    public function addUnit()
    {
        // number format leading zero 01, 02, 03, ...
        $number = sprintf("%02d", count($this->units) + 1);

        $beforeUnit = $this->units[count($this->units) - 1] ?? null;

        $this->units[] = [
            'identifier' => $this->identifier . '-' . $number,
            'length' => $beforeUnit ? $beforeUnit['length'] : 0,
            'width' => $beforeUnit ? $beforeUnit['width'] : 0,
            'height' => $beforeUnit ? $beforeUnit['height'] : 0,
        ];
    }

    public function removeUnit($index): void
    {
        unset($this->units[$index]);

        $this->units = array_values($this->units);
    }

    public function save()
    {
        // Save the storage
        $data = $this->validate([
            'identifier' => 'required',
        ]);

        $storage = Storage::create($data);

        // create storage units
        foreach ($this->units as $i => $unit) {
            StorageUnit::create([
                'storage_id' => $storage->id,
                'identifier' => $unit['identifier'],
                'length' => $unit['length'],
                'width' => $unit['width'],
                'height' => $unit['height'],
            ]);
        }


        Flux::modals()->close();
    }
}
