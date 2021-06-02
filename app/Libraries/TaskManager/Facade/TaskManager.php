<?php

namespace App\Libraries\TaskManager\Facade;

use Illuminate\Support\Facades\Facade;


class TaskManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\Libraries\TaskManager\TaskManager::class;
    }
}
