<?php

namespace App\Domains\Assignments\Services;

use App\Models\AssignmentGroup;
use Illuminate\Support\Facades\DB;

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
}
