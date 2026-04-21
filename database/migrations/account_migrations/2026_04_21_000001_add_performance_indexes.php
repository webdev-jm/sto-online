<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index('account_branch_id', 'sales_account_branch_id_index');
            $table->index('account_id', 'sales_account_id_index');
            $table->index('customer_id', 'sales_customer_id_index');
            $table->index('product_id', 'sales_product_id_index');
            $table->index('salesman_id', 'sales_salesman_id_index');
            $table->index('location_id', 'sales_location_id_index');
            $table->index('date', 'sales_date_index');
            $table->index(['account_branch_id', 'date'], 'sales_branch_date_index');
            $table->index(['account_branch_id', 'product_id'], 'sales_branch_product_index');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->index('account_branch_id', 'inventories_account_branch_id_index');
            $table->index('account_id', 'inventories_account_id_index');
            $table->index('inventory_upload_id', 'inventories_upload_id_index');
            $table->index('product_id', 'inventories_product_id_index');
            $table->index('location_id', 'inventories_location_id_index');
            $table->index(['account_branch_id', 'inventory_upload_id'], 'inventories_branch_upload_index');
        });

        Schema::table('monthly_inventories', function (Blueprint $table) {
            $table->index('account_branch_id', 'monthly_inv_account_branch_id_index');
            $table->index('account_id', 'monthly_inv_account_id_index');
            $table->index('product_id', 'monthly_inv_product_id_index');
            $table->index('location_id', 'monthly_inv_location_id_index');
            $table->index(['account_branch_id', 'year', 'month'], 'monthly_inv_branch_year_month_index');
            $table->index(['account_branch_id', 'product_id'], 'monthly_inv_branch_product_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('account_branch_id', 'customers_account_branch_id_index');
            $table->index('account_id', 'customers_account_id_index');
            $table->index('salesman_id', 'customers_salesman_id_index');
            $table->index('channel_id', 'customers_channel_id_index');
            $table->index(['account_branch_id', 'code'], 'customers_branch_code_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_account_branch_id_index');
            $table->dropIndex('sales_account_id_index');
            $table->dropIndex('sales_customer_id_index');
            $table->dropIndex('sales_product_id_index');
            $table->dropIndex('sales_salesman_id_index');
            $table->dropIndex('sales_location_id_index');
            $table->dropIndex('sales_date_index');
            $table->dropIndex('sales_branch_date_index');
            $table->dropIndex('sales_branch_product_index');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex('inventories_account_branch_id_index');
            $table->dropIndex('inventories_account_id_index');
            $table->dropIndex('inventories_upload_id_index');
            $table->dropIndex('inventories_product_id_index');
            $table->dropIndex('inventories_location_id_index');
            $table->dropIndex('inventories_branch_upload_index');
        });

        Schema::table('monthly_inventories', function (Blueprint $table) {
            $table->dropIndex('monthly_inv_account_branch_id_index');
            $table->dropIndex('monthly_inv_account_id_index');
            $table->dropIndex('monthly_inv_product_id_index');
            $table->dropIndex('monthly_inv_location_id_index');
            $table->dropIndex('monthly_inv_branch_year_month_index');
            $table->dropIndex('monthly_inv_branch_product_index');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_account_branch_id_index');
            $table->dropIndex('customers_account_id_index');
            $table->dropIndex('customers_salesman_id_index');
            $table->dropIndex('customers_channel_id_index');
            $table->dropIndex('customers_branch_code_index');
        });
    }
};
