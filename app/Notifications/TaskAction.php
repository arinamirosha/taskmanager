<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TaskAction extends Notification
{
    use Queueable;

    private $task;
    private $user;
    private $action;

    /**
     * Create a new notification instance.
     *
     * @param Task $task
     * @param string $action
     */
    public function __construct(Task $task, string $action)
    {
        $this->task = $task;
        $this->user    = Auth::user();
        $this->action  = $action;
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
            'user'    => $this->user->name . ($this->user->surname ? ' ' . $this->user->surname : ''),
            'task'    => $this->task->name,
            'project' => $this->task->project->name,
            'action'  => $this->action,
        ];
    }
}
