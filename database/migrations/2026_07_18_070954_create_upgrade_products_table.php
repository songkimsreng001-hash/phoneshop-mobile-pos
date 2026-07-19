<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add brand, category, supplier references
            $table->unsignedBigInteger('brand_id')->nullable()->after('shop_id');
            $table->unsignedBigInteger('category_id')->nullable()->after('brand_id');
            $table->unsignedBigInteger('supplier_id')->nullable()->after('category_id');

            // Additional professional fields
            $table->string('sku')->nullable()->unique()->after('name');
            $table->string('barcode')->nullable()->after('sku');
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');  // Purchase cost
            $table->integer('reorder_level')->default(5)->after('qty');        // Low-stock alert threshold

            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['brand_id', 'category_id', 'supplier_id', 'sku', 'barcode', 'cost_price', 'reorder_level']);
        });
    }
};
