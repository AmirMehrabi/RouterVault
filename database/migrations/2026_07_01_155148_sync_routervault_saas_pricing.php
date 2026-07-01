<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ([
            'saas_free' => ['price' => 0, 'currency' => 'EUR', 'max_routers' => 1, 'backup_retention_days' => 7],
            'saas_starter' => ['price' => 9, 'currency' => 'EUR', 'max_routers' => 3, 'backup_retention_days' => 30],
            'saas_operator' => ['price' => 19, 'currency' => 'EUR', 'max_routers' => 10, 'backup_retention_days' => 180],
            'saas_extra_router' => ['price' => 2, 'currency' => 'EUR', 'max_routers' => 1],
        ] as $internalName => $values) {
            DB::table('plans')->where('internal_name', $internalName)->update($values);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plans')->whereIn('internal_name', [
            'saas_free',
            'saas_starter',
            'saas_operator',
            'saas_extra_router',
        ])->update(['currency' => 'USD']);

        DB::table('plans')->where('internal_name', 'saas_extra_router')->update(['price' => 1]);
    }
};
