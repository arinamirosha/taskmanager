<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CommentUpdated extends Notification
{
    use Queueable;

    private $user;
    private $comment;
    private $oldText;

    /**
     * Create a new notification instance.
     *
     * @param Comment $comment
     * @param $oldText
     */
    public function __construct(Comment $comment, $oldText)
    {
        $this->comment = $comment;
        $this->user    = Auth::user();
        $this->oldText = $oldText;
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
        $oldText = $this->oldText;
        $newText = $this->comment->text;

        $i = 0;
        while ($oldText[$i] == $newText[$i]) {
            $i++;
        }
        $oldText = substr($oldText, $i, 25);
        $newText = substr($newText, $i, 25);

        return [
            'user' => $this->user->name . ($this->user->surname ? ' ' . $this->user->surname : ''),
            'text' => 'updated comment in task "' . $this->comment->task->name . '": "' . $oldText . '" -> "' . $newText . '"',
        ];
    }
}
