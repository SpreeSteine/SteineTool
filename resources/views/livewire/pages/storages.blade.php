<div class="p-2 sm:p-5 sm:py-0 md:pt-5 space-y-3">
    <div class="mb-4 xl:mb-8">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-neutral-200 mb-3">
            {{ __('Storage Management') }}
        </h1>

        <livewire:components.storage.add-new-storage />
    </div>

    <flux:table wire:poll.5s :paginate="$this->storages">
        <flux:columns>
            <flux:column sortable :sorted="$sortBy === 'identifier'" :direction="$sortDirection" wire:click="sort('identifier')">Identifier</flux:column>
            <flux:column>Capacity</flux:column>
            <flux:column>Dimensions</flux:column>
            <flux:column>Items</flux:column>
            <flux:column>Actions</flux:column>
        </flux:columns>

        <flux:rows>
            @foreach ($this->storages as $storage)
                <flux:row :key="'storage' . $storage->id">
                    <flux:cell class="bg-gray-100">
                        {{ $storage->identifier }}
                    </flux:cell>
                    <flux:cell class="bg-gray-100"></flux:cell>
                    <flux:cell class="bg-gray-100"></flux:cell>
                    <flux:cell class="bg-gray-100">
                        {{ $storage->total_items ?? 0 }}
                    </flux:cell>
                    <flux:cell class="bg-gray-100">
                        <div class="flex gap-2">
                            <livewire:components.storage.edit-storage :storage="$storage" :key="$storage->id" />
                            <flux:button
                                wire:click="deleteStorage({{ $storage->id }})"
                                wire:confirm="Are you sure you want to delete this storage and all its units?"
                                variant="danger">
                                Delete
                            </flux:button>
                        </div>
                    </flux:cell>
                </flux:row>

                @forelse ($storage->units as $unit)
                    <flux:row :key="'unit' . $unit->id">
                        <flux:cell class="pl-6">{{ $unit->identifier }}</flux:cell>
                        <flux:cell>
                            <div class="flex items-center gap-2">
                                <!-- Step Progress -->
                                <div class="w-32 flex items-center gap-x-1">
                                    @php
                                        $percentage = round($unit->capacity_percentage);
                                        $steps = 4;
                                        $stepPercentage = 100 / $steps;
                                        $completedSteps = floor($percentage / $stepPercentage);
                                    @endphp
                                    @for ($i = 0; $i < $steps; $i++)
                                        <div class="w-full h-2.5 flex flex-col justify-center overflow-hidden
                                            {{ $i < $completedSteps ? 'bg-blue-600 dark:bg-blue-500' : 'bg-gray-300 dark:bg-neutral-600' }}
                                            text-xs text-white text-center whitespace-nowrap transition duration-500"
                                             role="progressbar"
                                             aria-valuenow="{{ $percentage }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    @endfor

                                </div>
                                <!-- End Step Progress -->
                                <div class="w-10 text-end">
                                    <span class="text-sm text-gray-800 dark:text-white">{{ $percentage }}%</span>
                                </div>
                            </div>
                        </flux:cell>
                        <flux:cell>{{ $unit->length }}x{{ $unit->width }}x{{ $unit->height }}</flux:cell>
                        <flux:cell>{{ $unit->number_of_items }}</flux:cell>
                        <flux:cell></flux:cell>
                    </flux:row>
                @empty
                    <flux:row>
                        <flux:cell colspan="5" class="pl-6 text-gray-500">No units assigned</flux:cell>
                    </flux:row>
                @endforelse
            @endforeach
        </flux:rows>
    </flux:table>
</div>
