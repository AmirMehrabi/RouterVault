<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedBigInteger('saas_plan_id')->nullable()->after('plan_id');
            $table->string('subscription_status')->default('trial')->after('saas_plan_id');
            $table->datetime('subscription_starts_at')->nullable()->after('subscription_status');
            $table->datetime('subscription_expires_at')->nullable()->after('subscription_starts_at');
            $table->unsignedInteger('extra_routers_count')->default(0)->after('subscription_expires_at');
            $table->datetime('next_billing_at')->nullable()->after('extra_routers_count');
            $table->boolean('onboarding_completed')->default(false)->after('next_billing_at');
            $table->foreign('saas_plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['saas_plan_id']);
            $table->dropColumn([
                'saas_plan_id',
                'subscription_status',
                'subscription_starts_at',
                'subscription_expires_at',
                'extra_routers_count',
                'next_billing_at',
                'onboarding_completed',
            ]);
        });
    }
};
