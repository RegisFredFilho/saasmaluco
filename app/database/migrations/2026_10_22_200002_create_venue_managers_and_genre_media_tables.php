<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venue_managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_profile_id')->constrained('venue_profiles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 32); // owner, admin
            $table->timestamps();

            $table->unique(['venue_profile_id', 'user_id']);
            $table->index(['user_id', 'role']);
        });

        Schema::create('venue_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_profile_id')->constrained('venue_profiles')->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained('genres')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['venue_profile_id', 'genre_id']);
        });

        Schema::create('venue_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_profile_id')->constrained('venue_profiles')->cascadeOnDelete();
            $table->string('path');
            $table->string('caption', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('venue_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_media');
        Schema::dropIfExists('venue_genre');
        Schema::dropIfExists('venue_managers');
    }
};
