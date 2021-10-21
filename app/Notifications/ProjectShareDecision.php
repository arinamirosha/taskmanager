<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProjectShareDecision extends Notification
{
    use Queueable;

    private $project;
    private $user;
    private $accepted;

    /**
     * Create a new notification instance.
     *
     * @param Project $project
     * @param $accepted
     */
    public function __construct(Project $project, $accepted)
    {
        $this->project  = $project;
        $this->user     = Auth::user();
        $this->accepted = $accepted;
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
            'text' => ($this->accepted ? 'accepted' : 'declined') . ' shared project "' . $this->project->name . '"',
        ];
    }
}
