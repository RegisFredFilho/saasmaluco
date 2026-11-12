<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('followable_type');
            $table->unsignedBigInteger('followable_id');
            $table->timestamps();

            $table->unique(['user_id', 'followable_type', 'followable_id'], 'follows_user_target_unique');
            $table->index(['followable_type', 'followable_id'], 'follows_target_index');
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('favoritable_type');
            $table->unsignedBigInteger('favoritable_id');
            $table->timestamps();

            $table->unique(['user_id', 'favoritable_type', 'favoritable_id'], 'favorites_user_target_unique');
            $table->index(['favoritable_type', 'favoritable_id'], 'favorites_target_index');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('follows');
    }
};
