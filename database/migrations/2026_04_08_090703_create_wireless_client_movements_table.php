<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wireless_client_movements', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('wireless_client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_access_point_id')->nullable()->constrained('access_points')->nullOnDelete();
            $table->foreignId('to_access_point_id')->nullable()->constrained('access_points')->nullOnDelete();
            $table->foreignId('from_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('to_site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('from_router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->foreignId('to_router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->timestamp('moved_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'wireless_client_id']);
            $table->index(['tenant_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wireless_client_movements');
    }
};
