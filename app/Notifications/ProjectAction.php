<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProjectAction extends Notification
{
    use Queueable;

    private $project;
    private $user;
    private $action;

    /**
     * Create a new notification instance.
     *
     * @param Project $project
     * @param string $action
     */
    public function __construct(Project $project, string $action)
    {
        $this->project = $project;
        $this->user    = Auth::user();
        $this->action  = $action;
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
            'text' => $this->action . ' project "' . $this->project->name . '"',
        ];
    }
}
