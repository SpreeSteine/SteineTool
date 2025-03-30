<?php

namespace App\Services;

use App\Models\AnalyzedSet;
use App\Models\Inventory;
use App\Services\BrickLink\BrickLinkPriceService;
use App\Services\BrickLink\BrickLinkService;
use Illuminate\Support\Facades\Log;

class SetAnalyzerService
{
    public function __construct(
        private readonly BrickLinkService $brickLinkService,
        private readonly BrickLinkPriceService $brickLinkPriceService
    ) {}

    /**
     * Processes the set analysis and updates the database.
     */
    public function analyzeSet(AnalyzedSet $analyzer): void
    {
        Log::info("Starting analysis for set: {$analyzer->set_number}");

        // Fetch set parts
        $setParts = $this->brickLinkService->getSetParts($analyzer->set_number);

        if (empty($setParts)) {
            Log::error("No parts found for set {$analyzer->set_number}");
            Log::info($setParts);
            return;
        }

        // Prepare analysis variables
        $totalValue = [
            'min' => 0,
            'avg' => 0,
            'qty_avg_price' => 0,
        ];

        $entries = collect($setParts)->pluck('entries')->flatten(1);

        $newParts = collect();

        $analyzer->update([
            'total_parts' => $entries->count(),
        ]);

        $entries->each(function (array $entry, int $key) use (&$totalValue, &$newParts, $analyzer, $entries) {
            if (!isset($entry['item']['no'], $entry['quantity'], $entry['color_id'])) {
                Log::warning("Skipping invalid entry: " . json_encode($entry));
                return;
            }

            $idx = "{$entry['item']['no']}-{$entry['color_id']}-N";

            $itemId = $entry['item']['no'];
            $quantity = $entry['quantity'];
            $type = $entry['item']['type'];
            $colorId = $entry['color_id'];
            $partIdentifier = "{$itemId}_{$colorId}";

            if ($entry['is_counterpart']) {
                return;
            }

            // Fetch average price
            $price = $this->brickLinkPriceService
                ->fetchPrice($itemId, $colorId, $type,'N');

            $totalValue['min'] += $price['min_price'] * $quantity;
            $totalValue['avg'] += $price['avg_price'] * $quantity;
            $totalValue['qty_avg_price'] += $price['qty_avg_price'] * $quantity;

            // check if Part exists in Inventory
            $existingParts = Inventory::query()
                ->where('id', $idx)
                ->get();

            if ($existingParts->isEmpty()) {
                $newParts->put($idx, $idx);
            }

            $analyzer->update([
                'status' => 'processing parts: ' . ($key + 1) . '/' . $entries->count(),
            ]);
        });

        // Calculate results
        $povRatio = [
            'min' => $totalValue['min'] / $analyzer->price,
            'avg' => $totalValue['avg'] / $analyzer->price,
            'qty_avg' => $totalValue['qty_avg_price'] / $analyzer->price,
        ];

        $newPartsPercentage = ($newParts->count() / max(1, $entries->count())) * 100;

        // Update set analysis
        $analyzer->update([
            'status' => 'completed',
            'total_value_min' => $totalValue['min'],
            'total_value_avg' => $totalValue['avg'],
            'total_value_qty_avg' => $totalValue['qty_avg_price'],
            'pov_ratio_min' => $povRatio['min'],
            'pov_ratio_avg' => $povRatio['avg'],
            'pov_ratio_qty_avg' => $povRatio['qty_avg'],
            'new_parts_count' => $newParts->count(),
            'new_parts_percentage' => round($newPartsPercentage, 2),
        ]);

        Log::info("Set analysis completed: {$analyzer->set_number}");
    }
}
