<?php

namespace App\Http\Livewire\Notifications;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\NotificationFrequency as NotificationFrequencyModel;

class NotificationFrequency extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $showAdd = 0;
    public $type, $time, $day;

    public function addFrequency() {
        if($this->showAdd == 0) {
            $this->showAdd = 1;
        } else {
            $this->showAdd = 0;
        }
    }

    public function save() {
        $this->validate([
            'type' => 'required',
            'time' => 'required',
            'day' => 'required',
        ]);

        $frequency = new NotificationFrequencyModel([
            'type' => $this->type,
            'time' => $this->time,
            'day' => $this->day,
        ]);
        $frequency->save();

        $this->showAdd = 0;
    }

    public function render()
    {
        $frequencies = NotificationFrequencyModel::orderBy('created_at', 'DESC')
            ->paginate(10, ['*'], 'frequency-page')
            ->onEachSide(1);

        return view('livewire.notifications.notification-frequency')->with([
            'frequencies' => $frequencies,
        ]);
    }
}
