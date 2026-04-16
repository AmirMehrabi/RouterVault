<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wireless_clients', function (Blueprint $table) {
            $table->string('management_ip_address')->nullable()->after('last_ip_address');
            $table->unsignedInteger('management_port')->default(8728)->after('management_ip_address');
            $table->string('management_protocol')->default('routeros_api')->after('management_port');
            $table->string('device_identity')->nullable()->after('host_name');
            $table->string('device_mac_address')->nullable()->after('device_identity');
            $table->string('device_version')->nullable()->after('device_mac_address');
            $table->string('device_uptime')->nullable()->after('device_version');
            $table->string('pppoe_username')->nullable()->after('device_uptime');
            $table->timestamp('last_discovered_at')->nullable()->after('last_seen_at');
            $table->string('last_management_status')->nullable()->after('last_discovered_at');
            $table->text('last_management_message')->nullable()->after('last_management_status');
            $table->timestamp('last_management_ran_at')->nullable()->after('last_management_message');

            $table->index(['tenant_id', 'management_ip_address']);
            $table->index(['tenant_id', 'last_discovered_at']);
        });
    }

    public function down(): void
    {
        Schema::table('wireless_clients', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'management_ip_address']);
            $table->dropIndex(['tenant_id', 'last_discovered_at']);
            $table->dropColumn([
                'management_ip_address',
                'management_port',
                'management_protocol',
                'device_identity',
                'device_mac_address',
                'device_version',
                'device_uptime',
                'pppoe_username',
                'last_discovered_at',
                'last_management_status',
                'last_management_message',
                'last_management_ran_at',
            ]);
        });
    }
};
