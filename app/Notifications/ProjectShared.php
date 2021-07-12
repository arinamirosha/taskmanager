<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProjectShared extends Notification
{
    use Queueable;

    private $project;
    private $user;
    private $accepted;
    private $userShared;

    /**
     * Create a new notification instance.
     *
     * @param Project $project
     * @param User $userShared
     */
    public function __construct(Project $project, User $userShared)
    {
        $this->project    = $project;
        $this->user       = Auth::user();
        $this->userShared = $userShared;
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
            'user'       => $this->user->name . ($this->user->surname ? ' ' . $this->user->surname : ''),
            'userShared' => $this->userShared->name . ($this->userShared->surname ? ' ' . $this->userShared->surname : ''),
            'project'    => $this->project->name,
        ];
    }
}
