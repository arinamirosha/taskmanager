<?php

namespace App\Http\Controllers;

use App\Libraries\TaskManager\Facade\TaskManager;
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

    public function update(Task $task, Request $request)
    {
        $task->update($request->all());

        return $task;
    }

    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('restore', $task);
        $task->restore();

        return true;
    }

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
        $tasks     = Auth::user()->tasks()->where('status', Task::STATUS_FINISHED);
        $count     = 0;

        if ($type) {
            switch ($type) {
                case Task::TODAY:
                    TaskManager::filterToday($tasks);
                    break;
                case Task::NOT_SCHEDULED:
                    TaskManager::filterNotScheduled($tasks);
                    break;
                case Task::UPCOMING:
                    TaskManager::filterUpcoming($tasks);
                    break;
            }
            $count = $tasks->count();
            $tasks->delete();
        } elseif ($projectId) {
            Project::findOrFail($projectId);
            $tasks = $tasks->where('project_id', $projectId);
            $count = $tasks->count();
            $tasks->delete();
        }

        return $count;
    }

    public function destroyForce($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $task);
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
                    TaskManager::filterArchived($tasks, true);
                    break;
                case Task::TODAY:
                    TaskManager::filterToday($tasks, true);
                    break;
                case Task::NOT_SCHEDULED:
                    TaskManager::filterNotScheduled($tasks, true);
                    break;
                case Task::UPCOMING:
                    TaskManager::filterUpcoming($tasks, true);
                    break;
                default:
                    return [];
            }
        }

        return $tasks->with(['project'])->get();
    }
}
