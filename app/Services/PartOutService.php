<?php

namespace App\Services;

use App\Models\AnalyzedSet;
use App\Models\Inventory;
use App\Models\SetPart;
use Illuminate\Support\Facades\DB;

class PartOutService
{
    /**
     * Calculate and update the cost per part from a parted-out set
     */
    public static function calculatePartCosts(int $setId, float $setPrice)
    {
        // Get all parts in the set
        $parts = AnalyzedSet::where('set_id', $setId)->get();

        // Get total market value of all parts
        $totalMarketValue = $parts->sum(fn($part) => $part->market_price * $part->quantity);

        if ($totalMarketValue <= 0) {
            return;
        }

        DB::transaction(function () use ($parts, $setPrice, $totalMarketValue) {
            foreach ($parts as $part) {
                // Calculate proportionate cost
                $costShare = ($part->market_price * $part->quantity) / $totalMarketValue;
                $myCost = ($setPrice * $costShare) / $part->quantity;

                // Update inventory with calculated my_cost
                Inventory::where('item_no', $part->item_no)
                    ->where('color_id', $part->color_id)
                    ->update(['my_cost' => round($myCost, 4)]);
            }
        });
    }
}
