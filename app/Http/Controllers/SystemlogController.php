<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Spatie\Activitylog\Models\Activity;

class SystemlogController extends Controller
{
    public function index(Request $request) {
        $search = trim($request->get('search') ?? '');

        $activities = Activity::with('causer')
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

            $updates = [];
            foreach($activities as $activity) {
                if($activity->log_name == 'update') {
                    $old = $activity->properties['old'];
                    $changes = $activity->properties['changes'];
                    
                    // Check if the model exists in the array
                    $models = [
                        'user_id' => [
                            'class'     => \App\Models\User::class,
                            'column'    => 'name'
                        ]
                    ];
    
                    foreach($changes as $key => $update) {
                        if($key === 'updated_at') {
                            continue;
                        }
            
                        $old_val = $old[$key];
                        $new_val = $update;
            
                        if (isset($models[$key])) {
                            $class = $models[$key]['class'];
                            $column = $models[$key]['column'];
            
                            // Check if old value exists
                            $old_val = !empty($old_val)
                                ? cache()->rememberForever("$class:$old_val", function () use ($class, $old_val) {
                                    return $class::find($old_val)[$column] ?? '-';
                                })
                                : '-';
            
                            // Check if new value exists
                            $new_val = !empty($new_val)
                                ? cache()->rememberForever("$class:$new_val", function () use ($class, $new_val) {
                                    return $class::find($new_val)[$column] ?? '-';
                                })
                                : '-';
                        }
            
                        if($key === 'arr') {
                            $updates[$activity->id][$key] = [
                                'old' => implode(', ', $old_val),
                                'new' => implode(', ', $new_val)
                            ];
                        } else {
                            $updates[$activity->id][$key] = [
                                'old' => $old_val,
                                'new' => $new_val
                            ];
                        }
                    }
                }
            }

        return view('pages.systemlogs.index')->with([
            'activities' => $activities,
            'updates' => $updates,
            'search' => $search
        ]);
    }
}
