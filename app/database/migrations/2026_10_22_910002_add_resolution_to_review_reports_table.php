<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_reports', function (Blueprint $table) {
            $table->timestamp('resolved_at')->nullable()->after('reason');
            $table->foreignId('resolved_by_user_id')->nullable()->after('resolved_at')
                ->constrained('users')->nullOnDelete();
            $table->string('resolution', 32)->nullable()->after('resolved_by_user_id');
            $table->string('resolution_note', 500)->nullable()->after('resolution');

            $table->index(['resolved_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('review_reports', function (Blueprint $table) {
            $table->dropIndex(['resolved_at', 'created_at']);
            $table->dropConstrainedForeignId('resolved_by_user_id');
            $table->dropColumn(['resolved_at', 'resolution', 'resolution_note']);
        });
    }
};
