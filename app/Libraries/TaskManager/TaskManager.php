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
            $tasks->orderBy('schedule', 'desc');
        }

        return $tasks;
    }

    public function filterUpcoming($tasks, $order = false)
    {
        $tasks->where('schedule', '<>', Carbon::today()->format('Y-m-d'));
        if ($order) {
            $tasks->orderBy('schedule', 'desc');
        }

        return $tasks;
    }

    public function filterNotScheduled($tasks, $order = false)
    {
        $tasks->whereNull('schedule');
        if ($order) {
            $tasks->orderBy('created_at', 'desc');
        }

        return $tasks;
    }

    public function filterArchived($tasks, $order = false)
    {
        $tasks->onlyTrashed();
        if ($order) {
            $tasks->orderBy('deleted_at', 'desc');
        }

        return $tasks;
    }

    public function getCountsByStatuses()
    {
        $user = Auth::user();

        $selectRow = "count(*) as total, "
                     . "sum(case when status = '" . Task::STATUS_NEW . "' then 1 else 0 end) AS " . Task::STATUS_NEW_TEXT . ", "
                     . "sum(case when status = '" . Task::STATUS_PROGRESS . "' then 1 else 0 end) AS " . Task::STATUS_PROGRESS_TEXT . ", "
                     . "sum(case when status = '" . Task::STATUS_FINISHED . "' then 1 else 0 end) AS " . Task::STATUS_FINISHED_TEXT;

        return array(
            Task::TODAY         => $this->filterToday($user->tasks())->select(DB::raw($selectRow))->first(),
            Task::UPCOMING      => $this->filterUpcoming($user->tasks())->select(DB::raw($selectRow))->first(),
            Task::NOT_SCHEDULED => $this->filterNotScheduled($user->tasks())->select(DB::raw($selectRow))->first(),
        );
    }
}
