<?php

namespace App\Domains\Assignments\Services;

use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentSubmission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class AssignmentSubmissionService
{
    public function __construct(
        private readonly DocxFileValidator $docxValidator,
    ) {}

    public function submit(Assignment $assignment, AssignmentGroup $group, UploadedFile $file,): AssignmentSubmission
    {
        $this->docxValidator->validate($file);
        $this->validateEligibility($assignment, $group);

        $disk = Storage::disk('local');
        $directory = "assignment-submissions/{$assignment->id}/{$group->id}";
        $filename = bin2hex(random_bytes(24)) . '.docx';
        $temporaryPath = $file->getRealPath();

        $sha256 = hash_file('sha256', $temporaryPath);

        if ($sha256 === false) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file could not be processed.',
            ]);
        }

        $path = $file->storeAs($directory, $filename, 'local');

        if ($path === false) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file could not be stored.',
            ]);
        }

        try {
            return DB::transaction(function () use (
                $assignment,
                $group,
                $file,
                $disk,
                $path,
                $sha256,
            ) {
                // Lock the group to serialize concurrent submissions.
                $lockedGroup = AssignmentGroup::query()
                    ->where('assignment_id', $assignment->id)
                    ->lockForUpdate()
                    ->findOrFail($group->id);

                $this->validateEligibility($assignment->fresh(), $lockedGroup);

                $submission = AssignmentSubmission::query()->create([
                    'assignment_id' => $assignment->id,
                    'assignment_group_id' => $lockedGroup->id,
                    'uploaded_by_member_id' => null,
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType()
                        ?: 'application/octet-stream',
                    'file_size' => $file->getSize(),
                    'sha256' => $sha256,
                    'duplicate_status' => 'pending',
                    'metadata' => [],
                    'duplicate_findings' => [],
                    'submitted_at' => now(),
                ]);

                $lockedGroup->forceFill([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ])->save();

                return $submission;
            });
        } catch (Throwable $exception) {
            // The database transaction cannot roll back filesystem changes.
            $disk->delete($path);

            throw $exception;
        }
    }

    private function validateEligibility(
        Assignment $assignment,
        AssignmentGroup $group,
    ): void {
        if ($assignment->status !== 'published') {
            throw ValidationException::withMessages([
                'assignment' => 'This assignment is not accepting submissions.',
            ]);
        }

        if ($assignment->due_at && $assignment->due_at->lte(now())) {
            throw ValidationException::withMessages([
                'assignment' => 'The submission deadline has passed.',
            ]);
        }

        if ((int) $group->assignment_id !== (int) $assignment->id) {
            throw ValidationException::withMessages([
                'group' => 'This group does not belong to the assignment.',
            ]);
        }

        if ($group->status !== 'ready_for_upload') {
            throw ValidationException::withMessages([
                'group' => 'The group is not ready to submit.',
            ]);
        }

        if ($group->submission()->exists()) {
            throw ValidationException::withMessages([
                'file' => 'This group has already submitted its assignment.',
            ]);
        }

        $hasIncompleteMembers = $group->members()
            ->whereDoesntHave('verification', function ($query) {
                $query->where('status', 'completed');
            })
            ->exists();

        if ($hasIncompleteMembers) {
            throw ValidationException::withMessages([
                'group' => 'All group members must complete verification before submission.',
            ]);
        }
    }
}
