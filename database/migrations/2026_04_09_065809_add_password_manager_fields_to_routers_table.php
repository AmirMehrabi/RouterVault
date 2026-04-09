<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->foreignId('password_manager_credential_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('password_manager_credentials')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('password_manager_credential_id');
        });
    }
};
