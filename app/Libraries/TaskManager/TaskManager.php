<?php

namespace App\Libraries\TaskManager;

use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskAction;
use App\Notifications\TaskUpdated;
use Carbon\Carbon;
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
                   ->orderBy('deleted_at', 'desc')
                   ->orderBy('name', 'asc');
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

        $updates = $this->getTextUpdates($old, $new);

        if ( ! empty($updates)) {
            foreach ($users as $user) {
                $user->notify(new TaskUpdated($old, $updates));
            }
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

    /**
     * Get array of strings - text updates
     *
     * @param $old
     * @param $new
     *
     * @return array
     */
    protected function getTextUpdates($old, $new)
    {
        $updates = [];

        if ($old['name'] != $new['name']) {
            array_push($updates, $this->genStrUpd('name', $old['name'], $new['name']));
        }

        if ($old['details'] != $new['details']) {
            $oldD = $old['details'];
            $newD = $new['details'];
            if ($oldD && $newD) {
                $i = 0;
                while ($oldD[$i] == $newD[$i]) {
                    $i++;
                }
                $oldD = substr($oldD, $i, 25);
                $newD = substr($newD, $i, 25);
            }
            array_push($updates, $this->genStrUpd('details', $oldD, $newD));
        }

        if ($old['schedule'] != $new['schedule']) {
            array_push($updates, $this->genStrUpd('schedule', $old['schedule'], $new['schedule']));
        }

        if ($old['importance'] != $new['importance']) {
            array_push($updates, $this->genStrUpd('importance', $this->importanceText($old['importance']), $this->importanceText($new['importance'])));
        }

        if ($old['status'] != $new['status']) {
            array_push($updates, $this->genStrUpd('status', $this->statusText($old['status']), $this->statusText($new['status'])));
        }

        if ($old['user_id'] != $new['user_id']) {
            $oldUser = $old['user']['name'] . ($old['user']['surname'] ? ' ' . $old['user']['surname'] : '');
            $newUser = $new['user']['name'] . ($new['user']['surname'] ? ' ' . $new['user']['surname'] : '');
            array_push($updates, $this->genStrUpd('performer', $oldUser, $newUser));
        }

        if ($old['project_id'] != $new['project_id']) {
            array_push($updates, $this->genStrUpd('project', $old['project']['name'], $new['project']['name']));
        }

        return $updates;
    }

    /**
     * Generate string for updates
     *
     * @param $before
     * @param $from
     * @param $to
     *
     * @return string
     */
    protected function genStrUpd($before, $from, $to)
    {
        return $before . ' - "' . $from . '" -> "' . $to . '"';
    }

    /**
     * Get text from importance
     *
     * @param $importance
     *
     * @return string
     */
    protected function importanceText($importance)
    {
        switch ($importance) {
            case Task::STATUS_NORMAL:
                return 'Normal';
            case Task::STATUS_MEDIUM:
                return 'Medium';
            case Task::STATUS_STRONG:
                return 'Strong';
        }

        return '';
    }

    /**
     * Get text from status
     *
     * @param $status
     *
     * @return string
     */
    protected function statusText($status)
    {
        switch ($status) {
            case Task::STATUS_NEW:
                return 'New';
            case Task::STATUS_PROGRESS:
                return 'Progress';
            case Task::STATUS_FINISHED:
                return 'Finished';
        }

        return '';
    }
}
