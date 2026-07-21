<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_product_selections', function (Blueprint $table) {
            $table->id();
            // pro_id = identite EXTERNE fournie par le SSO Planipets, jamais le frontend.
            // Pas de FK : la table des pros ne vit pas dans ce projet.
            $table->unsignedBigInteger('pro_id')->index();
            $table->foreignId('product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['pro_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_product_selections');
    }
};
