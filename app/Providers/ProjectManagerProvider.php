<?php

namespace App\Providers;

use App\Libraries\ProjectManager\ProjectManager;
use Illuminate\Support\ServiceProvider;

class ProjectManagerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ProjectManager::class, function () {
            return new ProjectManager();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ProjectManager::class];
    }
}
