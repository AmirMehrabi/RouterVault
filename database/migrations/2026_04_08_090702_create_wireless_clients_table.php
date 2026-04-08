<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wireless_clients', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('access_point_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('router_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mac_address');
            $table->string('interface_name')->nullable();
            $table->string('radio_name')->nullable();
            $table->string('host_name')->nullable();
            $table->string('comment')->nullable();
            $table->string('ssid')->nullable();
            $table->string('band')->nullable();
            $table->unsignedInteger('frequency')->nullable();
            $table->integer('signal_strength')->nullable();
            $table->integer('signal_to_noise')->nullable();
            $table->string('tx_rate')->nullable();
            $table->string('rx_rate')->nullable();
            $table->unsignedInteger('tx_ccq')->nullable();
            $table->unsignedInteger('rx_ccq')->nullable();
            $table->string('uptime')->nullable();
            $table->string('last_ip_address')->nullable();
            $table->boolean('is_connected')->default(true);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_moved_at')->nullable();
            $table->json('last_snapshot')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'mac_address']);
            $table->index(['tenant_id', 'access_point_id']);
            $table->index(['tenant_id', 'site_id']);
            $table->index(['tenant_id', 'is_connected']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wireless_clients');
    }
};
