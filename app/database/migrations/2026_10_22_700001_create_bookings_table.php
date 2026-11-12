<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained('proposals')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();
            $table->foreignId('venue_profile_id')->constrained('venue_profiles')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('BRL');
            $table->text('conditions')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('timezone', 64)->default('America/Sao_Paulo');
            $table->string('status', 32)->default('confirmed');
            $table->timestamp('artist_completed_at')->nullable();
            $table->timestamp('venue_completed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamps();

            $table->unique('proposal_id');
            $table->index(['artist_profile_id', 'starts_at', 'ends_at']);
            $table->index(['venue_profile_id', 'starts_at']);
            $table->index('status');
        });

        Schema::table('artist_experiences', function (Blueprint $table) {
            $table->foreign('booking_id')
                ->references('id')
                ->on('bookings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('artist_experiences', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
        });

        Schema::dropIfExists('bookings');
    }
};
