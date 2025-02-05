<div class="p-2 sm:p-5 sm:py-0 md:pt-5 space-y-3">
    <form wire:submit.prevent="submit" >
        <div class="mb-4 xl:mb-8">
            <h1 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">
                BrickLink API
            </h1>
            <p class="text-sm text-gray-500 dark:text-neutral-500">
                Configure your BrickLink API credentials
            </p>
        </div>

        <div class="bg-gray-50 space-y-4 mb-4 p-5 md:p-8 border border-gray-200 shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="consumerKey" label="Customer key" placeholder="" />
                <flux:input wire:model="consumerSecret" label="Customer secret" placeholder="" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="accessToken" label="Access token" placeholder="" />
                <flux:input wire:model="tokenSecret" label="Token secret" placeholder="" />
            </div>

            <flux:description>The BrickLink Credentials are locally stored and encrypted on your computer.</flux:description>


            <flux:button
                wire:click="save"
                variant="primary">
                Save
            </flux:button>
        </div>


    </form>
</div>
