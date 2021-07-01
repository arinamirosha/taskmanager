<?php

namespace App\Libraries\TaskManager;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskManager
{
    public function filterToday($tasks, $order = false)
    {
        $tasks->where('schedule', '<=', Carbon::today()->format('Y-m-d'));
        if ($order) {
            $tasks->orderBy('schedule')->orderBy('importance', 'desc');
        }

        return $tasks;
    }

    public function getToday()
    {
        return $this->getTasks()
                    ->where('schedule', '<=', Carbon::today()->format('Y-m-d'))
                    ->orderBy('schedule')
                    ->orderBy('importance', 'desc');
    }

    public function filterUpcoming($tasks, $order = false)
    {
        $tasks->where('schedule', '<>', Carbon::today()->format('Y-m-d'));
        if ($order) {
            $tasks->orderBy('schedule')->orderBy('importance', 'desc');
        }

        return $tasks;
    }

    public function getUpcoming()
    {
        return $this->getTasks()
                    ->where('schedule', '<>', Carbon::today()->format('Y-m-d'))
                    ->orderBy('schedule')
                    ->orderBy('importance', 'desc');
    }

    public function filterNotScheduled($tasks, $order = false)
    {
        $tasks->whereNull('schedule');
        if ($order) {
            $tasks->orderBy('importance', 'desc');
        }

        return $tasks;
    }

    public function getNotScheduled() {
        return $this->getTasks()
                    ->whereNull('schedule')
                    ->orderBy('importance', 'desc');
    }

    private function getTasks()
    {
        $user = Auth::user();

        $ids1 = $user->projects()->pluck('id');
        $ids2 = $user->shared_projects()->wherePivot('accepted', true)->pluck('id');

        return $user->tasks()->whereIn('project_id', array_merge($ids1->toArray(), $ids2->toArray()));
    }

    public function getArchived()
    {
        $user = Auth::user();

        $ids1 = $user->projects()->withTrashed()->pluck('id');
        $ids2 = $user->shared_projects()->withTrashed()->wherePivot('accepted', true)->pluck('id');

        return Task::whereIn('project_id', array_merge($ids1->toArray(), $ids2->toArray()))
                   ->onlyTrashed()
                   ->orderBy('deleted_at', 'desc');
    }

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
