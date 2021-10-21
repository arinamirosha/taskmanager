<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TaskUpdated extends Notification
{
    use Queueable;

    private $old;
    private $user;
    private $updates;

    /**
     * Create a new notification instance.
     *
     * @param $old
     * @param $updates
     */
    public function __construct($old, $updates)
    {
        $this->old     = $old;
        $this->user    = Auth::user();
        $this->updates = $updates;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user' => $this->user->name . ($this->user->surname ? ' ' . $this->user->surname : ''),
            'text' => 'updated task "' . $this->old['name'] . "\": \n" . implode("\n", $this->updates),
        ];
    }
}
