<?php

namespace App\Libraries\TaskManager;

use Carbon\Carbon;

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
}
