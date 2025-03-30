<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\Item;
use App\Services\BrickLink\BrickLinkApiClient;
use Illuminate\Console\Command;

class SyncItemInfosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-item-infos-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(private readonly BrickLinkApiClient $apiClient)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Inventory::each(function (Inventory $inventory) {

            // check if item already exists in database
            if (Item::query()
                ->where('no', $inventory->item_no)
                ->where('type', $inventory->item_type)
                ->exists()) {

                $this->info("Item {$inventory->item_no} already exists in database.");

                return;
            }

            $response = $this->apiClient->request('GET', "items/{$inventory->item_type}/{$inventory->item_no}");

            $data = $response['data'];

            // calculate package_dimensions
            $data['package_dim_x'] = $data['dim_x'] * 0.8;
            $data['package_dim_y'] = $data['dim_y'] * 0.8;
            $data['package_dim_z'] =
                ($data['dim_z'] == 0) ? 0.5 : $data['dim_z'] * 1.15;

            Item::query()->create($data);
        });
    }
}
