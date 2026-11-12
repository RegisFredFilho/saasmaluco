<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->string('stage_name')->nullable()->after('display_name');
            $table->string('photo_path')->nullable()->after('is_public');
            $table->string('cover_path')->nullable()->after('photo_path');
            $table->text('bio')->nullable()->after('cover_path');
            $table->string('city', 120)->nullable()->after('bio');
            $table->string('state', 120)->nullable()->after('city');
            $table->string('region', 120)->nullable()->after('state');
            $table->string('presentation_type', 64)->nullable()->after('region');
            $table->unsignedTinyInteger('members_count')->nullable()->after('presentation_type');
            $table->decimal('fee_min', 12, 2)->nullable()->after('members_count');
            $table->decimal('fee_max', 12, 2)->nullable()->after('fee_min');
            $table->string('currency', 3)->default('BRL')->after('fee_max');
            $table->unsignedSmallInteger('typical_duration_minutes')->nullable()->after('currency');
            $table->text('own_equipment')->nullable()->after('typical_duration_minutes');
            $table->text('required_tech')->nullable()->after('own_equipment');
            $table->text('repertoire')->nullable()->after('required_tech');
            $table->text('availability_notes')->nullable()->after('repertoire');

            $table->index(['city', 'state']);
            $table->index('presentation_type');
        });
    }

    public function down(): void
    {
        Schema::table('artist_profiles', function (Blueprint $table) {
            $table->dropIndex(['city', 'state']);
            $table->dropIndex(['presentation_type']);
            $table->dropColumn([
                'stage_name',
                'photo_path',
                'cover_path',
                'bio',
                'city',
                'state',
                'region',
                'presentation_type',
                'members_count',
                'fee_min',
                'fee_max',
                'currency',
                'typical_duration_minutes',
                'own_equipment',
                'required_tech',
                'repertoire',
                'availability_notes',
            ]);
        });
    }
};
