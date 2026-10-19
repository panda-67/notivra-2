<?php

namespace App\Domains\Assignments\Controllers;

use App\Domains\Assignments\Services\AssignmentSubmissionService;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssignmentSubmissionController extends Controller
{
    public function store(
        Request $request,
        string $slug,
        string $reference_code,
        AssignmentSubmissionService $submissionService,
    ): RedirectResponse {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:docx',
                'extensions:docx',
                'max:5120',
            ],
        ]);

        $assignment = Assignment::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $group = AssignmentGroup::query()
            ->where('assignment_id', $assignment->id)
            ->where('reference_code', $reference_code)
            ->firstOrFail();

        $submissionService->submit(
            $assignment,
            $group,
            $validated['file'],
        );

        return to_route('assignments.groups.show', [
            'slug' => $assignment->slug,
            'reference_code' => $group->reference_code,
        ])->with('success', 'Assignment submitted successfully.');
    }
}
