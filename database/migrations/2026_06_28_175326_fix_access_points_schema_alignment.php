<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('access_points', 'frequency_band') && ! Schema::hasColumn('access_points', 'band')) {
            DB::statement('ALTER TABLE access_points RENAME COLUMN frequency_band TO band');
        }

        if (Schema::hasColumn('access_points', 'connected_clients') && ! Schema::hasColumn('access_points', 'connected_clients_count')) {
            DB::statement('ALTER TABLE access_points RENAME COLUMN connected_clients TO connected_clients_count');
        }

        if (Schema::hasColumn('access_points', 'last_status_checked_at') && ! Schema::hasColumn('access_points', 'last_seen_at')) {
            DB::statement('ALTER TABLE access_points RENAME COLUMN last_status_checked_at TO last_seen_at');
        }

        $columns = Schema::getColumnListing('access_points');

        Schema::table('access_points', function (Blueprint $table) use ($columns) {
            if (! in_array('board_name', $columns, true)) {
                $table->string('board_name')->nullable()->after('model');
            }
            if (! in_array('architecture_name', $columns, true)) {
                $table->string('architecture_name')->nullable()->after('firmware_version');
            }
            if (! in_array('platform', $columns, true)) {
                $table->string('platform')->nullable()->after('architecture_name');
            }
            if (! in_array('frequency', $columns, true)) {
                $table->unsignedInteger('frequency')->nullable()->after('channel');
            }
            if (! in_array('location', $columns, true)) {
                $table->string('location')->nullable()->after('tx_power');
            }
            if (! in_array('cpu_usage', $columns, true)) {
                $table->unsignedInteger('cpu_usage')->default(0)->nullable();
            }
            if (! in_array('cpu_count', $columns, true)) {
                $table->unsignedInteger('cpu_count')->nullable();
            }
            if (! in_array('cpu_frequency', $columns, true)) {
                $table->unsignedInteger('cpu_frequency')->nullable();
            }
            if (! in_array('memory_usage', $columns, true)) {
                $table->unsignedInteger('memory_usage')->default(0)->nullable();
            }
            if (! in_array('total_memory', $columns, true)) {
                $table->unsignedBigInteger('total_memory')->nullable();
            }
            if (! in_array('free_memory', $columns, true)) {
                $table->unsignedBigInteger('free_memory')->nullable();
            }
            if (! in_array('total_hdd_space', $columns, true)) {
                $table->unsignedBigInteger('total_hdd_space')->nullable();
            }
            if (! in_array('free_hdd_space', $columns, true)) {
                $table->unsignedBigInteger('free_hdd_space')->nullable();
            }
            if (! in_array('signal_quality', $columns, true)) {
                $table->unsignedInteger('signal_quality')->default(0);
            }
            if (! in_array('noise_floor', $columns, true)) {
                $table->integer('noise_floor')->nullable();
            }
            if (! in_array('channel_utilization', $columns, true)) {
                $table->unsignedInteger('channel_utilization')->default(0);
            }
            if (! in_array('enable_monitoring', $columns, true)) {
                $table->boolean('enable_monitoring')->default(true);
            }
            if (! in_array('enable_provisioning', $columns, true)) {
                $table->boolean('enable_provisioning')->default(true);
            }
        });

        $obsoleteColumns = array_values(array_intersect([
            'serial_number',
            'antenna_type',
            'antenna_gain',
            'height_meters',
            'azimuth',
            'coverage_angle',
            'max_clients',
        ], Schema::getColumnListing('access_points')));

        if ($obsoleteColumns !== []) {
            Schema::table('access_points', function (Blueprint $table) use ($obsoleteColumns) {
                $table->dropColumn($obsoleteColumns);
            });
        }
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
