<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wireless_clients', function (Blueprint $table) {
            $table->foreignId('password_manager_credential_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('password_manager_credentials')
                ->nullOnDelete();
            $table->string('provisioning_username')->nullable()->after('last_ip_address');
            $table->text('provisioning_password')->nullable()->after('provisioning_username');
        });
    }

    public function down(): void
    {
        Schema::table('wireless_clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('password_manager_credential_id');
            $table->dropColumn(['provisioning_username', 'provisioning_password']);
        });
    }
};
