<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pro_packs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('pro_id')->index();
            $table->string('name');
            // slug public (utilise en M2 pour /pack/{slug}) : nullable tant que le pack est en DRAFT
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('DRAFT')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_packs');
    }
};
