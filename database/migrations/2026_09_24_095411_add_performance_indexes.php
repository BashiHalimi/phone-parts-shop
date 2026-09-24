<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sales — filtered by status + created_at in reports and dashboard
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'sales_status_created_at_index');
            $table->index('customer_id');
        });

        // Purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'purchases_status_created_at_index');
            $table->index('supplier_id');
        });

        // Sale items — grouped by product_id in top products
        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('sale_id');
        });

        // Stock movements — filtered by product_id + type + created_at
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['product_id', 'created_at'], 'stock_movements_product_created_index');
            $table->index(['type', 'created_at'], 'stock_movements_type_created_index');
        });

        // Expenses — filtered by date + category in reports
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['date', 'category'], 'expenses_date_category_index');
        });

        // Customers — searched by name + phone
        Schema::table('customers', function (Blueprint $table) {
            $table->index('name');
        });

        // Suppliers — searched by name + phone
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('name');
        });

        // Payments — indexed by payable + created_at for history
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['payable_type', 'payable_id', 'created_at'], 'payments_payable_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_status_created_at_index');
            $table->dropIndex(['customer_id']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex('purchases_status_created_at_index');
            $table->dropIndex(['supplier_id']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['sale_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_product_created_index');
            $table->dropIndex('stock_movements_type_created_index');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_date_category_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_payable_created_index');
        });
    }
};