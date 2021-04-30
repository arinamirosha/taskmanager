<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectsController extends Controller
{
    public function store(Request $request)
    {
        return Auth::user()->projects()->create($request->all());
    }

    public function index()
    {
        return Auth::user()->projects()->get();
    }

    public function show(Project $project)
    {
        return $project->load('tasks');
    }

    public function update(Project $project, Request $request)
    {
        $project->update($request->all());
        return $project;
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return true;
    }
}
