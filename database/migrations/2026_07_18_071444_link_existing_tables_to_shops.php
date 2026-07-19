<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This migration adds a proper reference from existing tables to the new `shops` table.
 * The existing migrations use `users` as the shop table (legacy); this adds a
 * `proper_shop_id` column pointing to `shops` alongside the legacy `shop_id → users`.
 *
 * For NEW installs you can run all migrations fresh and this just adds the column.
 * For existing deployments with data, you would populate proper_shop_id from shops
 * after creating matching shop records.
 */
return new class extends Migration
{
    private array $tables = ['products', 'invoices', 'sales', 'claims', 'stocks', 'purchases'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'shop_ref_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    // New column points to shops; nullable during migration period
                    $blueprint->unsignedBigInteger('shop_ref_id')
                              ->nullable()
                              ->after('shop_id')
                              ->comment('FK to shops.id — use this going forward');

                    $blueprint->foreign('shop_ref_id')
                              ->references('id')
                              ->on('shops')
                              ->onDelete('cascade');
                });
            }
        }

        // Also add shop_ref_id to shop_admins
        if (Schema::hasTable('shop_admins') && !Schema::hasColumn('shop_admins', 'shop_ref_id')) {
            Schema::table('shop_admins', function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('shop_ref_id')->nullable()->after('shop_id');
                $blueprint->foreign('shop_ref_id')->references('id')->on('shops')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        $allTables = array_merge($this->tables, ['shop_admins']);
        foreach ($allTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'shop_ref_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropForeign(['shop_ref_id']);
                    $blueprint->dropColumn('shop_ref_id');
                });
            }
        }
    }
};
