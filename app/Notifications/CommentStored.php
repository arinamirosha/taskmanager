<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CommentStored extends Notification
{
    use Queueable;

    private $comment;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->user    = Auth::user();
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
            'comment' => $this->comment->text,
            'task'    => $this->comment->task->name,
        ];
    }
}
