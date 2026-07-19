<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Upgrade invoices: link to customers table, add payment info
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('shop_id');
            $table->string('payment_method')->default('cash')->after('final_bill'); // cash | card | transfer | mixed
            $table->string('payment_status')->default('paid')->after('payment_method'); // paid | unpaid | partial
            $table->decimal('amount_paid', 12, 2)->default(0)->after('payment_status');
            $table->decimal('change_amount', 10, 2)->default(0)->after('amount_paid');
            $table->string('status')->default('completed')->after('change_amount'); // completed | voided | refunded

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });

        // sale_details: line-level breakdown (replaces / extends existing sales rows)
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);               // Price at time of sale
            $table->decimal('unit_cost', 10, 2)->nullable();    // Cost at time of sale (for profit calc)
            $table->decimal('discount', 10, 2)->default(0);     // Per-line discount
            $table->decimal('subtotal', 12, 2);
            $table->integer('warranty_months')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_details');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'payment_method', 'payment_status', 'amount_paid', 'change_amount', 'status']);
        });
    }
};
