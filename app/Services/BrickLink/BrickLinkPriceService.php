<?php

namespace App\Services\BrickLink;

use App\Models\ItemPrice;
use App\Services\BrickLink\BrickLinkApiClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BrickLinkPriceService
{
    private BrickLinkApiClient $apiClient;

    public function __construct(BrickLinkApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Fetch price for a specific item from BrickLink
     *
     * @param string $itemNo
     * @param int $colorId
     * @param string $newOrUsed ('N' = New, 'U' = Used)
     * @param bool $storeInDatabase (true = save to database, false = just return data)
     * @return array|null
     */
    public function fetchPrice(string $itemNo, int $colorId, string $type, string $newOrUsed, bool $storeInDatabase = true): ?array
    {
        $idx = "{$itemNo}-{$colorId}-{$newOrUsed}";

        // Check if recent price data exists (last 7 days)
        $existingPrice = ItemPrice::where('id', $idx)
            ->where('last_synced_at', '>=', Carbon::now()->subDays(7))
            ->first();

        if ($existingPrice) {
            Log::info("Using cached data for {$idx}, last synced less than 7 days ago.");
            return $existingPrice->toArray();
        }

        try {
            // Fetch latest price data from BrickLink API
            $response = $this->apiClient->request('GET', "items/{$type}/{$itemNo}/price", [
                'new_or_used' => $newOrUsed,
                'color_id' => $colorId,
                'country_code' => 'DE',
                'currency_code' => 'EUR',
            ]);

            $data = $response['data'] ?? null;
            if (!$data) {
                Log::warning("No data received from BrickLink for {$idx}");
                return null;
            }

            $priceDetails = $data['price_detail'] ?? [];

            // Store detailed price data in storage
            Storage::put("prices/{$idx}.json", json_encode($priceDetails, JSON_PRETTY_PRINT));

            // If storing is disabled, return data without saving in DB
            if (!$storeInDatabase) {
                return [
                    'item_no' => $itemNo,
                    'color_id' => $colorId,
                    'new_or_used' => $newOrUsed,
                    'currency_code' => $data['currency_code'],
                    'min_price' => $data['min_price'],
                    'max_price' => $data['max_price'],
                    'avg_price' => $data['avg_price'],
                    'qty_avg_price' => $data['qty_avg_price'],
                    'unit_quantity' => $data['unit_quantity'],
                    'total_quantity' => $data['total_quantity'],
                    'price_detail' => $priceDetails,
                ];
            }

            // Save price data in the database
            $price = ItemPrice::updateOrCreate(
                ['id' => $idx],
                [
                    'item_no' => $itemNo,
                    'color_id' => $colorId,
                    'item_type' => 'part', // Default type, can be modified
                    'new_or_used' => $newOrUsed,
                    'currency_code' => $data['currency_code'],
                    'min_price' => $data['min_price'],
                    'max_price' => $data['max_price'],
                    'avg_price' => $data['avg_price'],
                    'qty_avg_price' => $data['qty_avg_price'],
                    'unit_quantity' => $data['unit_quantity'],
                    'total_quantity' => $data['total_quantity'],
                    'last_synced_at' => now(),
                ]
            );

            Log::info("Successfully updated price data for {$idx}");

            return $price->toArray();

        } catch (\Exception $e) {
            Log::error("Error fetching price for {$idx}: " . $e->getMessage());
            return null;
        }
    }
}
