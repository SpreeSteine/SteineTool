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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('no')->unique();
            $table->string('name');
            $table->string('type');
            $table->unsignedBigInteger('category_id');
            $table->string('alternate_no')->nullable();
            $table->string('image_url');
            $table->string('thumbnail_url');
            $table->float('weight');
            $table->float('dim_x');
            $table->float('dim_y');
            $table->float('dim_z');
            $table->float('package_dim_x');
            $table->float('package_dim_y');
            $table->float('package_dim_z');
            $table->unsignedSmallInteger('year_released');
            $table->text('description')->nullable();
            $table->boolean('is_obsolete')->default(false);
            $table->timestamps();
        });

        // combined index for no and type
        Schema::table('items', function (Blueprint $table) {
            $table->index(['no', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
