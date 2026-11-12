<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->index('is_public');
            $table->index(['is_public', 'city', 'state'], 'artist_profiles_discover_geo_index');
            $table->index('kind');
            $table->index('region');
        });

        Schema::table('venue_profiles', function (Blueprint $table) {
            $table->index('is_public');
            $table->index(['is_public', 'city', 'state'], 'venue_profiles_discover_geo_index');
        });

        Schema::table('opportunities', function (Blueprint $table) {
            $table->index(['status', 'event_date'], 'opportunities_discover_status_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->dropIndex(['is_public']);
            $table->dropIndex('artist_profiles_discover_geo_index');
            $table->dropIndex(['kind']);
            $table->dropIndex(['region']);
        });

        Schema::table('venue_profiles', function (Blueprint $table) {
            $table->dropIndex(['is_public']);
            $table->dropIndex('venue_profiles_discover_geo_index');
        });

        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropIndex('opportunities_discover_status_date_index');
        });
    }
};
