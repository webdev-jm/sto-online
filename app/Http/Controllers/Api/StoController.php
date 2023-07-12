<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SalesView;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class StoController extends Controller
{
    public function index(Request $request) {

        $year = $request->year;
        $prev_year = $year - 1;
        $start_month = $request->start_month;
        $end_month = $request->end_month;

        $filters = $request->filters;

        $validator = Validator::make($request->all(), [
            'year' => 'required',
            'start_month' => 'required',
            'end_month' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $divisor = DB::connection('mysql')
            ->table('sto_online.sales_view')
            ->select('month')->distinct()
            ->where('year', $year)
            ->where('month', '>=', $start_month)
            ->where('month', '<=', $end_month)
            ->count('month');

        $sales = DB::connection('mysql')
            ->table('sto_online.sales_view')
            ->select(
                'month',
                DB::raw('SUM(IF(year = '.$prev_year.', sales, NULL)) as previous'),
                DB::raw('SUM(IF(year = '.$year.', sales, NULL)) as current'),
            )
            ->where('month', '>=', $start_month)
            ->where('month', '<=', $end_month)
            ->groupBy('month')
            ->orderBy('month', 'ASC');

        $sql = 'not accessible';
        if(env('API_VIEW_QUERY')) {
            $sql = $sales->toSql();
        }

        $data = $sales->get();

        return response()->json([
            'success' => true,
            'param' => [
                'year' => $year,
                'start_month' => $start_month,
                'end_month' => $end_month
            ],
            'sql' => $sql,
            'data' => $data
        ]);
    }

    public function areas(Request $request) {
        $year = $request->year;
        $prev_year = $year - 1;
        $start_month = $request->start_month;
        $end_month = $request->end_month;

        $filters = $request->filters;

        $validator = Validator::make($request->all(), [
            'year' => 'required',
            'start_month' => 'required',
            'end_month' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        
    }
}
