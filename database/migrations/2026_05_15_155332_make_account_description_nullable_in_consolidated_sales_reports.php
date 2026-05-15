<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consolidated_sales_reports', function (Blueprint $table) {
            $table->string('account_description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('consolidated_sales_reports', function (Blueprint $table) {
            $table->string('account_description')->nullable(false)->default('')->change();
        });
    }
};
