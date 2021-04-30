<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        return Project::findOrFail($request->get('project_id'))->tasks()->create($request->all());
    }

    public function update(Task $task, Request $request)
    {
        $task->update($request->all());
        return $task;
    }

    public function show(Task $task)
    {
        return $task;
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return true;
    }

    public function destroyForce(Task $task)
    {
        $task->forceDelete();
        return true;
    }

    public function index(Request $request)
    {
        $tasks = Task::leftJoin('projects', function ($query) {
            $query
                ->on('projects.id', '=', 'tasks.project_id')
                ->where('projects.user_id', Auth::id());
        });

        if ($request->has('trashed')) {
            $tasks = $tasks->onlyTrashed();
        }

        return $tasks->with('project')->select('tasks.*')->get();
    }
}
