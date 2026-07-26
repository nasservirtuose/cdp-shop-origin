<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('provider');
            $table->string('provider_transaction_id');
            $table->foreignId('product_id')->constrained('shop_products');
            $table->unsignedBigInteger('origin_pro_id')->nullable();
            $table->string('origin_token')->nullable();
            $table->string('origin_status')->default('UNMATCHED');
            $table->unsignedBigInteger('amount_cents');
            $table->string('currency', 3)->default('EUR');
            $table->string('payment_status')->default('PAID');
            $table->string('reward_status')->default('NO_PRO');
            $table->json('economic_snapshot')->nullable();
            $table->decimal('reward_amount', 10, 2)->nullable();
            $table->unsignedTinyInteger('reward_tier')->nullable();
            $table->json('reward_draw_context')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_transaction_id']);
            $table->index('origin_pro_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
    }
};
