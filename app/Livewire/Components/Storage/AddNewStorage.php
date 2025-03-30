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

    public function rules(): array
    {
        return [
            'identifier' => 'required|unique:storages,identifier|max:255',
            'units.*.identifier' => 'required|max:255',
            'units.*.length' => 'required|numeric|min:0',
            'units.*.width' => 'required|numeric|min:0',
            'units.*.height' => 'required|numeric|min:0',
        ];
    }

    public function render()
    {
        return view('livewire.components.storage.add-new-storage');
    }

    public function addUnit()
    {
        $number = sprintf("%02d", count($this->units) + 1);
        $beforeUnit = end($this->units) ?: null;

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
        $this->validate();

        $storage = Storage::create([
            'identifier' => $this->identifier,
        ]);

        foreach ($this->units as $unit) {
            StorageUnit::create([
                'storage_id' => $storage->id,
                'identifier' => $unit['identifier'],
                'length' => $unit['length'],
                'width' => $unit['width'],
                'height' => $unit['height'],
            ]);
        }

        $this->dispatch('refreshStorageList');
        Flux::modals()->close();
    }
}
