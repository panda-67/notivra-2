<?php

namespace App\Domains\Hire\Controller;

use App\Http\Controllers\Controller;
use App\Models\HireDraft;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function dashboard()
    {
        $projects = [];
        $drafts = [];

        if ($userId = Auth::id()) {
            $projects = Project::where('user_id', $userId)->latest()->get();
            $drafts = HireDraft::where('user_id', $userId)->latest()->get();
        }

        return inertia('Dashboard', compact('projects', 'drafts'));
    }

    public function edit(Project $project)
    {
        $locale = App::getLocale();

        return inertia('Hire/Edit', [
            'project' => $project,
            'services' => Service::all()->map(function ($service) use ($locale) {
                return [
                    'id' => $service->id,
                    'title' => $service->title[$locale] ?? $service->title['id'] ?? 'No Title'
                ];
            }),
            'statuses' => ['pending', 'processing', 'final']
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'service_id'    => 'required|exists:services,id',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone'         => 'nullable|string|max:20',
            'institution'   => 'nullable|string|max:255',
            'level'         => 'nullable|string|max:255',
            'field'         => 'nullable|string|max:255',
            'topic'         => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'output'        => 'nullable|string|max:255',
            'deadline'      => 'required|date',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'postal_code'   => 'nullable|string|max:10',
            'willing_dp'    => 'nullable|boolean',
            'budget'        => 'nullable|numeric|min:0',
            'status'        => 'required|in:pending,processing,final',
        ]);

        $project->update($validated);

        return redirect()->route('dashboard')->with('success', 'Project updated successfully.');
    }
}
