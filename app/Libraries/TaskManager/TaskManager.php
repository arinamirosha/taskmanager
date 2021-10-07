<?php

namespace App\Libraries\TaskManager;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskManager
{
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

        $project_ids_own    = $user->projects()->pluck('id');
        $project_ids_shared = $user->shared_projects()->wherePivot('accepted', true)->pluck('id');
        $project_ids        = array_merge($project_ids_own->toArray(), $project_ids_shared->toArray());

        return $user->tasks()->whereIn('project_id', $project_ids);
    }

    /**
     * Get archived tasks
     *
     * @return mixed
     */
    public function getArchived()
    {
        $user = Auth::user();

        $project_ids_own    = $user->projects()->withTrashed()->pluck('id');
        $project_ids_shared = $user->shared_projects()->withTrashed()->wherePivot('accepted', true)->pluck('id');
        $project_ids        = array_merge($project_ids_own->toArray(), $project_ids_shared->toArray());

        return Task::whereIn('project_id', $project_ids)
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
}
