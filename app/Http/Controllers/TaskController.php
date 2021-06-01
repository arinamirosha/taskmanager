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
        $project = Project::findOrFail($request->get('project_id', 0));
        $this->authorize('update', $project);

        return $project->tasks()->create($request->all());
    }

//    public function update(Task $task, Request $request)
//    {
//        $task->update($request->all());
//
//        return $task;
//    }
//
//    public function show(Task $task)
//    {
//        return $task;
//    }

    public function destroy(Task $task)
    {
        $task->delete();

        return true;
    }

    public function archive(Request $request)
    {
        $type      = $request->get('type', false);
        $projectId = $request->get('project_id', false);

        $tasks = Auth::user()->tasks()->where('status', Task::STATUS_FINISHED);
        $count = 0;

        if ($type) {
            switch ($type) {
                case Task::TODAY:
                    $tasks = $tasks->where('schedule', '<=', Carbon::today()->format('Y-m-d'));
                    break;
                case Task::NOT_SCHEDULED:
                    $tasks = $tasks->whereNull('schedule');
                    break;
                case Task::UPCOMING:
                    $tasks = $tasks->where('schedule', '<>', Carbon::today()->format('Y-m-d'));
                    break;
            }
            $count = $tasks->count();
            $tasks->delete();
        } elseif ($projectId) {
            $tasks = $tasks->where('project_id', $projectId);
            $count = $tasks->count();
            $tasks->delete();
        }

        return $count;
    }

    public function destroyForce(Task $task)
    {
        $task->forceDelete();

        return true;
    }

    public function index(Request $request)
    {
        $user  = Auth::user();
        $tasks = $user->tasks();
        $type  = $request->get('type', false);

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

        return $tasks->with(['project'])->get();
    }
}
