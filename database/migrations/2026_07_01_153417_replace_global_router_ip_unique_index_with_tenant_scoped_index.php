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
        Schema::table('routers', function (Blueprint $table) {
            $table->dropUnique('routers_ip_address_unique');
            $table->unique(['tenant_id', 'ip_address'], 'routers_tenant_ip_address_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropUnique('routers_tenant_ip_address_unique');
            $table->unique('ip_address');
        });
    }
};
