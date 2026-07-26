<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kooneo_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('kooneo_transaction_id')->nullable();
            $table->string('kooneo_order_id')->nullable();
            $table->json('raw_payload');
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->string('processing_status')->default('received');
            $table->text('processing_error')->nullable();
            $table->timestamps();

            $table->index('event_type');
            $table->index('kooneo_transaction_id');
            $table->unique(['event_type', 'kooneo_transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kooneo_webhook_events');
    }
};