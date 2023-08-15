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

        if(!empty($filters)) {
            // ACCOUNT
            if(!empty($filters['accounts'])) {

            }
            // AREA
            if(!empty($filters['areas'])) {

            }
            // CLASSIFICATION
            if(!empty($filters['classifications'])) {

            }
            // CORE
            if(!empty($filters['cores'])) {

            }
            // UNIT
            if(!empty($filters['units'])) {

            }
            // MONTH
            if(!empty($filters['months'])) {

            }
            // BRAND
            if(!empty($filters['brands'])) {

            }
        }

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
            // ACCOUNT
            ->when(!empty($filters['accounts']), function($query) use($filters) {
                $query->whereIn('account_code', $filters['accounts']);
            })
            // AREA
            ->when(!empty($filters['areas']), function($query) use($filters) {
                $query->whereIn('area_code', $filters['areas']);
            })
            // CLASSIFICATION
            ->when(!empty($filters['classifications']), function($query) use($filters) {
                $query->whereIn('channel_code', $filters['classifications']);
            })
            // CORE
            ->when(!empty($filters['cores']), function($query) use($filters) {
                $query->whereIn('brand_classification', $filters['cores']);
            })
            // UNIT
            ->when(!empty($filters['units']), function($query) use($filters) {
                $query->whereIn('vendor', $filters['units']);
            })
            // MONTH
            ->when(!empty($filters['months']), function($query) use($filters) {
                $query->whereIn('month', $filters['months']);
            })
            // BRAND
            ->when(!empty($filters['brands']), function($query) use($filters) {
                $query->whereIn('brand', $filters['brands']);
            })
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
            // ACCOUNT
            ->when(!empty($filters['accounts']), function($query) use($filters) {
                $query->whereIn('account_code', $filters['accounts']);
            })
            // AREA
            ->when(!empty($filters['areas']), function($query) use($filters) {
                $query->whereIn('area_code', $filters['areas']);
            })
            // CLASSIFICATION
            ->when(!empty($filters['classifications']), function($query) use($filters) {
                $query->whereIn('channel_code', $filters['classifications']);
            })
            // CORE
            ->when(!empty($filters['cores']), function($query) use($filters) {
                $query->whereIn('brand_classification', $filters['cores']);
            })
            // UNIT
            ->when(!empty($filters['units']), function($query) use($filters) {
                $query->whereIn('vendor', $filters['units']);
            })
            // MONTH
            ->when(!empty($filters['months']), function($query) use($filters) {
                $query->whereIn('month', $filters['months']);
            })
            // BRAND
            ->when(!empty($filters['brands']), function($query) use($filters) {
                $query->whereIn('brand', $filters['brands']);
            })
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

        $divisor = DB::connection('mysql')
            ->table('sto_online.sales_view')
            ->select('month')->distinct()
            ->where('year', $year)
            ->where('month', '>=', $start_month)
            ->where('month', '<=', $end_month)
            ->count('month');

        DB::statement('SET sql_mode=(SELECT REPLACE(@@sql_mode,"ONLY_FULL_GROUP_BY",""));');

        $sales = DB::table('sto_online.sales_view')
            ->select(
                'area_code',
                'area_name',
                DB::raw('SUM(IF(year = '.$prev_year.', sales, NULL)) as fy_prev'),
                DB::raw('SUM(IF(year = '.$prev_year.', sales, NULL)) / '.$divisor.' as avg_prev'),
                DB::raw('SUM(IF(year = '.$year.', sales, NULL)) as fy_current'),
                DB::raw('SUM(IF(year = '.$year.', sales, NULL)) / '.$divisor.' as avg_current')
            )
            ->groupBy(['area_code', 'area_name']);

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
}
