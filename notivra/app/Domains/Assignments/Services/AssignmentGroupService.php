<?php

namespace App\Domains\Assignments\Services;

use App\Models\AssignmentGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignmentGroupService
{
    /**
     * Recalculate whether a group is ready to upload its submission.
     */
    public function refreshUploadReadiness(AssignmentGroup $group): AssignmentGroup
    {
        return DB::transaction(function () use ($group) {
            $lockedGroup = AssignmentGroup::query()
                ->lockForUpdate()
                ->findOrFail($group->id);

            if ($lockedGroup->upload_bypassed_at !== null) {
                $lockedGroup->forceFill([
                    'status' => 'ready_for_upload',
                ])->save();

                return $lockedGroup->refresh();
            }

            // Do not overwrite a group's status after submission or review.
            if (! in_array($lockedGroup->status, [
                'draft',
                'verification_in_progress',
                'ready_for_upload',
            ], true)) {
                return $lockedGroup;
            }

            $members = $lockedGroup->members();

            if (! $members->exists()) {
                $lockedGroup->forceFill([
                    'status' => 'draft',
                ])->save();

                return $lockedGroup->refresh();
            }

            $hasUnverifiedMembers = $lockedGroup->members()
                ->whereDoesntHave('verification', function ($query) {
                    $query->whereIn('status', [
                        'completed',
                        'expired',
                    ]);
                })
                ->exists();

            $lockedGroup->forceFill([
                'status' => $hasUnverifiedMembers
                    ? 'verification_in_progress'
                    : 'ready_for_upload',
            ])->save();

            return $lockedGroup->refresh();
        });
    }

    public function bypassUpload(AssignmentGroup $group, User $user): AssignmentGroup
    {
        return DB::transaction(function () use ($group, $user) {
            $lockedGroup = AssignmentGroup::query()
                ->lockForUpdate()
                ->findOrFail($group->id);

            if ($lockedGroup->submission()->exists()) {
                throw ValidationException::withMessages([
                    'group' => 'Bypass tidak dapat dilakukan karena grup sudah memiliki submission.',
                ]);
            }

            if (! in_array($lockedGroup->status, [
                'draft',
                'verification_in_progress',
                'ready_for_upload',
            ], true)) {
                throw ValidationException::withMessages([
                    'group' => 'Status grup tidak mengizinkan bypass upload.',
                ]);
            }

            $lockedGroup->forceFill([
                'status' => 'ready_for_upload',
                'upload_bypassed_at' => now(),
                'upload_bypassed_by' => $user->id,
            ])->save();

            return $lockedGroup->refresh();
        });
    }
}
