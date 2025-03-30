<?php

namespace App\Livewire\Pages;

use App\Models\Storage;
use Livewire\Component;

class Storages extends Component
{
    public $sortBy = 'id';
    public $sortDirection = 'desc';

    public $listeners = [
        'refreshStorageList' => '$refresh',
    ];

    #[\Livewire\Attributes\Computed]
    public function storages()
    {
        return Storage::query()
            ->with('units') // Eager load the units relationship
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(50);
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        return view('livewire.pages.storages');
    }
}
