<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_prices', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('item_no')->index();
            $table->string('item_type')->index();
            $table->string('color_id')->index();
            $table->string('new_or_used')->index();
            $table->string('currency_code');
            $table->decimal('min_price', 10, 4);
            $table->decimal('max_price', 10, 4);
            $table->decimal('avg_price', 10, 4);
            $table->decimal('qty_avg_price', 10, 4);
            $table->integer('unit_quantity');
            $table->integer('total_quantity');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_prices');
    }
};
