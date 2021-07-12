<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Task $task)
    {
        $this->authorize('view', $task->project);
        $data['comments'] = $task->comments()->orderBy('created_at', 'desc')->with('user')->paginate(25);
        $data['currentUserId'] = Auth::id();

        return $data;
    }

    public function store(Task $task, Request $request)
    {
        $this->authorize('view', $task->project);
        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'text'    => $request->get('text')
        ]);

        return $comment->load('user');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return true;
    }

    public function update(Comment $comment, Request $request)
    {
        $comment->update($request->all());

        return $comment;
    }
}
