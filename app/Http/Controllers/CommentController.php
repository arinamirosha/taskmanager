<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(CommentRequest $request)
    {
        return Comment::where('task_id', $request->get('task_id'))->orderBy('created_at', 'desc')->with('user')->get();
    }

    public function store(CommentRequest $request)
    {
        $comment = Auth::user()->comments()->create($request->all());
        return $comment->load('user');
    }
}
