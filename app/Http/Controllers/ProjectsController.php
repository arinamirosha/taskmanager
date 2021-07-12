<?php

namespace App\Http\Controllers;

use App\Libraries\TaskManager\Facade\TaskManager;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ProjectAction;
use App\Notifications\ProjectShared;
use App\Notifications\ProjectShareDecision;
use App\Notifications\ProjectUnshared;
use App\Notifications\ProjectUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectsController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $project = $user->projects()->create($request->all());
        $user->notify(new ProjectAction($project, 'stored'));

        return $project;
    }

    public function index(Request $request)
    {
        $type      = $request->get('type', false);
        $getCounts = $request->get('get_counts', false);
        $projects  = Auth::user()->projects();

        if ($type === Task::ARCHIVE) {
            $s              = $request->get('s', false);
            $hasNotFinished = $request->get('hasNotFinished', false);
            $projects       = $projects->onlyTrashed();

            if ($s) {
                $projects->where('name', 'like', "%$s%");
            }

            $projects->withCount([
                'tasks'=> function ($query) {
                    $query->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PROGRESS])->withTrashed();
                },
            ]);

            if ($hasNotFinished) {
                $projects->havingRaw('tasks_count > 0');
            }

            $ids = Auth::user()->shared_projects()->where('accepted', true)->onlyTrashed()->pluck('id');

            $data['projects'] = $projects->orWhereIn('id', $ids)->orderBy('deleted_at', 'desc')->paginate(25);
        } else {
            $data['newShared'] = Auth::user()->shared_projects()->whereNull('accepted')->get();

            $projects = $projects->select(DB::raw('projects.*, 0 as shared'))
                                 ->withCount('tasks')
                                 ->withCount('shared_users');

            $sharedProjects = Auth::user()->shared_projects()
                                  ->where('accepted', true)
                                  ->select(DB::raw('projects.*, 1 as shared'))
                                  ->withCount('tasks')
                                  ->withCount('shared_users');

            $data['projects'] = $projects->union($sharedProjects)->orderBy('name')->get();
        }

        foreach ($data['projects'] as $project) {
            if ($project->shared) {
                $project->favorite = $project->shared_users()->wherePivot('user_id', Auth::id())->pluck('favorite')[0];
            }
        }

        if ($getCounts) {
            $data['counts'] = TaskManager::getCountsByStatuses();
        }

        return $data;
    }

    public function show($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('view', $project);

        $project->load('shared_users')->load('user');

        if ($project->trashed()) {
            $project->load([
                'tasks'=> function ($query) {
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

        $data['project'] = $project;
        $data['currentUserId'] = Auth::id();

        return $data;
    }

    public function update(Project $project, Request $request)
    {
        $oldName = $project->name;
        $project->update($request->all());
        $newName = $project->name;

        if ($oldName != $newName) {
            $users = $project->all_users();
            foreach ($users as $user) {
                $user->notify(new ProjectUpdated('name', $oldName, $newName));
            }
        }

        return $project;
    }

    public function favorite(Project $project, Request $request)
    {
        if ($project->user_id === Auth::id()) {
            $project->update($request->all());
        } else {
            $project->shared_users()->wherePivot('user_id', Auth::id())->update($request->all());
        }

        return $project;
    }

    public function share(Project $project, Request $request)
    {
        $user   = User::where('email', $request->get('email'))->firstOrFail();
        $userId = $user->id;

        if ($userId === $project->user_id) {
            return response('You cannot share with yourself', 400);
        }

        if ($project->shared_users()->wherePivot('user_id', $userId)->exists()) {
            return response('Already shared', 400);
        }

        $project->shared_users()->attach($userId);

        Auth::user()->notify(new ProjectShared($project, $user));
        $user->notify(new ProjectShared($project, $user));

        return $project;
    }

    public function unshare(Project $project, Request $request)
    {
        $userUnshared = User::where('email', $request->get('email'))->firstOrFail();
        $users = $project->all_users();
        $project->shared_users()->wherePivot('user_id', $userUnshared->id)->detach();
        $project->tasks()->where('user_id', $userUnshared->id)->update(['user_id' => Auth::id()]);

        foreach ($users as $user) {
            $user->notify(new ProjectUnshared($project, $userUnshared));
        }

        return $project;
    }

    public function accepted(Project $project, Request $request)
    {
        $shared_users = $project->shared_users()->wherePivot('user_id', Auth::id())->whereNull('accepted')->first();

        if (!$shared_users) {
            return response('No new shared projects', 400);
        }

        $accepted = $request->get('accepted');
        $shared_users->pivot->update(['accepted' => $accepted]);

        $users = $project->all_users();
        foreach ($users as $user) {
            $user->notify(new ProjectShareDecision($project, $accepted));
        }

        return $project;
    }

    public function archive(Project $project)
    {
        $project->delete();

        $users = $project->all_users();
        foreach ($users as $user) {
            $user->notify(new ProjectAction($project, 'archived'));
        }

        return true;
    }

    public function restore($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('restore', $project);
        $project->tasks()->withTrashed()->whereIn('status', [Task::STATUS_NEW, Task::STATUS_PROGRESS])->restore();
        $project->restore();

        $users = $project->all_users();
        foreach ($users as $user) {
            $user->notify(new ProjectAction($project, 'restored'));
        }

        return true;
    }

    public function destroyForce($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $project);
        $users = $project->all_users();
        $project->forceDelete();

        foreach ($users as $user) {
            $user->notify(new ProjectAction($project, 'deleted'));
        }

        return true;
    }
}
