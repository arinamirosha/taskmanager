<?php

namespace App\Providers;

use App\Libraries\TaskManager\TaskManager;
use Illuminate\Support\ServiceProvider;

class TaskManagerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TaskManager::class, function () {
            return new TaskManager();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [TaskManager::class];
    }
}
