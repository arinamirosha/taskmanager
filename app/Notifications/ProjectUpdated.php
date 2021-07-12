<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProjectUpdated extends Notification
{
    use Queueable;

    private $old;
    private $new;
    private $field;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param string $field
     * @param $old
     * @param $new
     */
    public function __construct(string $field, $old, $new)
    {
        $this->field = $field;
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
            'field' => $this->field,
            'old'   => $this->old,
            'new'   => $this->new,
        ];
    }
}
