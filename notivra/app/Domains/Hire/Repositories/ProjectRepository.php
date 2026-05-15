<?php

namespace App\Domains\Hire\Repositories;

use App\Models\Project;
use App\Models\File;
use App\Models\HireDraft;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProjectRepository
{
    public function submitProject(HireDraft $draft, array $validated)
    {
        return DB::transaction(function () use ($draft, $validated) {
            // 1. Update Draft
            $draft->update([
                'data' => array_merge($draft->data ?? [], $validated),
                'status' => 'submitted'
            ]);

            $data = $draft->fresh()->data;

            // 2. Create Project
            $project = Project::create([
                'service_id'    => $data['service_id'],
                'name'          => Auth::user()->name ?? $data['name'],
                'email'         => Auth::user()->email ?? $data['email'],
                'phone'         => $data['phone'] ?? null,
                'institution'   => $data['institution'] ?? null,
                'level'         => $data['level'] ?? null,
                'field'         => $data['field'] ?? null,
                'topic'         => $data['topic'] ?? null,
                'description'   => $data['description'] ?? null,
                'output'        => $data['output'] ?? null,
                'deadline'      => $data['deadline'],
                'address'       => $data['address'] ?? null,
                'city'          => $data['city'] ?? null,
                'postal_code'   => $data['postal_code'] ?? null,
                'willing_dp'    => $data['willing_dp'] ?? false,
                'budget'        => $data['budget'] ?? 0,
                'files'         => $data['files'] ?? null,
                'user_id'       => Auth::id() ?? null,
                'status'        => 'pending',
            ]);

            // 3. Handle Files
            if ($project->files && Storage::disk('local')->exists($project->files)) {
                $fileName = basename($project->files);
                $newPath = "research_drafts/{$project->id}/{$fileName}";

                Storage::disk('local')->move($project->files, $newPath);
                $project->update(['files' => $newPath]);

                File::create([
                    'project_id' => $project->id,
                    'user_id'    => Auth::id(),
                    'name'       => $fileName,
                    'path'       => $newPath,
                    'mime_type'  => Storage::disk('local')->mimeType($newPath),
                    'size'       => Storage::disk('local')->size($newPath),
                ]);
            }

            return $project;
        });
    }
}
