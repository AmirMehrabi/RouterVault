<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('onboarding_step')->default('plan')->after('onboarding_completed');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_step');
        });

        DB::table('tenants')
            ->where('onboarding_completed', true)
            ->update([
                'onboarding_step' => 'complete',
                'onboarding_completed_at' => DB::raw('updated_at'),
            ]);

        DB::table('tenants')
            ->where('onboarding_completed', false)
            ->whereNotNull('saas_plan_id')
            ->where('subscription_status', 'active')
            ->update(['onboarding_step' => 'router']);

        DB::table('tenants')
            ->where('onboarding_completed', false)
            ->whereNotNull('saas_plan_id')
            ->where('subscription_status', 'pending')
            ->update(['onboarding_step' => 'payment']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['onboarding_step', 'onboarding_completed_at']);
        });
    }
};
