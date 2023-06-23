<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Spatie\Activitylog\Models\Activity;

class SystemlogController extends Controller
{
    public function index(Request $request) {
        $search = trim($request->get('search'));

        $activities = Activity::where('causer_id', auth()->user()->id)
            ->where('causer_type', 'App\Models\User')
            ->when(!empty($search), function($query) use($search) {
                $query->where(function($qry) use($search) {
                    $qry->where('description', 'like', '%'.$search.'%')
                    ->orWhere('log_name', 'like', '%'.$search.'%')
                    ->orWhere('properties', 'like', '%'.$search.'%')
                    ->orWhereHas('causer', function($qry1) use($search) {
                        $qry1->where('name', 'like', '%'.$search.'%');
                    });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'activity-page')
            ->onEachSide(1);

        return view('pages.systemlogs.index')->with([
            'activities' => $activities,
            'search' => $search
        ]);
    }
}
