<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE access_points RENAME COLUMN frequency_band TO band');
        DB::statement('ALTER TABLE access_points RENAME COLUMN connected_clients TO connected_clients_count');
        DB::statement('ALTER TABLE access_points RENAME COLUMN last_status_checked_at TO last_seen_at');

        Schema::table('access_points', function (Blueprint $table) {
            $table->string('board_name')->nullable()->after('model');
            $table->string('architecture_name')->nullable()->after('firmware_version');
            $table->string('platform')->nullable()->after('architecture_name');
            $table->unsignedInteger('frequency')->nullable()->after('channel');
            $table->string('location')->nullable()->after('tx_power');
            $table->unsignedInteger('cpu_usage')->default(0)->nullable();
            $table->unsignedInteger('cpu_count')->nullable();
            $table->unsignedInteger('cpu_frequency')->nullable();
            $table->unsignedInteger('memory_usage')->default(0)->nullable();
            $table->unsignedBigInteger('total_memory')->nullable();
            $table->unsignedBigInteger('free_memory')->nullable();
            $table->unsignedBigInteger('total_hdd_space')->nullable();
            $table->unsignedBigInteger('free_hdd_space')->nullable();
            $table->unsignedInteger('signal_quality')->default(0);
            $table->integer('noise_floor')->nullable();
            $table->unsignedInteger('channel_utilization')->default(0);
            $table->boolean('enable_monitoring')->default(true);
            $table->boolean('enable_provisioning')->default(true);
        });

        Schema::table('access_points', function (Blueprint $table) {
            $table->dropColumn(['serial_number', 'antenna_type', 'antenna_gain', 'height_meters', 'azimuth', 'coverage_angle', 'max_clients']);
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE access_points RENAME COLUMN band TO frequency_band');
        DB::statement('ALTER TABLE access_points RENAME COLUMN connected_clients_count TO connected_clients');
        DB::statement('ALTER TABLE access_points RENAME COLUMN last_seen_at TO last_status_checked_at');

        Schema::table('access_points', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->after('mac_address');
            $table->string('antenna_type')->nullable()->after('tx_power');
            $table->integer('antenna_gain')->nullable()->after('antenna_type');
            $table->decimal('height_meters', 5, 2)->nullable()->after('antenna_gain');
            $table->integer('azimuth')->nullable()->after('height_meters');
            $table->integer('coverage_angle')->nullable()->after('azimuth');
            $table->integer('max_clients')->default(0)->after('coverage_angle');
        });

        Schema::table('access_points', function (Blueprint $table) {
            $table->dropColumn([
                'board_name',
                'architecture_name',
                'platform',
                'frequency',
                'location',
                'cpu_usage',
                'cpu_count',
                'cpu_frequency',
                'memory_usage',
                'total_memory',
                'free_memory',
                'total_hdd_space',
                'free_hdd_space',
                'signal_quality',
                'noise_floor',
                'channel_utilization',
                'enable_monitoring',
                'enable_provisioning',
            ]);
        });
    }
};
