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
        Schema::create('configuration_baselines', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('router_id')->constrained()->cascadeOnDelete();
            $table->foreignId('router_backup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('label')->default('Approved baseline');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique('router_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_baselines');
    }
};
