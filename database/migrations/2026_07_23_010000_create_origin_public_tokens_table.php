<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('origin_public_tokens', function (Blueprint $table) {
            $table->id();
            // pro_id = identité EXTERNE (planipets_pro_id), pas de FK
            $table->unsignedBigInteger('pro_id')->index();
            $table->string('token')->unique();
            // statut : ACTIVE | REVOKED (un seul ACTIVE par pro, garanti côté service)
            $table->string('status')->default('ACTIVE')->index();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('origin_public_tokens');
    }
};
