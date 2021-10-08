<?php

namespace App\Libraries\ProjectManager\Facade;

use Illuminate\Support\Facades\Facade;

class ProjectManager extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\Libraries\ProjectManager\ProjectManager::class;
    }
}
