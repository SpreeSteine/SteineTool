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
        Schema::create('inventory_sources', function (Blueprint $table) {
            $table->id();
            $table->string('item_no');
            $table->integer('color_id');
            $table->integer('quantity'); // Quantity from this set
            $table->decimal('my_cost', 10, 4); // Cost per piece from this set
            $table->string('set_number'); // Which set it came from
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_sources');
    }
};
