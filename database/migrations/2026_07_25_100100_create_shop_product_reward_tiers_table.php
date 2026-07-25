<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_product_reward_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->unsignedTinyInteger('tier_number');
            $table->decimal('range_start_percentage', 5, 2);
            $table->decimal('range_end_percentage', 5, 2);
            $table->unsignedTinyInteger('probability_percentage');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'tier_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_product_reward_tiers');
    }
};
