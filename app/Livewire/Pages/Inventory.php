<?php

namespace App\Livewire\Pages;

use App\Services\BrickLink\BrickLinkService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Inventory extends Component
{
    use \Livewire\WithPagination;

    private BrickLinkService $brickLinkService;

    public $sortBy = 'date';

    public $sortDirection = 'desc';

    public function boot(BrickLinkService $brickLinkService)
    {
        $this->brickLinkService = $brickLinkService;
    }

    public function render()
    {
        return view('livewire.pages.inventory');
    }

    #[\Livewire\Attributes\Computed]
    public function inventory(): LengthAwarePaginator
    {
        return \App\Models\Inventory::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(50);
    }

    public function syncInventory(): void
    {
        $this->brickLinkService->getInventory()
            ->each(fn ($inventory) => $this->updateInventory($inventory));
    }

    private function updateInventory($inventory): void
    {
        $idx = $inventory['item']['no'] . '-' . $inventory['color_id'] . '-' . $inventory['new_or_used'];

        // Sync inventory with local database
        \App\Models\Inventory::updateOrCreate(['id' => $idx], [
            'inventory_id' => $inventory['inventory_id'],
            'item_no' => $inventory['item']['no'],
            'item_name' => $inventory['item']['name'],
            'item_type' => $inventory['item']['type'],
            'item_category_id' => $inventory['item']['category_id'],
            'color_id' => $inventory['color_id'],
            'color_name' => $inventory['color_name'],
            'quantity' => $inventory['quantity'],
            'new_or_used' => $inventory['new_or_used'],
            'unit_price' => $inventory['unit_price'],
            'description' => $inventory['description'],
            'remarks' => $inventory['remarks'],
            'bulk' => $inventory['bulk'],
            'is_retain' => $inventory['is_retain'],
            'is_stock_room' => $inventory['is_stock_room'],
            'date_created' => $inventory['date_created'],
            'my_cost' => $inventory['my_cost'],
            'sale_rate' => $inventory['sale_rate'],
        ]);
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
