<?php

namespace App\Livewire\Pages\Tools;

use App\Jobs\AnalyseSetJob;
use App\Models\AnalyzedSet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;

class SetAnalyzer extends Component
{
    use \Livewire\WithPagination;

    public string $setNumber = '';

    public float $price = 0.0;

    public $sortBy = 'date';
    public $sortDirection = 'desc';

    public function render()
    {
        return view('livewire.pages.tools.set-analyzer');
    }

    public function save(): void
    {
        $this->validate([
            'setNumber' => 'required|string',
            'price' => 'required|numeric',
        ]);

        $set = AnalyzedSet::create([
            'set_number' => trim($this->setNumber),
            'price' => trim($this->price),
        ]);

        AnalyseSetJob::dispatch($set);
    }

    #[\Livewire\Attributes\Computed]
    public function sets(): LengthAwarePaginator
    {
        return AnalyzedSet::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(50);
    }

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }
}
