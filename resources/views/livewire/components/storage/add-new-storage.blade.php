<div>
    <flux:modal.trigger name="add-storage">
        <flux:button variant="primary">Add New Storage</flux:button>
    </flux:modal.trigger>

    <flux:modal name="add-storage" class="w-[600px] max-w-none space-y-6 p-6">
        <form wire:submit.prevent="save">
            <div>
                <flux:heading size="lg">Add Storage</flux:heading>
                <flux:subheading>Enter the details for the new storage.</flux:subheading>
            </div>

            <div class="space-y-2">
                <flux:input
                    label="Identifier"
                    wire:model.live="identifier"
                    placeholder="e.g., M0001, K0001"
                />
                <flux:description>Enter a unique identifier for the storage.</flux:description>
                @error('identifier') <flux:error>{{ $message }}</flux:error> @enderror
            </div>

            @if ($identifier)
                <flux:separator text="Units" />

                <flux:button wire:click="addUnit" variant="outline" class="mb-4">Add Unit</flux:button>

                <div class="space-y-4">
                    @foreach ($units as $index => $unit)
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end border p-4 rounded">
                            <div>
                                <flux:input
                                    label="Unit Identifier"
                                    wire:model="units.{{ $index }}.identifier"
                                />
                                @error("units.{$index}.identifier") <flux:error>{{ $message }}</flux:error> @enderror
                            </div>
                            <div>
                                <flux:input
                                    label="Length"
                                    wire:model="units.{{ $index }}.length"
                                    type="number"
                                    step="0.1"
                                />
                                @error("units.{$index}.length") <flux:error>{{ $message }}</flux:error> @enderror
                            </div>
                            <div>
                                <flux:input
                                    label="Width"
                                    wire:model="units.{{ $index }}.width"
                                    type="number"
                                    step="0.1"
                                />
                                @error("units.{$index}.width") <flux:error>{{ $message }}</flux:error> @enderror
                            </div>
                            <div>
                                <flux:input
                                    label="Height"
                                    wire:model="units.{{ $index }}.height"
                                    type="number"
                                    step="0.1"
                                />
                                @error("units.{$index}.height") <flux:error>{{ $message }}</flux:error> @enderror
                            </div>
                            <flux:button
                                wire:click="removeUnit({{ $index }})"
                                variant="danger"
                                class="h-10"
                            >
                                Remove
                            </flux:button>
                        </div>
                    @endforeach
                </div>

                <flux:separator />
            @endif

            <div class="flex justify-end gap-2">
                <flux:button
                    type="button"
                    variant="ghost"
                    x-on:click="$dispatch('close-modal')"
                >
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
