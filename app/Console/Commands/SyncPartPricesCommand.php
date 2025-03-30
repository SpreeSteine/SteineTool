<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\ItemPrice;
use App\Services\BrickLink\BrickLinkApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class SyncPartPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-part-prices-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize part prices from BrickLink API and compare with market prices';

    public function __construct(private readonly BrickLinkApiClient $apiClient)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Inventory::each(function (Inventory $inventory) {

            // Generate unique identifier for the item (now also in Inventory table)
            $idx = "{$inventory->item_no}-{$inventory->color_id}-{$inventory->new_or_used}";

            // Check if price data is already in the database and recent (within 7 days)
            $existingPrice = ItemPrice::where('id', $idx)
                ->where('last_synced_at', '>=', Carbon::now()->subDays(7))
                ->first();

            // If recent data exists, use it for market price analysis but skip API call
            if ($existingPrice) {
                $this->info("Using cached data for {$idx}, last synced less than 7 days ago.");
                $priceDetails = json_decode(Storage::get("prices/{$idx}.json"), true);
                $this->analyzeMarketPrices($idx, $priceDetails, $existingPrice->avg_price);
                return;
            }

            // Fetch latest price data from BrickLink API
            $response = $this->apiClient->request('GET', "items/{$inventory->item_type}/{$inventory->item_no}/price", [
                'new_or_used' => $inventory->new_or_used,
                'color_id' => $inventory->color_id,
                'country_code' => 'DE',
                'currency_code' => 'EUR',
            ]);

            $data = $response['data'];

            $priceDetails = $data['price_detail'];

            // Update or create price record in the database
            $price = ItemPrice::updateOrCreate([
                'id' => $idx,
            ], [
                'item_no' => $inventory->item_no,
                'color_id' => $inventory->color_id,
                'item_type' => $inventory->item_type,
                'new_or_used' => $inventory->new_or_used,
                'currency_code' => $data['currency_code'],
                'min_price' => $data['min_price'],
                'max_price' => $data['max_price'],
                'avg_price' => $data['avg_price'],
                'qty_avg_price' => $data['qty_avg_price'],
                'unit_quantity' => $data['unit_quantity'],
                'total_quantity' => $data['total_quantity'],
                'last_synced_at' => now(),
            ]);

            // Store the detailed price data as a JSON file
            Storage::put("prices/{$idx}.json", json_encode($priceDetails, JSON_PRETTY_PRINT));

            // Perform market price analysis
            $this->analyzeMarketPrices($idx, $priceDetails, $price->avg_price);
        });
    }

    /**
     * Analyzes market prices, calculates price competitiveness, and suggests an optimal price.
     */
    private function analyzeMarketPrices(string $idx, array $priceDetails, float $ownPrice): void
    {
        $this->info("Analyzing market prices for {$idx}");

        $pricesCollection = collect($priceDetails);

        // Determine best market price
        $bestPrice = $pricesCollection->min('unit_price') ?? null;
        $marketAvgPrice = $pricesCollection->avg('unit_price') ?? null;

        // Get total quantity of items priced below own price
        $qtyBelowOwn = $pricesCollection->where('unit_price', '<', $ownPrice)->sum('quantity');

        // Check if we are competitive
        $competitiveness = ($marketAvgPrice !== null && $marketAvgPrice < $ownPrice) ? 'Competitive' : 'Expensive';

        // Calculate percentage difference from best market price
        $priceDifferencePercentage = ($bestPrice !== null && $bestPrice > 0)
            ? (($ownPrice - $bestPrice) / $bestPrice) * 100
            : null;

        // Get inventory details for further decision-making
        $inventory = Inventory::where('id', $idx)->first();
        $myCost = $inventory?->my_cost ?? null;  // Ensure we have a valid cost
        $stockLevel = $inventory?->quantity ?? 0; // Get available stock

        // Define minimum profit margin (10% for now, can be changed dynamically)
        $desiredProfitMargin = 1.10;

        // 1. Set a baseline suggested price (never below cost + profit margin)
        if ($myCost !== null && $myCost > 0) {
            $minSellingPrice = $myCost * $desiredProfitMargin;
        } else {
            $minSellingPrice = null;  // If no cost data, no strict lower limit
        }

        // 2. Adjust price based on market conditions
        if ($ownPrice <= $marketAvgPrice) {
            // If our price is already at/below market average, keep it
            $suggestedPrice = $ownPrice;
        } elseif ($bestPrice !== null) {
            // If we are above the best price, adjust strategically
            if ($stockLevel > 10) {
                // High stock: More aggressive pricing (move inventory faster)
                $suggestedPrice = max($bestPrice * 1.02, $minSellingPrice);
            } else {
                // Low stock: Maintain some profit buffer
                $suggestedPrice = max($bestPrice * 1.05, $minSellingPrice);
            }
        } else {
            // If no best price is available, fallback to market average or a safe margin
            $suggestedPrice = max($marketAvgPrice * 1.05, $minSellingPrice);
        }

        // Round suggested price for better presentation
        $suggestedPrice = round($suggestedPrice, 4);

        // Update inventory with analyzed pricing data
        Inventory::where('id', $idx)->update([
            'best_price' => $bestPrice,
            'qty_below_own' => $qtyBelowOwn,
            'competitiveness' => $competitiveness,
            'price_difference_percentage' => $priceDifferencePercentage,
            'suggested_price' => $suggestedPrice,
        ]);
    }
}
