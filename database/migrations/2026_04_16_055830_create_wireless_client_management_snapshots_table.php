<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wireless_client_management_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('wireless_client_id')->constrained()->cascadeOnDelete();
            $table->string('action_key')->nullable();
            $table->string('snapshot_type')->default('discovery');
            $table->json('payload');
            $table->timestamp('collected_at');
            $table->timestamps();

            // $table->index(['tenant_id', 'wireless_client_id']);
            // $table->index(['tenant_id', 'snapshot_type']);
            // $table->index(['tenant_id', 'collected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wireless_client_management_snapshots');
    }
};
