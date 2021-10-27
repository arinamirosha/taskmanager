<?php

namespace App\Http\Controllers;

use App\Libraries\ProjectManager\Facade\ProjectManager;
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
use Illuminate\Support\Facades\Log;

class ProjectsController extends Controller
{
    /**
     * Store project
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        $user    = Auth::user();
        $project = $user->projects()->create($request->all());

        $user->notify(new ProjectAction($project, 'stored'));

        return $project;
    }

    /**
     * Get user projects
     *
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request)
    {
        $type           = $request->get('type', false);
        $s              = $request->get('s', false);
        $hasNotFinished = $request->get('hasNotFinished', false);
        $getCounts      = $request->get('get_counts', false);

        if ($type !== Task::ARCHIVE) {
            $data['newShared'] = Auth::user()->shared_projects()->whereNull('accepted')->get();
        }

        $data['projects'] = ProjectManager::getIndexProjects($type, $s, $hasNotFinished);

        if ($getCounts) {
            $data['counts'] = TaskManager::getCountsByStatuses();
        }

        return $data;
    }

    /**
     * Show project
     *
     * @param $id
     *
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('view', $project);

        return ProjectManager::addDataToProject($project);
    }

    /**
     * Update project
     *
     * @param Project $project
     * @param Request $request
     *
     * @return Project
     */
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

    /**
     * Mark project as favorite
     *
     * @param Project $project
     * @param Request $request
     *
     * @return Project
     */
    public function favorite(Project $project, Request $request)
    {
        if ($project->user_id === Auth::id()) {
            $project->update($request->all());
        } else {
            $project->shared_users()->wherePivot('user_id', Auth::id())->update($request->all());
        }

        return $project;
    }

    /**
     * Share project: send application to user
     *
     * @param Project $project
     * @param Request $request
     *
     * @return Project|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
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

    /**
     * Unshare project with user
     *
     * @param Project $project
     * @param Request $request
     *
     * @return Project
     */
    public function unshare(Project $project, Request $request)
    {
        $userUnshared = User::where('email', $request->get('email'))->firstOrFail();
        $users        = $project->all_users();

        $project->shared_users()->wherePivot('user_id', $userUnshared->id)->detach();
        $project->tasks()->where('user_id', $userUnshared->id)->update(['user_id' => Auth::id()]);

        foreach ($users as $user) {
            $user->notify(new ProjectUnshared($project, $userUnshared));
        }

        return $project;
    }

    /**
     * User answer to shared project: accepted or not
     *
     * @param Project $project
     * @param Request $request
     *
     * @return Project|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function accepted(Project $project, Request $request)
    {
        $shared_users = $project->shared_users()->wherePivot('user_id', Auth::id())->whereNull('accepted')->first();

        if ( ! $shared_users) {
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

    /**
     * Archive project
     *
     * @param Project $project
     *
     * @return bool
     * @throws \Exception
     */
    public function archive(Project $project)
    {
        $project->delete();

        $users = $project->all_users();

        foreach ($users as $user) {
            $user->notify(new ProjectAction($project, 'archived'));
        }

        return true;
    }

    /**
     * Restore project
     *
     * @param $id
     *
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
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

    /**
     * Delete project forever
     *
     * @param $id
     *
     * @return bool
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
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
