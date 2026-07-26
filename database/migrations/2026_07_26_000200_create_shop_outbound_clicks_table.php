<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_outbound_clicks', function (Blueprint $table) {
            $table->id();
            $table->uuid('click_uuid')->unique();
            $table->string('visitor_uuid')->nullable();
            $table->foreignId('product_id')->constrained('shop_products');
            $table->string('commerce_mode');
            $table->string('origin_token')->nullable();
            $table->unsignedBigInteger('pro_id')->nullable();
            $table->string('provider');
            $table->string('destination_url', 2000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_outbound_clicks');
    }
};
