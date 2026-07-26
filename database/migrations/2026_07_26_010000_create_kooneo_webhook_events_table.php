<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kooneo_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('origin_tag')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('product_reference')->nullable();
            $table->unsignedBigInteger('amount_cents')->nullable();
            $table->string('currency', 3)->nullable();
            $table->boolean('is_test')->default(false);
            $table->json('payload');
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Idempotence: Kooneo re-emet le meme evenement. refund partage
            // le transaction_id du paiement, on dedoublonne sur (transaction_id, type).
            $table->unique(['transaction_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kooneo_webhook_events');
    }
};