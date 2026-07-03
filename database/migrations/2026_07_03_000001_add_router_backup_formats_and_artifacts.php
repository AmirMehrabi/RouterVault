<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table): void {
            $table->boolean('backup_rsc_enabled')->default(true)->after('enable_ssh');
            $table->boolean('backup_binary_enabled')->default(false)->after('backup_rsc_enabled');
        });

        Schema::create('router_backup_artifacts', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('router_backup_id')->constrained('router_backups')->cascadeOnDelete();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->string('disk')->default('local');
            $table->string('path')->nullable();
            $table->string('checksum')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->text('error_message')->nullable();
            $table->text('cleanup_error')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['router_backup_id', 'type']);
            $table->index(['tenant_id', 'type', 'status']);
        });

        DB::table('router_backups')
            ->whereNotNull('path')
            ->orderBy('id')
            ->chunkById(250, function ($backups): void {
                $now = now();
                $rows = $backups->map(fn ($backup): array => [
                    'tenant_id' => $backup->tenant_id,
                    'router_backup_id' => $backup->id,
                    'type' => 'rsc',
                    'status' => $backup->status,
                    'disk' => $backup->disk,
                    'path' => $backup->path,
                    'checksum' => $backup->checksum,
                    'size_bytes' => $backup->size_bytes,
                    'error_message' => $backup->error_message,
                    'cleanup_error' => null,
                    'created_at' => $backup->created_at ?? $now,
                    'updated_at' => $backup->updated_at ?? $now,
                ])->all();

                DB::table('router_backup_artifacts')->insert($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_backup_artifacts');

        Schema::table('routers', function (Blueprint $table): void {
            $table->dropColumn(['backup_rsc_enabled', 'backup_binary_enabled']);
        });
    }
};
