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
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('interval_value');
            $table->string('interval_unit');
            $table->string('timezone')->default('UTC');
            $table->unsignedInteger('retention_count')->default(30);
            $table->string('export_mode')->default('full');
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_status')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'is_enabled', 'next_run_at']);
        });

        Schema::create('backup_schedule_router', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['backup_schedule_id', 'router_id']);
        });

        Schema::create('backup_runs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('backup_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('trigger');
            $table->string('status')->default('queued');
            $table->unsignedInteger('total_routers')->default(0);
            $table->unsignedInteger('successful_backups')->default(0);
            $table->unsignedInteger('failed_backups')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_summary')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('router_backups', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->foreignId('backup_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('backup_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('previous_router_backup_id')->nullable()->constrained('router_backups')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->boolean('changed')->nullable();
            $table->string('disk')->default('local');
            $table->string('path')->nullable();
            $table->string('checksum')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('routeros_version')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'router_id', 'status']);
        });

        Schema::create('router_backup_diffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('router_backup_id')->constrained('router_backups')->cascadeOnDelete();
            $table->foreignId('previous_router_backup_id')->constrained('router_backups')->cascadeOnDelete();
            $table->unsignedInteger('added_lines')->default(0);
            $table->unsignedInteger('removed_lines')->default(0);
            $table->longText('unified_diff');
            $table->json('hunks')->nullable();
            $table->timestamps();
        });

        Schema::create('diff_alert_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('ignore_blank_lines')->default(true);
            $table->json('ignored_sections')->nullable();
            $table->json('ignored_keywords')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique('tenant_id');
        });

        Schema::create('diff_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->foreignId('router_backup_id')->constrained('router_backups')->cascadeOnDelete();
            $table->foreignId('previous_router_backup_id')->constrained('router_backups')->cascadeOnDelete();
            $table->foreignId('router_backup_diff_id')->constrained('router_backup_diffs')->cascadeOnDelete();
            $table->string('severity')->default('low');
            $table->string('status')->default('unread');
            $table->string('summary');
            $table->json('sections')->nullable();
            $table->json('matched_ignored_patterns')->nullable();
            $table->unsignedInteger('added_lines')->default(0);
            $table->unsignedInteger('removed_lines')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status', 'severity']);
        });

        Schema::create('diff_alert_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diff_alert_id')->constrained()->cascadeOnDelete();
            $table->string('tenant_id');
            $table->text('body');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diff_alert_notes');
        Schema::dropIfExists('diff_alerts');
        Schema::dropIfExists('diff_alert_settings');
        Schema::dropIfExists('router_backup_diffs');
        Schema::dropIfExists('router_backups');
        Schema::dropIfExists('backup_runs');
        Schema::dropIfExists('backup_schedule_router');
        Schema::dropIfExists('backup_schedules');
    }
};
