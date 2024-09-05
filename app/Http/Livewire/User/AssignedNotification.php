<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Notification;
use App\Models\NotificationFrequency;
use App\Models\UserNotification;

class AssignedNotification extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $user;
    public $notifications;
    public $frequencies;
    
    public $showAdd = 0;

    public $notification_id;
    public $frequency_id;

    public function showAdd() {
        if($this->showAdd) {
            $this->showAdd = 0;
        } else {
            $this->showAdd = 1;
        }
    }

    public function save() {
        $this->validate([
            'notification_id' => [
                'required'
            ],
            'frequency_id' => [
                'required'
            ],
        ]);

        // check if already exists
        $check = UserNotification::where('user_id', $this->user->id)
            ->where('notification_id', $this->notification_id)
            ->where('notification_frequency_id', $this->frequency_id)
            ->first();
        // avoid duplicate entry
        if(empty($check)) {
            $user_notification = new UserNotification([
                'user_id' => $this->user->id,
                'notification_id' => $this->notification_id,
                'notification_frequency_id' => $this->frequency_id
            ]);
            $user_notification->save();

            $this->form_message = 'Notification has been created!';
        } else {
            $this->form_message = 'Notification already exists.';
        }

        $this->reset(['notification_id', 'frequency_id']);
        $this->showAdd = 0;
        
    }

    public function mount($user) {
        $this->user = $user;
        $this->notifications = Notification::all();
        $this->frequencies = NotificationFrequency::all();
    }

    public function render()
    {
        $user_notifications = UserNotification::where('user_id', $this->user->id)
            ->paginate(10, ['*'], 'user-notif-page')
            ->onEachSide(1);

        return view('livewire.user.assigned-notification')->with([
            'user_notifications' => $user_notifications
        ]);
    }
}
