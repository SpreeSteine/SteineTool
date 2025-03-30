<div class="p-2 sm:p-5 sm:py-0 md:pt-5 space-y-3">
    <div class="mb-4 xl:mb-8">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">
            Inventory
        </h1>
        <flux:button wire:click="syncInventory" variant="primary">Sync Inventory</flux:button>
    </div>

    <flux:table :paginate="$this->inventory">
        <flux:columns>
            <flux:column sortable :sorted="$sortBy === 'item_no'" :direction="$sortDirection" wire:click="sort('item_no')">Part ID</flux:column>
            <flux:column sortable :sorted="$sortBy === 'color_name'" :direction="$sortDirection" wire:click="sort('color_name')">Color Name</flux:column>
            <flux:column sortable :sorted="$sortBy === 'my_cost'" :direction="$sortDirection" wire:click="sort('my_cost')">My Cost</flux:column>
            <flux:column sortable :sorted="$sortBy === 'best_price'" :direction="$sortDirection" wire:click="sort('best_price')">Best Price</flux:column>
            <flux:column sortable :sorted="$sortBy === 'unit_price'" :direction="$sortDirection" wire:click="sort('unit_price')">Price</flux:column>
            <flux:column sortable :sorted="$sortBy === 'new_or_used'" :direction="$sortDirection" wire:click="sort('new_or_used')">Condition</flux:column>
            <flux:column sortable :sorted="$sortBy === 'remarks'" :direction="$sortDirection" wire:click="sort('remarks')">Remarks</flux:column>
            <flux:column sortable :sorted="$sortBy === 'sale_rate'" :direction="$sortDirection" wire:click="sort('sale_rate')">Sale Rate</flux:column>
            <flux:column sortable :sorted="$sortBy === 'price_difference_percentage'" :direction="$sortDirection" wire:click="sort('price_difference_percentage')">Price Difference (%)</flux:column>
            <flux:column sortable :sorted="$sortBy === 'suggested_price'" :direction="$sortDirection" wire:click="sort('suggested_price')">Suggested Price</flux:column>
            <flux:column sortable :sorted="$sortBy === 'qty_below_own'" :direction="$sortDirection" wire:click="sort('qty_below_own')">Qty Below Own</flux:column>
            <flux:column sortable :sorted="$sortBy === 'competitiveness'">Competitive (Intl)</flux:column>
        </flux:columns>

        <flux:rows>
            @foreach ($this->inventory as $inventory)
                <flux:row :key="$inventory->id">
                    <flux:cell class="flex items-center gap-3">
                        {{ $inventory->item_no }}
                    </flux:cell>

                    <flux:cell class="whitespace-nowrap">{{ $inventory->color_name }}</flux:cell>

                    <flux:cell variant="strong">{{ round($inventory->my_cost,3) }}</flux:cell>

                    <flux:cell variant="strong">{{ $inventory->best_price }}</flux:cell>

                    <flux:cell variant="strong">{{ number_format($inventory->unit_price, 4) }}</flux:cell>

                    <flux:cell variant="strong">{{ $inventory->new_or_used }}</flux:cell>

                    <flux:cell variant="strong">{{ $inventory->remarks }}</flux:cell>

                    <flux:cell variant="strong">{{ $inventory->sale_rate }}</flux:cell>

                    <flux:cell variant="strong">{{ round($inventory->price_difference_percentage,2) }}</flux:cell>

                    <flux:cell variant="strong">{{ round($inventory->suggested_price,3) }}</flux:cell>

                    <flux:cell variant="strong">{{ $inventory->qty_below_own }}</flux:cell>

                    <flux:cell variant="strong">{{ $inventory->competitiveness }}</flux:cell>

                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>
</div>
