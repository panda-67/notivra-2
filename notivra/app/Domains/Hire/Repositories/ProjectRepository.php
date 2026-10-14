<?php

namespace App\Domains\Hire\Repositories;

use App\Models\File;
use App\Models\HireDraft;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectRepository
{
    public function submitProject(HireDraft $draft, array $validated): ?Project
    {
        $disk = Storage::disk('local');
        $movedFile = null;

        try {
            return DB::transaction(function () use (
                $draft,
                $validated,
                $disk,
                &$movedFile
            ) {
                $lockedDraft = HireDraft::query()
                    ->whereKey($draft->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedDraft->status === 'submitted') {
                    return null;
                }

                if ($lockedDraft->status !== 'draft') {
                    throw new \DomainException(
                        'Status draft tidak memungkinkan submission.'
                    );
                }

                if ($lockedDraft->step < 3) {
                    throw new \DomainException(
                        'Lengkapi semua step sebelum mengirim.'
                    );
                }

                $data = array_merge(
                    $lockedDraft->data ?? [],
                    $validated
                );

                $user = Auth::user();

                if (! $user) {
                    throw new \RuntimeException(
                        'Pengguna tidak terautentikasi.'
                    );
                }

                $project = Project::create([
                    'service_id'  => $data['service_id'],
                    'name'        => $user->name ?? $data['name'],
                    'email'       => $user->email ?? $data['email'],
                    'phone'       => $data['phone'] ?? null,
                    'institution' => $data['institution'] ?? null,
                    'level'       => $data['level'] ?? null,
                    'field'       => $data['field'] ?? null,
                    'topic'       => $data['topic'] ?? null,
                    'description' => $data['description'] ?? null,
                    'output'      => $data['output'] ?? null,
                    'deadline'    => $data['deadline'],
                    'address'     => $data['address'] ?? null,
                    'city'        => $data['city'] ?? null,
                    'postal_code' => $data['postal_code'] ?? null,
                    'willing_dp'  => $data['willing_dp'] ?? false,
                    'budget'      => $data['budget'] ?? 0,
                    'files'       => $data['files'] ?? null,
                    'user_id'     => $user->id,
                    'status'      => 'pending',
                ]);

                if ($project->files !== null) {
                    $oldPath = $project->files;

                    if (! $disk->exists($oldPath)) {
                        throw new \RuntimeException(
                            'Lampiran draft tidak ditemukan.'
                        );
                    }

                    $fileName = basename($oldPath);
                    $newPath = "research_drafts/{$project->id}/{$fileName}";

                    if (! $disk->move($oldPath, $newPath)) {
                        throw new \RuntimeException(
                            'Gagal memindahkan lampiran proyek.'
                        );
                    }

                    $movedFile = ['old' => $oldPath, 'new' => $newPath];

                    $project->files = $newPath;
                    $project->save();

                    File::create([
                        'project_id' => $project->id,
                        'user_id'    => $user->id,
                        'name'       => $fileName,
                        'path'       => $newPath,
                        'mime_type'  => $disk->mimeType($newPath),
                        'size'       => $disk->size($newPath),
                    ]);
                }

                $lockedDraft->update(['data' => $data, 'status' => 'submitted']);

                return $project;
            });
        } catch (\Throwable $e) {
            if ($movedFile !== null) {
                try {
                    if (
                        $disk->exists($movedFile['new'])
                        && ! $disk->exists($movedFile['old'])
                    ) {
                        $disk->move(
                            $movedFile['new'],
                            $movedFile['old']
                        );
                    }
                } catch (\Throwable $restoreException) {
                    report($restoreException);
                }
            }

            throw $e;
        }
    }
}
