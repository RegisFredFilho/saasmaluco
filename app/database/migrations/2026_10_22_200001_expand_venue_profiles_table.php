<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venue_profiles', function (Blueprint $table) {
            $table->string('cover_path')->nullable()->after('is_public');
            $table->text('description')->nullable()->after('cover_path');
            $table->string('address_line', 255)->nullable()->after('description');
            $table->string('neighborhood', 120)->nullable()->after('address_line');
            $table->string('city', 120)->nullable()->after('neighborhood');
            $table->string('state', 120)->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('state');
            $table->unsignedInteger('capacity')->nullable()->after('postal_code');
            $table->string('event_type', 64)->nullable()->after('capacity');
            $table->text('stage_structure')->nullable()->after('event_type');
            $table->text('equipment')->nullable()->after('stage_structure');
            $table->text('technical_info')->nullable()->after('equipment');
            $table->decimal('budget_min', 12, 2)->nullable()->after('technical_info');
            $table->decimal('budget_max', 12, 2)->nullable()->after('budget_min');
            $table->string('currency', 3)->default('BRL')->after('budget_max');
            $table->string('contact_name', 120)->nullable()->after('currency');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone', 40)->nullable()->after('contact_email');

            $table->index(['city', 'state']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::table('venue_profiles', function (Blueprint $table) {
            $table->dropIndex(['city', 'state']);
            $table->dropIndex(['event_type']);
            $table->dropColumn([
                'cover_path',
                'description',
                'address_line',
                'neighborhood',
                'city',
                'state',
                'postal_code',
                'capacity',
                'event_type',
                'stage_structure',
                'equipment',
                'technical_info',
                'budget_min',
                'budget_max',
                'currency',
                'contact_name',
                'contact_email',
                'contact_phone',
            ]);
        });
    }
};
