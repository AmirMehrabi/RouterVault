<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wireless_client_management_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('wireless_client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_key');
            $table->string('action_label')->nullable();
            $table->string('status')->default('pending');
            $table->string('target_host')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('command_batch')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            // $table->index(['tenant_id', 'wireless_client_id']);
            // $table->index(['tenant_id', 'status']);
            // $table->index(['tenant_id', 'action_key']);
            // $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wireless_client_management_logs');
    }
};
