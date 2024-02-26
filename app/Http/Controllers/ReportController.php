<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index() {
        $results = DB::table('sales as s')
            ->select(
                'c.name',
                'c.code',
                DB::raw('COUNT(DISTINCT s.document_number) as UBO'),
                DB::raw('SUM(amount_inc_vat) as STO'),
                DB::raw('YEAR(date) as year'),
                DB::raw('MONTH(date) as month'),   
            )
            ->leftJoin('customers as c', 'c.id', '=', 's.customer_id')
            ->where('s.account_id', 245)
            ->where('category', 0)
            ->groupBy('c.name', 'c.code', 'year', 'month')
            ->get();

        $data = array();
        $total_sto = 0;
        $total_sto_count = 0;
        $min_ubo = NULL;
        $max_ubo = NULL;
        foreach($results as $result) {
            $data[] = [
                $result->UBO,
                (float)$result->STO
            ];

             // Update min UBO
            if ($min_ubo === null || $result->UBO < $min_ubo) {
                $min_ubo = $result->UBO;
            }

            // Update max UBO
            if ($max_ubo === null || $result->UBO > $max_ubo) {
                $max_ubo = $result->UBO;
            }

            $total_sto += (float)$result->STO;
            $total_sto_count++;
        }

        $ave = $total_sto / $total_sto_count;

        $line_data = [
            [
                $min_ubo - 0.5,
                $ave,
            ],
            [
                $max_ubo + 0.5,
                $ave,
            ]
        ];

        return view('pages.reports.index')->with([
            'data' => $data,
            'line_data' => $line_data
        ]);
    }
}
