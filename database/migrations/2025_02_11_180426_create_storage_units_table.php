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
        Schema::create('storage_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storage_id')->constrained();
            $table->string('identifier')->unique();
            $table->float('length')->default(0);
            $table->float('width')->default(0);
            $table->float('height')->default(0);
            $table->float('capacity')->default(0);
            $table->float('capacity_percentage')->default(0);
            $table->float('available_capacity')->default(0);
            $table->enum('status', ['empty', 'partially_occupied', 'occupied'])->default('empty');
            $table->integer('number_of_items')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_units');
    }
};
