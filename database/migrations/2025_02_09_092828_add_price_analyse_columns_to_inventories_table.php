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
        Schema::table('inventories', function (Blueprint $table) {
            $table->decimal('best_price', 10, 4)->nullable()->after('price');
            $table->integer('qty_below_own')->default(0)->after('best_price');
            $table->enum('competitiveness', ['Competitive', 'Expensive'])->nullable()->after('qty_below_own');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn([
                'best_price_international',
                'best_price_eu',
                'best_price_de',
                'qty_below_own_international',
                'qty_below_own_eu',
                'qty_below_own_de',
                'competitiveness_international',
                'competitiveness_eu',
                'competitiveness_de',
            ]);
        });
    }
};
