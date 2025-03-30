<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * "inventory_id" => 452664223
     * "item" => array:4 [ …4]
     * "color_id" => 11
     * "color_name" => "Black"
     * "quantity" => 4
     * "new_or_used" => "N"
     * "unit_price" => "0.0540"
     * "bind_id" => 0
     * "description" => ""
     * "remarks" => "M001-04"
     * "bulk" => 1
     * "is_retain" => false
     * "is_stock_room" => false
     * "date_created" => "2025-01-12T05:00:00.000Z"
     * "my_cost" => "0.0000"
     * "sale_rate" => 0
     * "tier_quantity1" => 0
     * "tier_price1" => "0.0000"
     * "tier_quantity2" => 0
     * "tier_price2" => "0.0000"
     * "tier_quantity3" => 0
     * "tier_price3" => "0.0000"
     * "my_weight" => "0.0000"
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->string('id')->index();
            $table->unsignedBigInteger('inventory_id');
            $table->string('item_no');
            $table->string('item_name');
            $table->string('item_type');
            $table->unsignedInteger('item_category_id');
            $table->unsignedInteger('color_id');
            $table->string('color_name');
            $table->unsignedInteger('quantity');
            $table->string('new_or_used');
            $table->decimal('unit_price', 10, 4);
            $table->string('description');
            $table->string('remarks');
            $table->boolean('bulk');
            $table->boolean('is_retain');
            $table->boolean('is_stock_room');
            $table->dateTime('date_created');
            $table->decimal('my_cost', 10, 4);
            $table->unsignedInteger('sale_rate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
