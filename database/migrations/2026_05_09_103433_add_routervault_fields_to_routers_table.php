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
        Schema::table('routers', function (Blueprint $table) {
            $table->boolean('use_ssl')->default(false)->after('api_port');
            $table->boolean('legacy_login')->default(false)->after('use_ssl');
            $table->timestamp('last_checked_at')->nullable()->after('status');
            $table->timestamp('last_connected_at')->nullable()->after('last_checked_at');
            $table->text('last_error')->nullable()->after('last_connected_at');
            $table->string('ssh_auth_method')->default('private_key')->after('ssh_port');
            $table->text('ssh_private_key')->nullable()->after('ssh_auth_method');
            $table->unsignedInteger('ssh_timeout')->default(30)->after('ssh_private_key');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE routers MODIFY status ENUM('pending', 'online', 'offline') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE routers MODIFY status ENUM('online', 'offline') NOT NULL DEFAULT 'offline'");
        }

        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn([
                'use_ssl',
                'legacy_login',
                'last_checked_at',
                'last_connected_at',
                'last_error',
                'ssh_auth_method',
                'ssh_private_key',
                'ssh_timeout',
            ]);
        });
    }
};
