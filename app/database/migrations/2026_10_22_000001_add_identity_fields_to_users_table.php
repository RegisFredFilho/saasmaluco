<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('password');
            $table->string('city', 120)->nullable()->after('avatar_path');
            $table->string('state', 120)->nullable()->after('city');
            $table->string('country', 120)->nullable()->after('state');
            $table->json('preferences')->nullable()->after('country');
            $table->boolean('is_platform_admin')->default(false)->after('preferences');
            $table->timestamp('deactivated_at')->nullable()->after('is_platform_admin');
            $table->string('deactivation_reason', 500)->nullable()->after('deactivated_at');

            $table->index('deactivated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['deactivated_at']);
            $table->dropColumn([
                'avatar_path',
                'city',
                'state',
                'country',
                'preferences',
                'is_platform_admin',
                'deactivated_at',
                'deactivation_reason',
            ]);
        });
    }
};
