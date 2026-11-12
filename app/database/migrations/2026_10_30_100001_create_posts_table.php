<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('publisher');
            $table->text('body');
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // morphs() já cria publisher_type + publisher_id index; composto para feed.
            $table->index(['publisher_type', 'publisher_id', 'created_at'], 'posts_publisher_created_idx');
            $table->index(['is_public', 'created_at'], 'posts_public_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
