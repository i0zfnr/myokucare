<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('okus', function (Blueprint $table) {
            $table->string('residential_state', 100)->default('Terengganu')->after('address');
            $table->string('residential_district', 100)->default('Besut')->after('residential_state');
            $table->string('residential_mukim', 100)->nullable()->after('residential_district');
            $table->string('residential_village', 255)->nullable()->after('residential_mukim');
            $table->string('residential_postcode', 5)->nullable()->after('residential_village');
            $table->text('card_address')->nullable()->after('residential_postcode');
            $table->string('card_mukim', 100)->nullable()->after('card_address');
            $table->string('residence_verification_status', 30)->default('UNVERIFIED')->after('card_mukim');
            $table->text('residence_verification_notes')->nullable()->after('residence_verification_status');
            $table->timestamp('residence_verified_at')->nullable()->after('residence_verification_notes');
            $table->foreignId('residence_verified_by')->nullable()->after('residence_verified_at')->constrained('users')->nullOnDelete();
            $table->index(['residential_district', 'residential_mukim'], 'okus_residence_location_index');
            $table->index('residence_verification_status', 'okus_residence_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('okus', function (Blueprint $table) {
            $table->dropIndex('okus_residence_location_index');
            $table->dropIndex('okus_residence_status_index');
            $table->dropConstrainedForeignId('residence_verified_by');
            $table->dropColumn([
                'residential_state',
                'residential_district',
                'residential_mukim',
                'residential_village',
                'residential_postcode',
                'card_address',
                'card_mukim',
                'residence_verification_status',
                'residence_verification_notes',
                'residence_verified_at',
            ]);
        });
    }
};
