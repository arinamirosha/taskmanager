<?php


namespace App\Libraries\ProjectManager;


use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectManager
{
    /**
     * Get index projects
     *
     * @param $type
     * @param $s
     * @param $hasNotFinished
     *
     * @return mixed
     */
    public function getIndexProjects($type, $s, $hasNotFinished)
    {
        $projects = Auth::user()->projects();

        if ($type === Task::ARCHIVE) {
            $projects = $projects->onlyTrashed();

            if ($s) {
                $projects->where('name', 'like', "%$s%");
            }

            $projects->withCount([
                'tasks' => function ($query) {
                    $query->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PROGRESS])->withTrashed();
                },
            ]);

            if ($hasNotFinished) {
                $projects->havingRaw('tasks_count > 0');
            }

            $ids = Auth::user()->shared_projects()->where('accepted', true)->onlyTrashed()->pluck('id');

            $resultProjects = $projects->orWhereIn('id', $ids)->orderBy('deleted_at', 'desc')->paginate(25);
        } else {
            $projects = $projects->select(DB::raw('projects.*, 0 as shared'))
                                 ->withCount('tasks')
                                 ->withCount('shared_users');

            $sharedProjects = Auth::user()->shared_projects()
                                  ->where('accepted', true)
                                  ->select(DB::raw('projects.*, 1 as shared'))
                                  ->withCount('tasks')
                                  ->withCount('shared_users');

            $resultProjects = $projects->union($sharedProjects)->orderBy('name')->get();
        }

        foreach ($resultProjects as $project) {
            if ($project->shared) {
                $project->favorite = $project->shared_users()->wherePivot('user_id', Auth::id())->pluck('favorite')[0];
            }
        }

        return $resultProjects;
    }

    public function addDataToProject($project)
    {
        $project->load('shared_users')->load('user');

        if ($project->trashed()) {
            $project->load([
                'tasks' => function ($query) {
                    $query->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PROGRESS])->withTrashed()->with('user');
                },
            ]);
        } else {
            $project->load('tasks')->load(['tasks.owner', 'tasks.user']);
        }

        $project->shared = $project->user_id != Auth::id();

        if ($project->shared) {
            $project->favorite = $project->shared_users()->wherePivot('user_id', Auth::id())->pluck('favorite')[0];
        }

        return $project;
    }
}
