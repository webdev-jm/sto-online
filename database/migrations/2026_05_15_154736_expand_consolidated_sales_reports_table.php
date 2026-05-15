<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consolidated_sales_reports', function (Blueprint $table) {
            $table->string('area')->nullable()->after('account_name');
            $table->string('salesman_code')->nullable()->after('brgy');
            $table->string('salesman_name')->nullable()->after('salesman_code');
            $table->string('salesman_type')->nullable()->after('salesman_name');
            $table->string('location_code')->nullable()->after('salesman_type');
            $table->string('location_name')->nullable()->after('location_code');
            $table->string('channel_code')->nullable()->after('location_name');
            $table->string('channel_name')->nullable()->after('channel_code');
            $table->tinyInteger('customer_status')->default(0)->after('channel_name');
            $table->timestamps();

            $table->unique(
                ['account_code', 'year', 'month', 'customer_code', 'stock_code', 'uom'],
                'uq_csr_upsert_key'
            );
            $table->index(['account_code', 'year', 'month'], 'idx_csr_account_year_month');
            $table->index(['year', 'month'], 'idx_csr_year_month');
        });
    }

    public function down(): void
    {
        Schema::table('consolidated_sales_reports', function (Blueprint $table) {
            $table->dropUnique('uq_csr_upsert_key');
            $table->dropIndex('idx_csr_account_year_month');
            $table->dropIndex('idx_csr_year_month');
            $table->dropColumn([
                'area', 'salesman_code', 'salesman_name', 'salesman_type',
                'location_code', 'location_name', 'channel_code', 'channel_name',
                'customer_status', 'created_at', 'updated_at',
            ]);
        });
    }
};
