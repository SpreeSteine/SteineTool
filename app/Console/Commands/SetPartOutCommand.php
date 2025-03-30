<?php

namespace App\Console\Commands;

use App\Models\AnalyzedSet;
use App\Models\Inventory;
use App\Models\InventorySource;
use App\Services\BrickLink\BrickLinkPriceService;
use App\Services\BrickLink\BrickLinkService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SetPartOutCommand extends Command
{
    protected $signature = 'app:set-part-out-command';
    protected $description = 'Part out a LEGO set and store items in inventory';

    public function __construct(
        private readonly BrickLinkPriceService $brickLinkPriceService,
        private readonly BrickLinkService $brickLinkService
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $set = AnalyzedSet::where('id', 3)->first();
        if (!$set) {
            Log::error("Set not found in database.");
            return;
        }

        $setNumber = $set->set_number;
        $setPrice = $set->price;

        Log::info("Fetching parts for set {$setNumber}");

        // Fetch set parts from BrickLink
        $setParts = $this->brickLinkService->getSetParts($setNumber);
        if (empty($setParts)) {
            Log::error("No parts found for set {$setNumber}");
            return;
        }

        // Initialize totalMarketValue
        $totalMarketValue = 0;

        DB::transaction(function () use ($setParts, $setNumber, $setPrice, &$totalMarketValue) {
            $parts = collect($setParts);
            $lastPartIndex = $parts->count() - 1;
            $totalAllocatedCost = 0;

            foreach ($parts as $index => $part) {

                $entry = collect($part['entries'][0]);
                $itemNo = $entry->get('item')['no'];
                $itemType = $entry->get('item')['type'];
                $colorId = $entry->get('color_id');
                $quantity = $entry->get('quantity');

                // Fetch individual part price from BrickLink
                $brickLinkData = $this->brickLinkPriceService->fetchPrice($itemNo, $colorId, $itemType, 'N', true);
                if (!$brickLinkData) {
                    Log::warning("Skipping part {$itemNo} - {$colorId} (No price data found)");
                    continue;
                }

                $marketPrice = $brickLinkData['qty_avg_price'] ?? null;

                if ($marketPrice === null) {
                    Log::warning("Skipping part {$itemNo} - {$colorId} (No market price available)");
                    continue;
                }

                // Accumulate total market value dynamically
                $totalMarketValue += ($marketPrice * $quantity);
            }

            // If totalMarketValue is still 0, we cannot distribute the cost
            if ($totalMarketValue <= 0) {
                Log::error("Invalid market value for set {$setNumber}. Cannot calculate cost distribution.");
                return;
            }

            // Reset loop to now correctly distribute the cost
            foreach ($parts as $index => $part) {
                $entry = collect($part['entries'][0]);
                $itemNo = $entry->get('item')['no'];
                $itemType = $entry->get('item')['type'];
                $colorId = $entry->get('color_id');
                $quantity = $entry->get('quantity');

                // Fetch part price again (needed for cost distribution)
                $brickLinkData = $this->brickLinkPriceService->fetchPrice($itemNo, $colorId, $itemType,'N', true);
                if (!$brickLinkData) {
                    continue;
                }

                $marketPrice = $brickLinkData['qty_avg_price'] ?? null;

                if ($marketPrice === null) {
                    continue;
                }

                // ✅ Correctly distribute the cost of the set to each part
                $costShare = ($marketPrice * $quantity) / $totalMarketValue;
                $myCost = ($setPrice * $costShare) / $quantity; // No rounding yet

                // Track allocated cost to adjust last item
                $totalAllocatedCost += $myCost * $quantity;

                // If this is the last part, adjust for rounding errors
                if ($index === $lastPartIndex) {
                    $remainingCost = $setPrice - $totalAllocatedCost;
                    $myCost += $remainingCost / $quantity;
                }

                // Store part in inventory_sources (tracking which set this part came from)
                InventorySource::create([
                    'item_no' => $itemNo,
                    'color_id' => $colorId,
                    'quantity' => $quantity,
                    'my_cost' => round($myCost, 4), // Round only at the end
                    'set_number' => $setNumber,
                ]);

                // Update main inventory (weighted cost from multiple sets)
                $this->updateInventory($itemNo, $colorId);
            }
        });

        Log::info("Successfully parted out set {$setNumber}");
    }

    /**
     * Updates inventory using weighted average cost from multiple sources.
     */
    private function updateInventory(string $itemNo, int $colorId)
    {
        $sources = InventorySource::where('item_no', $itemNo)->where('color_id', $colorId)->get();

        if ($sources->isEmpty()) {
            return;
        }

        $totalQuantity = $sources->sum('quantity');
        $weightedCost = $sources->sum(fn($s) => $s->quantity * $s->my_cost) / $totalQuantity;

        $idx = "{$itemNo}-{$colorId}-N";

        // if inventory exists, update cost
        if (Inventory::where('id', $idx)->exists()) {

            Inventory::where('id', $idx)->update([
                'my_cost' => round($weightedCost, 4),
            ]);
        }
    }
}
