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
    private $new;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param $old
     * @param $new
     */
    public function __construct($old, $new)
    {
        $this->old   = $old;
        $this->new   = $new;
        $this->user  = Auth::user();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user'  => $this->user->name . ($this->user->surname ? ' ' . $this->user->surname : ''),
            'old'   => $this->old,
            'new'   => $this->new,
        ];
    }
}
