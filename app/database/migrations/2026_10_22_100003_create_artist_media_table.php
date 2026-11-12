<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();
            $table->string('type', 32); // photo, video_link
            $table->string('path')->nullable();
            $table->string('external_url', 2048)->nullable();
            $table->string('caption', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['artist_profile_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_media');
    }
};
