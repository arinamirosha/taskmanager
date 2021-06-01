<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
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
        $type     = $request->get('type', false);
        $projects = Auth::user()->projects();

        if ($type === Task::ARCHIVE) {
            $projects = $projects->onlyTrashed();
        }

        return $projects->withCount('tasks')->get();
    }

    public function show($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('view', $project);

        return $project->load('tasks');
    }

    public function update(Project $project, Request $request)
    {
        $project->update($request->all());

        return $project;
    }

    public function archive(Project $project, Request $request)
    {
        $archiveTasks = $request->get('archive_tasks', true);

        if ($archiveTasks) {
            $project->tasks()->delete();
        }
        $project->delete();

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
