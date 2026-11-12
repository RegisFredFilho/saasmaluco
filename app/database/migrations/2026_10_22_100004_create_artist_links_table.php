<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();
            $table->string('label', 120);
            $table->string('url', 2048);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('artist_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_links');
    }
};
