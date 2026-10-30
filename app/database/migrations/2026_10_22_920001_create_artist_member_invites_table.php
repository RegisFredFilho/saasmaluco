<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_member_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();
            $table->foreignId('invited_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invited_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 32); // admin, member
            $table->string('status', 32)->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // Nome explícito: o auto-gerado ultrapassa o limite de 64 chars do MySQL.
            $table->index(
                ['artist_profile_id', 'invited_user_id', 'status'],
                'ami_profile_user_status_idx'
            );
            $table->index(['invited_user_id', 'status'], 'ami_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_member_invites');
    }
};
