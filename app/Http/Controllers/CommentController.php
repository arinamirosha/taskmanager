<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Notifications\CommentStored;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Get comments of task
     *
     * @param Task $task
     *
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Task $task)
    {
        $this->authorize('view', $task->project);
        $data['comments']      = $task->comments()->orderBy('created_at', 'desc')->with('user')->paginate(25);
        $data['currentUserId'] = Auth::id();

        return $data;
    }

    /**
     * Store comment to task
     *
     * @param Task $task
     * @param Request $request
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Task $task, Request $request)
    {
        $this->authorize('view', $task->project);
        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'text'    => $request->get('text'),
        ]);

        $users = $comment->task->project->all_users();
        foreach ($users as $user) {
            $user->notify(new CommentStored($comment));
        }

        return $comment->load('user');
    }

    /**
     * Delete comment
     *
     * @param Comment $comment
     *
     * @return bool
     * @throws \Exception
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return true;
    }

    /**
     * Update comment
     *
     * @param Comment $comment
     * @param Request $request
     *
     * @return Comment
     */
    public function update(Comment $comment, Request $request)
    {
        $comment->update($request->all());

        return $comment;
    }
}
