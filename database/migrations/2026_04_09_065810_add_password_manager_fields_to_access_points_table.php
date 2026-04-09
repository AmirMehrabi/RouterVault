<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_points', function (Blueprint $table) {
            $table->foreignId('password_manager_credential_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('password_manager_credentials')
                ->nullOnDelete();
            $table->string('api_username')->nullable()->after('ip_address');
            $table->text('api_password')->nullable()->after('api_username');
        });
    }

    public function down(): void
    {
        Schema::table('access_points', function (Blueprint $table) {
            $table->dropConstrainedForeignId('password_manager_credential_id');
            $table->dropColumn(['api_username', 'api_password']);
        });
    }
};
