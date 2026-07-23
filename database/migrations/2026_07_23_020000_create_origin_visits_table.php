<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('origin_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_uuid')->unique(); // un seul row par visiteur (Last Click)
            $table->string('origin_token');
            $table->unsignedBigInteger('pro_id')->index(); // pro attribué (= planipets_pro_id)
            $table->string('landing_url')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('first_touch_at');
            $table->timestamp('last_touch_at');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('origin_visits');
    }
};
