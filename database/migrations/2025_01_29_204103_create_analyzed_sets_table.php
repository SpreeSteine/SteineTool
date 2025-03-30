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
        Schema::create('analyzed_sets', function (Blueprint $table) {
            $table->id();
            $table->string('set_number');
            $table->float('price');
            $table->integer('total_parts')->nullable();
            $table->float('total_value_min')->nullable();
            $table->float('total_value_avg')->nullable();
            $table->float('total_value_qty_avg')->nullable();
            $table->float('pov_ratio_min')->nullable();
            $table->float('pov_ratio_avg')->nullable();
            $table->float('pov_ratio_qty_avg')->nullable();
            $table->integer('new_parts_count')->nullable();
            $table->float('new_parts_percentage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyzed_sets');
    }
};
