<?php

namespace App\Libraries\TaskManager;

use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskAction;
use App\Notifications\TaskUpdated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskManager
{
    /**
     * Get tasks by type: today, notScheduled or upcoming
     *
     * @param string $type
     *
     * @return false|mixed
     */
    public function getTasksByType(string $type)
    {
        switch ($type) {
            case Task::ARCHIVE:
                return $this->getArchived();
            case Task::TODAY:
                return $this->getToday();
            case Task::NOT_SCHEDULED:
                return $this->getNotScheduled();
            case Task::UPCOMING:
                return $this->getUpcoming();
            default:
                return false;
        }
    }

    /**
     * Get today tasks and overdue
     *
     * @return mixed
     */
    public function getToday()
    {
        return $this->getTasks()
                    ->where('schedule', '<=', Carbon::today()->format('Y-m-d'))
                    ->orderBy('schedule')
                    ->orderBy('importance', 'desc');
    }

    /**
     * Get upcoming tasks and overdue
     *
     * @return mixed
     */
    public function getUpcoming()
    {
        return $this->getTasks()
                    ->where('schedule', '<>', Carbon::today()->format('Y-m-d'))
                    ->orderBy('schedule')
                    ->orderBy('importance', 'desc');
    }

    /**
     * Get tasks without schedule
     *
     * @return mixed
     */
    public function getNotScheduled()
    {
        return $this->getTasks()
                    ->whereNull('schedule')
                    ->orderBy('importance', 'desc');
    }

    /**
     * Get current user tasks including with shared projects
     *
     * @return mixed
     */
    private function getTasks()
    {
        $user = Auth::user();

        $projectIdsOwn    = $user->projects()->pluck('id');
        $projectIdsShared = $user->shared_projects()->wherePivot('accepted', true)->pluck('id');
        $projectIds       = array_merge($projectIdsOwn->toArray(), $projectIdsShared->toArray());

        return $user->tasks()->whereIn('project_id', $projectIds);
    }

    /**
     * Get archived tasks
     *
     * @return mixed
     */
    public function getArchived()
    {
        $user = Auth::user();

        $projectIdsOwn    = $user->projects()->withTrashed()->pluck('id');
        $projectIdsShared = $user->shared_projects()->withTrashed()->wherePivot('accepted', true)->pluck('id');
        $projectIds       = array_merge($projectIdsOwn->toArray(), $projectIdsShared->toArray());

        return Task::whereIn('project_id', $projectIds)
                   ->onlyTrashed()
                   ->orderBy('deleted_at', 'desc');
    }

    /**
     * Get counts of today/upcoming/notScheduled tasks
     *
     * @return array
     */
    public function getCountsByStatuses()
    {
        $selectRow = "count(*) as total, "
                     . "sum(case when status = '" . Task::STATUS_NEW . "' then 1 else 0 end) AS " . Task::STATUS_NEW_TEXT . ", "
                     . "sum(case when status = '" . Task::STATUS_PROGRESS . "' then 1 else 0 end) AS " . Task::STATUS_PROGRESS_TEXT . ", "
                     . "sum(case when status = '" . Task::STATUS_FINISHED . "' then 1 else 0 end) AS " . Task::STATUS_FINISHED_TEXT;

        return array(
            Task::TODAY         => $this->getToday()->select(DB::raw($selectRow))->first(),
            Task::UPCOMING      => $this->getUpcoming()->select(DB::raw($selectRow))->first(),
            Task::NOT_SCHEDULED => $this->getNotScheduled()->select(DB::raw($selectRow))->first(),
        );
    }

    /**
     * Store task for project
     *
     * @param array $data
     * @param Project $project
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function store(array $data, Project $project)
    {
        $userId = Auth::id();

        $data['user_id'] = in_array($data['user_id'], [$userId, $project->user_id])
            ? $data['user_id']
            : $project->shared_users()
                      ->wherePivot('user_id', $data['user_id'])
                      ->wherePivot('accepted', true)
                      ->firstOrFail()->id;

        $data['owner_id'] = $userId;

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
     * @param array $data
     *
     * @return Task
     */
    public function update(Task $task, array $data)
    {
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
     * Archive by type or by projectId
     *
     * @param $type
     * @param $projectId
     *
     * @return int
     */
    public function archive($type, $projectId)
    {
        if ($type) {
            $tasks = $this->getTasksByType($type);
            if ( ! $tasks) {
                return 0;
            }
        } elseif ($projectId) {
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
     * Get index tasks by type, use search string, is need with trashed projects
     *
     * @param $type
     * @param $s
     * @param $notTrashed
     *
     * @return array
     */
    public function getIndexTasks($type, $s, $notTrashed)
    {
        $tasks = $this->getTasksByType($type);

        if ( ! $tasks) {
            return [];
        }

        if (Auth::user()->hide_finished && $type != Task::ARCHIVE) {
            $tasks->where('status', '<>', Task::STATUS_FINISHED);
        }

        if ($notTrashed) {
            $tasks->whereHas('project', function ($q) {
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

        return $tasks->with([
            'project',
            'owner',
            'user',
            'project.shared_users',
            'project.user',
        ])->withCount('comments')->paginate(25);
    }
}
