<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateGenerateSalesReportStoredProceedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE PROCEDURE generate_sales_report(iAccount_id int, iAccount_branch_id int, iYear int, iMonth int)
            BEGIN
                -- CLEAR EXISTING DATA
                DELETE FROM sales_report WHERE account_id = iAccount_id AND account_branch_id = iAccount_branch_id AND year = iYear AND month = iMonth;

                -- INSERT NEW EXTACTED DATA
                INSERT INTO sales_report
                    SELECT
                        s.account_id,
                        s.account_branch_id,
                        s.customer_id,
                        YEAR(s.date) as year,
                        MONTH(s.date) as month,
                        p.id as product_id,
                        p.stock_code,
                        p.description,
                        p.size,
                        p.core_group as brand_classification,
                        p.brand,
                        p.category,
                        SUM(IF(s.category = 0 AND s.type = 1, s.quantity, NULL)) as quantity,
                        SUM(IF(s.category = 0 AND s.type = 1, s.amount_inc_vat, NULL)) as sales,
                        SUM(IF(s.category = 0 AND s.type = 2, s.quantity, NULL)) as fg_quantity,
                        SUM(IF(s.category = 0 AND s.type = 2, s.amount_inc_vat, NULL)) as fg_sales,
                        SUM(IF(s.category = 0 AND s.type = 3, s.quantity, NULL)) as promo_quantity,
                        SUM(IF(s.category = 0 AND s.type = 3, s.amount_inc_vat, NULL)) as promo_sales,
                        SUM(ABS(IF(s.category = 1, s.amount_inc_vat, 0))) as credit_memo,
                        0 as parked_quantity,
                        0 as parked_amount
                    FROM
                        sales as s
                    LEFT JOIN
                        '.env('DB_DATABASE_2').'.products as p ON p.id = s.product_id
                    LEFT JOIN
                        customers c ON c.id = s.customer_id
                    WHERE
                            s.account_id = iAccount_id
                        AND
                            s.account_branch_id = iAccount_branch_id
                        AND
                            YEAR(s.date) = iYear
                        AND
                            MONTH(s.date) = iMonth
                        AND
                            s.deleted_at IS NULL
                    GROUP BY
                        account_id, account_branch_id, customer_id, year, month, p.id, p.stock_code, p.description, p.size, brand_classification, brand, category;
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generate_sales_report_stored_proceedure');
    }
}
