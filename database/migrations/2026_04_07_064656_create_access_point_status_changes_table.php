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
        Schema::create('access_point_status_changes', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('access_point_id')->constrained()->cascadeOnDelete();
            $table->string('previous_status')->nullable();
            $table->string('current_status');
            $table->string('reason')->nullable();
            $table->timestamp('checked_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'access_point_id']);
            $table->index(['tenant_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_point_status_changes');
    }
};
