<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('main_image')->nullable();
            $table->foreignId('category_id')->nullable()
                ->constrained('shop_categories')->nullOnDelete();

            // Canal commercial : string + enum PHP applicatif (pas d'enum SQL, plus souple)
            $table->string('commerce_mode')->default('DIRECT_SHOP')->index();

            // Vendeur / references externes
            $table->string('seller_provider')->nullable();
            $table->string('external_reference')->nullable();
            $table->string('external_checkout_url')->nullable();

            // Affiliation : colonnes presentes des M1, FK reelles ajoutees en M4
            // (les tables affiliate_* n'existent pas encore -> pas de contrainte ici)
            $table->unsignedBigInteger('affiliate_provider_id')->nullable();
            $table->unsignedBigInteger('affiliate_program_id')->nullable();
            $table->string('affiliate_product_url')->nullable();
            $table->boolean('affiliate_reward_enabled')->default(false);

            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_products');
    }
};
