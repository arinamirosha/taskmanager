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
        $id   = Auth::id();

        $data['user_id']  = in_array($data['user_id'], [$id, $project->user_id])
            ? $id
            : $project->shared_users()
                      ->wherePivot('user_id', $data['user_id'])
                      ->wherePivot('accepted', true)
                      ->firstOrFail()->id;

        $data['owner_id'] = $id;

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

        if ($type) {
            switch ($type) {
                case Task::TODAY:
                    $tasks = TaskManager::getToday();
                    break;
                case Task::NOT_SCHEDULED:
                    $tasks = TaskManager::getNotScheduled();
                    break;
                case Task::UPCOMING:
                    $tasks = TaskManager::getUpcoming();
                    break;
                default: return 0;
            }
        } elseif ($projectId) {
            $project = Project::findOrFail($projectId);
            $this->authorize('view', $project);
            $tasks = Task::where(function ($query) {
                $query->where('user_id', Auth::id())->orWhere('owner_id', Auth::id());
            })->where('project_id', $projectId);
        } else {
            return 0;
        }

        $tasks->where('status', Task::STATUS_FINISHED);

        $count = $tasks->count();
        $tasks->delete();

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
        $type       = $request->get('type', false);
        $s          = $request->get('s', false);
        $notTrashed = $request->get('notTrashed', false);

        switch ($type) {
            case Task::ARCHIVE:
                $tasks = TaskManager::getArchived();
                break;
            case Task::TODAY:
                $tasks = TaskManager::getToday();
                break;
            case Task::NOT_SCHEDULED:
                $tasks = TaskManager::getNotScheduled();
                break;
            case Task::UPCOMING:
                $tasks = TaskManager::getUpcoming();
                break;
            default:
                return [];
        }

        if (Auth::user()->hide_finished && $type != Task::ARCHIVE) {
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

        $data['tasks'] = $tasks->with(['project'])->withCount('comments')->with(['owner', 'user'])->paginate(25);
        $data['currentUserId'] = Auth::id();

        return $data;
    }
}
