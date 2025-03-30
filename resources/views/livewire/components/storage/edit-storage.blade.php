<div>
    <flux:modal.trigger name="edit-storage-{{ $storage->id }}">
        <flux:button>Edit Storage</flux:button>
    </flux:modal.trigger>

    <flux:modal name="edit-storage-{{ $storage->id }}" class="w-2xl space-y-6">
        <form wire:submit.prevent="save">
            <div>
                <flux:heading size="lg">Edit Storage</flux:heading>
                <flux:subheading>Modify storage details</flux:subheading>
            </div>

            <flux:input label="Identifier" wire:model.live="identifier" />
            <flux:description>Unique identifier (i.e. M0001, K0001)</flux:description>

            @if ($identifier)
                <flux:separator text="Units" />

                <flux:button wire:click="addUnit" variant="filled">Add Unit</flux:button>

                <div class="space-y-4">
                    @foreach ($units as $index => $unit)
                        <div class="flex items-center gap-4 border-l-4 p-2"
                             style="border-color: {{ $this->getTrafficLightColor($unit['capacity_percentage']) }}">
                            <flux:input label="Unit Identifier" wire:model="units.{{ $index }}.identifier" />
                            <flux:input label="Length" wire:model="units.{{ $index }}.length" />
                            <flux:input label="Width" wire:model="units.{{ $index }}.width" />
                            <flux:input label="Height" wire:model="units.{{ $index }}.height" />
                            <div class="mt-2">
                                <span class="text-sm">Capacity: {{ round($unit['capacity_percentage']) }}%</span>
                            </div>
                            <flux:button wire:click="removeUnit({{ $index }})" variant="filled">Remove</flux:button>
                        </div>
                    @endforeach
                </div>

                <flux:separator />
            @endif

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Save changes</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
