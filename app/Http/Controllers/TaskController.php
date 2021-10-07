<?php

namespace App\Http\Controllers;

use App\Libraries\TaskManager\Facade\TaskManager;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskAction;
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

        return TaskManager::store($request->all(), $project);
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
        return TaskManager::update($task, $request->all());
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

        if ($projectId) {
            $project = Project::findOrFail($projectId);
            $this->authorize('view', $project);
        }

        return TaskManager::archive($type, $projectId);
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

        $data['tasks']         = TaskManager::getIndexTasks($type, $s, $notTrashed); //todo check order, changed if notTrashed changed
        $data['currentUserId'] = Auth::id();

        return $data;
    }
}
