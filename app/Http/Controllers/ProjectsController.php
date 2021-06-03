<?php

namespace App\Http\Controllers;

use App\Libraries\TaskManager\Facade\TaskManager;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectsController extends Controller
{
    public function store(Request $request)
    {
        return Auth::user()->projects()->create($request->all());
    }

    public function index(Request $request)
    {
        $type      = $request->get('type', false);
        $getCounts = $request->get('get_counts', false);
        $projects  = Auth::user()->projects();

        if ($type === Task::ARCHIVE) {
            $projects = $projects->onlyTrashed();
            $data['projects'] = $projects->withCount([
                'tasks'=> function ($query) {
                    $query->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PROGRESS])->withTrashed();
                },
            ])->get();
        } else {
            $data['projects'] = $projects->withCount('tasks')->get();
        }

        if ( $getCounts ) {
            $user = Auth::user();
            $data['counts'] = array(
                Task::TODAY         => TaskManager::filterToday($user->tasks())->count(),
                Task::UPCOMING      => TaskManager::filterUpcoming($user->tasks())->count(),
                Task::NOT_SCHEDULED => TaskManager::filterNotScheduled($user->tasks())->count(),
                Task::ARCHIVE       => TaskManager::filterArchived($user->tasks())->count(),
            );
        }

        return $data;
    }

    public function show($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('view', $project);

        if ($project->trashed()) {
            return $project->load([
                'tasks'=> function ($query) {
                    $query->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PROGRESS])->withTrashed();
                },
            ]);
        } else {
            return $project->load('tasks');
        }
    }

    public function update(Project $project, Request $request)
    {
        $project->update($request->all());

        return $project;
    }

    public function archive(Project $project)
    {
        $project->tasks()->delete();
        $project->delete();

        return true;
    }

    public function restore($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('restore', $project);
        $project->tasks()->withTrashed()->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PROGRESS])->restore();
        $project->restore();

        return true;
    }

    public function destroyForce($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $project);
        $project->forceDelete();

        return true;
    }
}
