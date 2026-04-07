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
        Schema::create('access_points', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('router_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('vendor')->default('Mikrotik');
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('ssid')->nullable();
            $table->enum('band', ['2.4GHz', '5GHz', '6GHz', 'dual'])->default('dual');
            $table->string('channel')->nullable();
            $table->unsignedInteger('frequency')->nullable();
            $table->unsignedInteger('tx_power')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline');
            $table->string('firmware_version')->nullable();
            $table->string('uptime')->nullable();
            $table->unsignedInteger('cpu_usage')->default(0);
            $table->unsignedInteger('memory_usage')->default(0);
            $table->unsignedInteger('connected_clients_count')->default(0);
            $table->unsignedInteger('signal_quality')->default(0);
            $table->integer('noise_floor')->nullable();
            $table->unsignedInteger('channel_utilization')->default(0);
            $table->boolean('enable_monitoring')->default(true);
            $table->boolean('enable_provisioning')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'ip_address']);
            $table->unique(['tenant_id', 'mac_address']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'router_id']);
            $table->index(['tenant_id', 'site_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_points');
    }
};
