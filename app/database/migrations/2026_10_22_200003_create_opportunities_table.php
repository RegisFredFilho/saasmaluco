<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_profile_id')->constrained('venue_profiles')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('location_label')->nullable();
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('currency', 3)->default('BRL');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('available_structure')->nullable();
            $table->unsignedTinyInteger('slots_count')->default(1);
            $table->date('application_deadline')->nullable();
            $table->string('status', 32)->default('draft'); // draft, open, closed, cancelled, filled
            $table->timestamps();

            $table->index(['venue_profile_id', 'status']);
            $table->index('event_date');
            $table->index('status');
        });

        Schema::create('opportunity_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->foreignId('genre_id')->constrained('genres')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['opportunity_id', 'genre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_genre');
        Schema::dropIfExists('opportunities');
    }
};
