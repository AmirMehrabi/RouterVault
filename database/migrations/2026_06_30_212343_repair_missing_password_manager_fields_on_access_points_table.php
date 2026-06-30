<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('access_points', 'password_manager_credential_id')) {
            Schema::table('access_points', function (Blueprint $table) {
                $table->foreignId('password_manager_credential_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('password_manager_credentials')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('access_points', 'api_username')) {
            Schema::table('access_points', function (Blueprint $table) {
                $table->string('api_username')->nullable()->after('ip_address');
            });
        }

        if (! Schema::hasColumn('access_points', 'api_password')) {
            Schema::table('access_points', function (Blueprint $table) {
                $table->text('api_password')->nullable()->after('api_username');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
