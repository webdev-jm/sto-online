<?php

namespace App\Http\Livewire\Notifications;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Notification;

class NotificationList extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $showAdd = 0;
    public $subject, $from_email, $from_name, $message, $link_name, $link_url;

    public function showAdd() {
        if($this->showAdd) {
            $this->showAdd = 0;
        } else {
            $this->showAdd = 1;
        }
    }

    public function save() {
        $this->validate([
            'subject' => [
                'required'
            ],
            'from_email' => [
                'required'
            ],
            'from_name' => [
                'required'
            ],
            'message' => [
                'required'
            ],
            'link_name' => [
                'required'
            ],
            'link_url' => [
                'required'
            ]
        ]);

        $notification = new Notification([
            'subject' => $this->subject,
            'from_email' => $this->from_email,
            'from_name' => $this->from_name,
            'message' => $this->message,
            'link_name' => $this->link_name,
            'link_url' => $this->link_url
        ]);
        $notification->save();

        $this->showAdd = 0;
    }

    public function render()
    {
        $notifications = Notification::orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'notification-page')
            ->onEachSide(1);

        return view('livewire.notifications.notification-list')->with([
            'notifications' => $notifications
        ]);
    }
}
