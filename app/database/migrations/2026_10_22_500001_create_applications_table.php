<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status', 32)->default('submitted');
            $table->text('venue_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['opportunity_id', 'artist_profile_id']);
            $table->index(['artist_profile_id', 'status']);
            $table->index(['opportunity_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
