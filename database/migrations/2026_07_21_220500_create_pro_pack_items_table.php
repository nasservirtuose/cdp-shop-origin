<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_pack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_id')->constrained('pro_packs')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['pack_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_pack_items');
    }
};
