<?php

namespace App\Http\Livewire\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;

use Spatie\Activitylog\Models\Activity;

class UserLogs extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search;

    public function updatedSearch() {
        $this->resetPage('activity-page');
    }

    public function render()
    {
        if(!empty($this->search)) {
            $activities = Activity::where('causer_id', auth()->user()->id)
            ->where('causer_type', 'App\Models\User')
            ->where(function($query) {
                $query->where('description', 'like', '%'.$this->search.'%')
                ->orWhere('log_name', 'like', '%'.$this->search.'%')
                ->orWhere('properties', 'like', '%'.$this->search.'%')
                ->orWhereHas('causer', function($qry) {
                    $qry->where('name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'activity-page')
            ->onEachSide(1);
        } else {
            $activities = Activity::where('causer_id', auth()->user()->id)
            ->where('causer_type', 'App\Models\User')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'activity-page')
            ->onEachSide(1);
        }

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

        return view('livewire.activity-logs.user-logs')->with([
            'activities' => $activities,
            'updates' => $updates
        ]);
    }
}
