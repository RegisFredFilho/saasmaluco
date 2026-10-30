<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();
            $table->string('title');
            $table->string('venue_name')->nullable();
            $table->string('city', 120)->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('source', 32)->default('manual'); // manual | booking
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->timestamps();

            $table->index(['artist_profile_id', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_experiences');
    }
};
