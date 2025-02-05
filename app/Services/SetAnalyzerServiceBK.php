<?php

namespace App\Services;

use App\Models\AnalyzedSet;
use Illuminate\Support\Facades\Log;

class SetAnalyzerServiceBK
{
    public function __construct(private readonly BrickLinkService $brickLinkService)
    {
    }

    public function analyze(AnalyzedSet $analyzer): void
    {
        // Get set information
        $setParts = $this->brickLinkService->getSetParts($analyzer->set_number);

        if (empty($setParts['data'])) {
            throw new \Exception("No parts found for set {$analyzer->set_number}.");
        }

        // Get inventory
        // TODO: sync inventory in different service
        $inventory = cache()->remember('bricklink_inventory', now()->addHour(), function () {
            return $this->brickLinkService->callApi('GET', 'inventories');
        });

        if (empty($inventory['data'])) {
            throw new \Exception("Error fetching inventory.");
        }

        // Calculate the total value of the set
        $totalValue = 0;
        $entries = collect($setParts['data'])->pluck('entries')->flatten(1);

        // Combined list of existing parts (item.no + color_id)
        $existingParts = collect($inventory['data'])->map(function ($part) {
            return "{$part['item']['no']}_{$part['color_id']}";
        })->toArray();

        $newPartsCount = 0;

        foreach ($entries as $entry) {

            Log::info('Analyzing entry: ' . $entry['item']['no']);

            if (!isset($entry['item']['no'], $entry['quantity'], $entry['color_id'])) {
                throw new \Exception("Invalid entry data: " . json_encode($entry));
            }

            $itemId = $entry['item']['no'];
            $quantity = $entry['quantity'];
            $type = $entry['item']['type'];
            $colorId = $entry['color_id'];

            // Part identifier
            $partIdentifier = "{$itemId}_{$colorId}";

            // Check if part is new
            if (!in_array($partIdentifier, $existingParts)) {
                $newPartsCount++;
            }

            // Get the average price per piece
            $cacheKey = "bricklink_part_price_{$partIdentifier}";

            // Get the average price from cache or API
            $avgPrice = cache()->remember($cacheKey, now()->addDays(7), function () use ($itemId, $type, $colorId) {
                $priceInfo = $this->brickLinkService->callApi(
                    method: 'GET',
                    endpoint: "items/{$type}/{$itemId}/price",
                    params: [
                        'new_or_used' => 'N',
                        'guide_type' => 'sold',
                        'vat' => 'Y',
                        'color_id' => $colorId,
                    ]
                );

                return !empty($priceInfo['data']['avg_price'])
                    ? ceil($priceInfo['data']['avg_price'] * 1000) / 1000
                    : null;
            });

            // Ensure a valid price per piece
            if ($avgPrice !== null) {
                // Calculate the total value
                $totalValue += $avgPrice * $quantity;
            } else {
                throw new \Exception("No valid price found for part {$itemId} {$type}.");
            }
        }

        // Calculate the POV ratio
        $povRatio = $totalValue / $analyzer->price;

        $newPartsRatio = $newPartsCount / $entries->count();
        $newPartsPercentage = $newPartsRatio * 100;

        $analyzer->update([
            'total_parts' => $entries->count(),
            'total_value' => round($totalValue, 2),
            'pov_ratio' => round($povRatio, 2),
            'new_parts_count' => $newPartsCount,
            'new_parts_percentage' => round($newPartsPercentage, 2),
        ]);
    }
}
