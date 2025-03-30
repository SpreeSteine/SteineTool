<div>
    <flux:modal.trigger name="add-storage">
        <flux:button>Add new Storage</flux:button>
    </flux:modal.trigger>

    <flux:modal name="add-storage" class="w-[200px] space-y-6">

        <form wire:submit.prevent="save" >
            <div>
                <flux:heading size="lg">Storage</flux:heading>
                <flux:subheading>Enter the details of the new storage.</flux:subheading>
            </div>

            <flux:input label="Identifier" wire:model.live="identifier" />
            <flux:description>Enter a unique identifier for the storage. (i.e. M0001, K0001)</flux:description>

            @if ($identifier)

                <flux:separator text="Units" />

                <flux:button wire:click="addUnit" variant="filled">Add Unit</flux:button>

                <div class="space-y-4">
                    @foreach ($units as $index => $unit)
                        <div class="flex items-center gap-4">
                            <flux:input label="Unit Identifier" wire:model="units.{{ $index }}.identifier" />
                            <flux:input label="Unit Length" wire:model="units.{{ $index }}.length" />
                            <flux:input label="Unit Width" wire:model="units.{{ $index }}.width" />
                            <flux:input label="Unit Height" wire:model="units.{{ $index }}.height" />
                            <flux:button class="mt-2" wire:click="removeUnit({{ $index }})" variant="filled">Remove</flux:button>
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
