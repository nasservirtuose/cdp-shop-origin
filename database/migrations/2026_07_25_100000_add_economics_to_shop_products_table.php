<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->decimal('price_ttc', 10, 2)->nullable();
            $table->decimal('vat_percent', 5, 2)->nullable();
            $table->decimal('purchase_cost_ht', 10, 2)->nullable();
            $table->decimal('variable_costs_ht', 10, 2)->nullable();
            $table->decimal('rex_share_percent', 5, 2)->nullable();
            $table->decimal('low_bound', 10, 2)->nullable();
            $table->decimal('high_bound', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn([
                'price_ttc',
                'vat_percent',
                'purchase_cost_ht',
                'variable_costs_ht',
                'rex_share_percent',
                'low_bound',
                'high_bound',
            ]);
        });
    }
};
