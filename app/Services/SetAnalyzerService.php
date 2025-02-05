<?php

namespace App\Services;

use App\Models\AnalyzedSet;
use App\Services\BrickLink\BrickLinkService;
use Illuminate\Support\Facades\Log;

class SetAnalyzerService
{
    public function __construct(
        private readonly BrickLinkService $brickLinkService,
    ) {}

    /**
     * Processes the set analysis and updates the database.
     */
    public function analyzeSet(AnalyzedSet $analyzer): void
    {
        Log::info("Starting analysis for set: {$analyzer->set_number}");

        // Fetch set parts
        $setParts = $this->brickLinkService->getSetParts($analyzer->set_number);
        if (empty($setParts['data'])) {
            Log::error("No parts found for set {$analyzer->set_number}");
            Log::info($setParts);
            return;
        }

        // Fetch inventory
        $inventory = $this->brickLinkService->getInventory();

        if (empty($inventory)) {
            Log::error("Error fetching inventory from BrickLink.");
            return;
        }

        // Prepare analysis variables
        $totalValue = 0;
        $entries = collect($setParts['data'])->pluck('entries')->flatten(1);
        $existingParts = collect($inventory)->mapWithKeys(fn ($part) => ["{$part['item']['no']}_{$part['color_id']}" => true]);

        $newPartsCount = 0;

        $analyzer->update([
            'total_parts' => $entries->count(),
        ]);

        $entries->each(function (array $entry, int $key) use (&$totalValue, &$newPartsCount, $existingParts, $analyzer, $entries) {
            if (!isset($entry['item']['no'], $entry['quantity'], $entry['color_id'])) {
                Log::warning("Skipping invalid entry: " . json_encode($entry));
                return;
            }

            $itemId = $entry['item']['no'];
            $quantity = $entry['quantity'];
            $type = $entry['item']['type'];
            $colorId = $entry['color_id'];
            $partIdentifier = "{$itemId}_{$colorId}";

            // Check if part is new
            if (!isset($existingParts[$partIdentifier])) {
                $newPartsCount++;
            }

            // Fetch average price
            $avgPrice = app(BrickLinkService::class)->getPartPrice($itemId, $type, $colorId);
            if ($avgPrice !== null) {
                $totalValue += $avgPrice * $quantity;
            }

            $analyzer->update([
                'status' => 'processing parts: ' . ($key + 1) . '/' . $entries->count(),
            ]);
        });

        // Calculate results
        $povRatio = $totalValue / $analyzer->price;
        $newPartsPercentage = ($newPartsCount / max(1, $entries->count())) * 100;

        // Update set analysis
        $analyzer->update([
            'status' => 'completed',
            'total_value' => round($totalValue, 2),
            'pov_ratio' => round($povRatio, 2),
            'new_parts_count' => $newPartsCount,
            'new_parts_percentage' => round($newPartsPercentage, 2),
        ]);

        Log::info("Set analysis completed: {$analyzer->set_number}");
    }
}
