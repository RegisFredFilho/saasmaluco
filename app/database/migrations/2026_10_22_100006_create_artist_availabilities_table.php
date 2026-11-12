<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_profile_id')->constrained('artist_profiles')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_available')->default(true);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['artist_profile_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_availabilities');
    }
};
