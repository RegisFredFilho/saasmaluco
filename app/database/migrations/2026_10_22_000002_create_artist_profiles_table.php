<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('display_name');
            $table->string('slug')->unique();
            $table->string('kind', 32); // solo, band, dj
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['owner_user_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_profiles');
    }
};
