<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\UserNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\NotificationFrequency;
use App\Notifications\UploadNotification;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $frequencies = NotificationFrequency::get();
        if(!empty($frequencies)) {
            foreach($frequencies as $frequency) {
                switch($frequency->type) {
                    case 'monthly':
                        $schedule->call(function() use($frequency) {
                            $user_notifications = $frequency->user_notifications;
                            foreach($user_notifications as $user_notification) {
                                Notification::send($user_notification->user, new UploadNotification($user_notification));
                            }
                        })->monthlyOn($frequency->day, $frequency->time);
                    break;
                    case 'weekly':
                        $schedule->call(function() use($frequency) {
                            $user_notifications = $frequency->user_notifications;
                            foreach($user_notifications as $user_notification) {
                                Notification::send($user_notification->user, new UploadNotification($user_notification));
                            }
                        })->weeklyOn($frequency->day, $frequency->time);
                    break;
                    case 'daily':
                        $schedule->call(function() use($frequency) {
                            $user_notifications = $frequency->user_notifications;
                            foreach($user_notifications as $user_notification) {
                                Notification::send($user_notification->user, new UploadNotification($user_notification));
                            }
                        })->dailyAt($frequency->time);
                    break;
                }
            }
        }
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
