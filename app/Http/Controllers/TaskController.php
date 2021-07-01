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
        $this->authorize('view', $project);
        $data = $request->all();
        $data['user_id'] = Auth::id();

        return $project->tasks()->create($data);
    }

    public function update(Task $task, Request $request)
    {
        if ($projectId = $request->get('project_id', false)) {
            Project::findOrFail($projectId);
        }
        $task->update($request->all());

        return $task->load(['project'])->loadCount('comments');
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
        $user       = Auth::user();
        $type       = $request->get('type', false);
        $s          = $request->get('s', false);
        $notTrashed = $request->get('notTrashed', false);

        switch ($type) {
            case Task::ARCHIVE:
                $tasks = TaskManager::getArchived($user);
                break;
            case Task::TODAY:
                $tasks = TaskManager::filterToday($user->tasks(), true);
                break;
            case Task::NOT_SCHEDULED:
                $tasks = TaskManager::filterNotScheduled($user->tasks(), true);
                break;
            case Task::UPCOMING:
                $tasks = TaskManager::filterUpcoming($user->tasks(), true);
                break;
            default:
                return [];
        }

        if ($user->hide_finished && $type != Task::ARCHIVE) {
            $tasks->where('status', '<>', Task::STATUS_FINISHED);
        }

        if ($notTrashed) {
            $tasks->whereHas('project', function($q) use ($s) {
                $q->whereNull('deleted_at');
            });
        }

        if ($s) {
            $tasks->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")->orWhereHas('project', function($q) use ($s) {
                    $q->where('name', 'like', "%$s%");
                });
            });
        }

        return $tasks->with(['project'])->withCount('comments')->with('user')->paginate(25);
    }
}
