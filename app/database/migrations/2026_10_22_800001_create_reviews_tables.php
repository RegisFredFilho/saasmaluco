<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('author_side', 16); // artist | venue
            $table->string('rateable_type');
            $table->unsignedBigInteger('rateable_id');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('comment', 2000)->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->string('hidden_reason', 500)->nullable();
            $table->foreignId('hidden_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hidden_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'author_side']);
            $table->index(['rateable_type', 'rateable_id', 'is_hidden']);
            $table->index(['booking_id', 'author_user_id']);
        });

        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 500);
            $table->timestamps();

            $table->unique(['review_id', 'reporter_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_reports');
        Schema::dropIfExists('reviews');
    }
};
