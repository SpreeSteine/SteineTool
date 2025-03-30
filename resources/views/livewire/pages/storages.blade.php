<div class="p-2 sm:p-5 sm:py-0 md:pt-5 space-y-3">
    <div class="mb-4 xl:mb-8">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-neutral-200 mb-3">
            {{ __('Storage Management') }}
        </h1>

        <livewire:components.storage.add-new-storage />
    </div>

    <flux:table wire:poll.5s :paginate="$this->storages">
        <flux:columns>
            <flux:column sortable :sorted="$sortBy === 'date'" :direction="$sortDirection" wire:click="sort('identifier')">Identifier</flux:column>
            <flux:column>Capacity %</flux:column>
            <flux:column>Dimensions</flux:column>
            <flux:column>Actions</flux:column>
        </flux:columns>

        <flux:rows>
            @foreach ($this->storages as $storage)
                <flux:row :key="'storage' . $storage->id" >
                    <flux:cell class="bg-gray-100" colspan="3"  >
                        {{ $storage->identifier }}
                    </flux:cell>
                    <flux:cell class="bg-gray-100" >
                        <livewire:components.storage.edit-storage :storage="$storage" :key="$storage->id" />
                    </flux:cell>
                </flux:row>

                @forelse ($storage->units as $unit)
                    <flux:row :key="'unit' . $unit->id">
                        <flux:cell class="pl-6">{{ $unit->identifier }}</flux:cell>
                        <flux:cell>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full"
                                      style="background-color: {{ $unit->capacity_percentage == 0 ? 'green' : ($unit->capacity_percentage > 90 ? 'red' : 'yellow') }}"></span>
                                {{ round($unit->capacity_percentage) }}%
                            </div>
                        </flux:cell>
                        <flux:cell>{{ $unit->length }}x{{ $unit->width }}x{{ $unit->height }}</flux:cell>
                        <flux:cell>
                            @if ($loop->first)

                            @endif
                        </flux:cell>
                    </flux:row>
                @empty
                    <flux:row>
                        <flux:cell colspan="4" class="pl-6 text-gray-500">No units assigned</flux:cell>
                    </flux:row>
                @endforelse
            @endforeach
        </flux:rows>
    </flux:table>
</div>
