<?php

namespace App\Domains\Hire\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\HireDraft;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Routing\Attributes\Controllers\Middleware;
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

        return inertia('Dashboard/Main', compact('projects', 'drafts'));
    }

    #[Middleware('auth')]
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

    #[Middleware('auth')]
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $alwaysEditable = [
            'name',
            'email',
            'phone',
            'institution',
            'city',
            'address',
            'description',
            'postal_code'
        ];

        $validated = $request->validated();

        if (in_array($project->status, ['processing', 'final'])) {
            $dataToUpdate = $request->only($alwaysEditable);

            if ($request->has('status')) {
                $dataToUpdate['status'] = $request->status;
            }

            $project->update($dataToUpdate);

            return redirect()->route('dashboard')
                ->with('success', 'Hanya data kontak & alamat yang diperbarui karena project sedang diproses.');
        }

        $project->update($validated);

        return redirect()->route('dashboard')->with('success', 'Projek berhasil diperbarui.');
    }
}
