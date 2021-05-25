<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
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
        $user = Auth::user();

        $tasks = Task::leftJoin('projects', function ($query) use ($user) {
            $query
                ->on('projects.id', '=', 'tasks.project_id')
                ->where('projects.user_id', $user->id);
        });

        $type = $request->get('type', false);

        if ($user->hide_finished && $type != Task::ARCHIVE) {
            $tasks->where('status', '<>', Task::STATUS_FINISHED);
        }

        if ($type) {
            switch ($type) {
                case Task::ARCHIVE:
                    $tasks = $tasks->onlyTrashed()->orderBy('deleted_at', 'desc');
                    break;
                case Task::TODAY:
                    $tasks = $tasks->where('schedule', '<=', Carbon::today()->format('Y-m-d'))->orderBy('schedule', 'desc');
                    break;
                case Task::NOT_SCHEDULED:
                    $tasks = $tasks->whereNull('schedule')->orderBy('created_at', 'desc');
                    break;
                case Task::UPCOMING:
                    $tasks = $tasks->where('schedule', '<>', Carbon::today()->format('Y-m-d'))->orderBy('schedule', 'desc');
                    break;
            }
        }

        return $tasks->with('project')->select('tasks.*')->get();
    }
}
