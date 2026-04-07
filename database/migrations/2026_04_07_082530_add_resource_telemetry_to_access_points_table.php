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
        Schema::table('access_points', function (Blueprint $table) {
            $table->string('board_name')->nullable()->after('model');
            $table->string('architecture_name')->nullable()->after('firmware_version');
            $table->string('platform')->nullable()->after('architecture_name');
            $table->unsignedInteger('cpu_count')->nullable()->after('cpu_usage');
            $table->unsignedInteger('cpu_frequency')->nullable()->after('cpu_count');
            $table->unsignedBigInteger('total_memory')->nullable()->after('memory_usage');
            $table->unsignedBigInteger('free_memory')->nullable()->after('total_memory');
            $table->unsignedBigInteger('total_hdd_space')->nullable()->after('free_memory');
            $table->unsignedBigInteger('free_hdd_space')->nullable()->after('total_hdd_space');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_points', function (Blueprint $table) {
            $table->dropColumn([
                'board_name',
                'architecture_name',
                'platform',
                'cpu_count',
                'cpu_frequency',
                'total_memory',
                'free_memory',
                'total_hdd_space',
                'free_hdd_space',
            ]);
        });
    }
};
