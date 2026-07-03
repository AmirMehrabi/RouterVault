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
        Schema::create('system_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->string('node')->default('default');
            $table->string('status')->default('healthy');
            $table->json('metadata')->nullable();
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique(['service', 'node']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_heartbeats');
    }
};
