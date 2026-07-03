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
        Schema::create('compliance_findings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->foreignId('router_backup_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rule_key');
            $table->string('rule_name');
            $table->string('status');
            $table->text('summary');
            $table->text('remediation')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['router_id', 'rule_key']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_findings');
    }
};
