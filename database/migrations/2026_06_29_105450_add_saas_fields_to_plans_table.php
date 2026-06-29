<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('max_routers')->default(0)->after('priority');
            $table->unsignedInteger('backup_retention_days')->default(7)->after('max_routers');
            $table->json('alert_channels')->nullable()->after('backup_retention_days');
            $table->unsignedInteger('max_users')->default(1)->after('alert_channels');
            $table->boolean('is_saas_plan')->default(false)->after('max_users');
            $table->boolean('is_extra_router')->default(false)->after('is_saas_plan');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'max_routers',
                'backup_retention_days',
                'alert_channels',
                'max_users',
                'is_saas_plan',
                'is_extra_router',
            ]);
        });
    }
};
