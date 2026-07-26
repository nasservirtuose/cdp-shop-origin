<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('planipets_client_id')->nullable()->index();
            $table->string('injection_status', 32)->default('pending_cdp')->index();
            $table->json('injection_response')->nullable();
            $table->timestamp('injected_at')->nullable();
            $table->unsignedBigInteger('cdp_reward_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn('planipets_client_id');
            $table->dropColumn('injection_status');
            $table->dropColumn('injection_response');
            $table->dropColumn('injected_at');
            $table->dropColumn('cdp_reward_id');
        });
    }
};
