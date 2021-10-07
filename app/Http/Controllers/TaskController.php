<?php

namespace App\Http\Controllers;

use App\Libraries\TaskManager\Facade\TaskManager;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskAction;
use App\Notifications\TaskUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Store task
     *
     * @param Request $request
     *
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $project = Project::findOrFail($request->get('project_id', 0));
        $this->authorize('view', $project);

        $data = $request->all();
        $id   = Auth::id();

        $data['user_id'] = in_array($data['user_id'], [$id, $project->user_id])
            ? $data['user_id']
            : $project->shared_users()
                      ->wherePivot('user_id', $data['user_id'])
                      ->wherePivot('accepted', true)
                      ->firstOrFail()->id;

        $data['owner_id'] = $id;

        $task  = $project->tasks()->create($data);
        $users = $task->project->all_users();

        foreach ($users as $user) {
            $user->notify(new TaskAction($task, 'stored'));
        }

        return $task;
    }

    /**
     * Update task
     *
     * @param Task $task
     * @param Request $request
     *
     * @return Task
     */
    public function update(Task $task, Request $request)
    {
        $data    = $request->all();
        $project = $task->project;

        if (isset($data['project_id'])) {
            if (Auth::id() == $task->owner_id) {
                if ($data['project_id'] != $task->project_id) {
                    $project         = Project::findOrFail($data['project_id']);
                    $data['status']  = Task::STATUS_NEW;
                    $data['user_id'] = Auth::id();
                }
            } else {
                unset($data['project_id']);
            }
        }

        if (isset($data['user_id'])) {
            $data['user_id'] = in_array($data['user_id'], [Auth::id(), $project->user_id])
                ? $data['user_id']
                : $project->shared_users()
                          ->wherePivot('user_id', $data['user_id'])
                          ->wherePivot('accepted', true)
                          ->firstOrFail()->id;
        }

        $users = $task->project->all_users();

        $old = $task->load(['user', 'project'])->toArray();
        $task->update($data);
        $new = $task->load(['user', 'project'])->toArray();

        foreach ($users as $user) {
            $user->notify(new TaskUpdated($old, $new));
        }

        return $task->load(['project', 'project.shared_users', 'project.user', 'owner', 'user'])->loadCount('comments');
    }

    /**
     * Restore task
     *
     * @param $id
     *
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('restore', $task);
        $task->restore();

        $users = $task->project->all_users();
        foreach ($users as $user) {
            $user->notify(new TaskAction($task, 'restored'));
        }

        return true;
    }

    /**
     * Archive task
     *
     * @param Task $task
     *
     * @return bool
     * @throws \Exception
     */
    public function destroy(Task $task)
    {
        $task->delete();

        $users = $task->project->all_users();
        foreach ($users as $user) {
            $user->notify(new TaskAction($task, 'archived'));
        }

        return true;
    }

    /**
     * Archive all tasks that finished
     *
     * @param Request $request
     *
     * @return int
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
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
                default:
                    return 0;
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

        $tasksWillDel = $tasks->get();
        $count        = $tasks->count();

        $tasks->delete();

        foreach ($tasksWillDel as $task) {
            $users = $task->project->all_users();
            foreach ($users as $user) {
                $user->notify(new TaskAction($task, 'archived'));
            }
        }

        return $count;
    }

    /**
     * Delete task forever
     *
     * @param $id
     *
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroyForce($id)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $task);

        $users = $task->project->all_users();

        $task->forceDelete();

        foreach ($users as $user) {
            $user->notify(new TaskAction($task, 'deleted'));
        }

        return true;
    }

    /**
     * Get tasks by type
     *
     * @param Request $request
     *
     * @return array
     */
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
            $tasks->whereHas('project', function ($q) use ($s) {
                $q->whereNull('deleted_at');
            });
        }

        if ($s) {
            $tasks->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")->orWhereHas('project', function ($q) use ($s) {
                    $q->where('name', 'like', "%$s%");
                });
            });
        }

        $data['tasks'] = $tasks->with([
            'project',
            'owner',
            'user',
            'project.shared_users',
            'project.user',
        ])->withCount('comments')->paginate(25);

        $data['currentUserId'] = Auth::id();

        return $data;
    }
}
