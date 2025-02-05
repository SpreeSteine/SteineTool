<div class="p-2 sm:p-5 sm:py-0 md:pt-5 space-y-3">
    <form wire:submit.prevent="save" >
        <div class="mb-4 xl:mb-8">
            <h1 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">
                SteineTool SetAnalyzer
            </h1>
            <p class="text-sm text-gray-500 dark:text-neutral-500">
                The SteineTool SetAnalyzer calculates the Price of Value (POV) for LEGO sets, compares their parts with your inventory, and provides data-driven purchase recommendations.
            </p>
        </div>

        <div class="bg-gray-50 space-y-4 mb-4 p-5 md:p-8 border border-gray-200 shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">

            <div class="grid grid-cols-2 gap-4">
                <flux:input
                    wire:model="setNumber"
                    label="Lego SET Number"
                    placeholder="" />

                <flux:input.group label="Price">
                    <flux:select variant="listbox" class="max-w-fit">
                        <flux:option selected>EUR</flux:option>
                    </flux:select>

                    <flux:input
                        wire:model="price"
                        placeholder="" />
                </flux:input.group>


            </div>

            <flux:button
                wire:click="save"
                variant="primary">
                Calculate
            </flux:button>
        </div>

        <flux:table wire:poll.5s :paginate="$this->sets">
            <flux:columns>
                <flux:column>Set Number</flux:column>
                <flux:column sortable :sorted="$sortBy === 'date'" :direction="$sortDirection" wire:click="sort('date')">Date</flux:column>
                <flux:column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">Status</flux:column>
                <flux:column sortable :sorted="$sortBy === 'price'" :direction="$sortDirection" wire:click="sort('price')">Price</flux:column>
                <flux:column sortable :sorted="$sortBy === 'total_parts'" :direction="$sortDirection" wire:click="sort('total_parts')">Total Lots</flux:column>
                <flux:column sortable :sorted="$sortBy === 'total_value'" :direction="$sortDirection" wire:click="sort('total_value')">Total Value</flux:column>
                <flux:column sortable :sorted="$sortBy === 'pov_ratio'" :direction="$sortDirection" wire:click="sort('pov_ratio')">POV Ratio</flux:column>
                <flux:column sortable :sorted="$sortBy === 'new_parts_count'" :direction="$sortDirection" wire:click="sort('new_parts_count')">New Lots Count</flux:column>
                <flux:column sortable :sorted="$sortBy === 'new_parts_percentage'" :direction="$sortDirection" wire:click="sort('new_parts_percentage')">New Lots %</flux:column>
            </flux:columns>

            <flux:rows>
                @foreach ($this->sets as $set)
                    <flux:row :key="$set->id">
                        <flux:cell class="flex items-center gap-3">

                            {{ $set->set_number }}
                        </flux:cell>

                        <flux:cell class="whitespace-nowrap">{{ $set->created_at }}</flux:cell>

                        <flux:cell>
                            <flux:badge size="sm" inset="top bottom">{{ $set->status }}</flux:badge>
                        </flux:cell>

                        <flux:cell variant="strong">{{ $set->price }}</flux:cell>

                        <flux:cell variant="strong">
                            @unless ($set->total_parts)
                                <flux:icon.loading class="size-5" />
                            @else
                                {{ $set->total_parts }}
                            @endunless
                        </flux:cell>

                        <flux:cell variant="strong">
                            @unless ($set->total_value)
                                <flux:icon.loading class="size-5" />
                            @else
                                {{ $set->total_value }}
                            @endunless
                        </flux:cell>

                        <flux:cell variant="strong">
                            @unless ($set->pov_ratio)
                                <flux:icon.loading class="size-5" />
                            @else
                                {{ $set->pov_ratio }}
                            @endunless
                        </flux:cell>

                        <flux:cell variant="strong">
                            @unless ($set->new_parts_count)
                                <flux:icon.loading class="size-5" />
                            @else
                                {{ $set->new_parts_count }}
                            @endunless
                        </flux:cell>

                        <flux:cell variant="strong">
                            @unless ($set->new_parts_percentage)
                                <flux:icon.loading class="size-5" />
                            @else
                                {{ $set->new_parts_percentage }}
                            @endunless
                        </flux:cell>

                    </flux:row>
                @endforeach
            </flux:rows>
        </flux:table>

    </form>
</div>
