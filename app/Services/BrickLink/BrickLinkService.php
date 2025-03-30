<?php

namespace App\Services\BrickLink;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class BrickLinkService
{
    public function __construct(private BrickLinkApiClient $apiClient) {}

    public function getInventory(): Collection
    {
        $inventory = $this->apiClient->request('GET', 'inventories');

        return collect($inventory['data'] ?? []);
//        return Cache::remember('bricklink_inventory', now()->addHour(), function () {
//            return $this->apiClient->request('GET', 'inventories')['data'] ?? [];
//        });
    }

    public function getPartPrice(string $itemId, string $type, int $colorId): ?float
    {
        return Cache::remember("bricklink_part_price_{$itemId}_{$colorId}", now()->addDays(7), function () use ($itemId, $type, $colorId) {
            return $this->apiClient->request('GET', "items/{$type}/{$itemId}/price", [
                'new_or_used' => 'N',
                'guide_type' => 'sold',
                'vat' => 'Y',
                'color_id' => $colorId,
            ])['data']['avg_price'] ?? null;
        });
    }

    /**
     * Fetches the parts list of a specific set.
     */
    public function getSetParts(string $setNumber): Collection
    {
        $data = $this->apiClient->request('GET', "items/SET/{$setNumber}/subsets");

        return collect($data['data'] ?? []);
    }
}
