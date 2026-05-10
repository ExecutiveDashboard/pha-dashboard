<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('is_active', 'desc')->orderBy('name')->get();
        return view('projects.index', compact('projects'));
    }

    public function switchProject(Request $request)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);
        Project::switchTo($request->project_id);
        return back()->with('success', 'Switched to project: ' . Project::find($request->project_id)->name);
    }

    public function updateBank(Request $request, Project $project)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'bank_account_no' => 'required|string',
            'bank_branch' => 'nullable|string'
        ]);

        $project->update($request->only('bank_name', 'bank_account_no', 'bank_branch'));

        return back()->with('success', "Bank details updated for project: {$project->name}");
    }
}
