<?php

namespace App\Domains\Assignments\Controllers;

use App\Domains\Assignments\Services\AssignmentGroupRegistrationService;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class AssignmentRegistrationController extends Controller
{
    public function create(string $slug): Response
    {
        $assignment = Assignment::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return inertia('Assignments/Register', [
            'assignment' => [
                'title' => $assignment->title,
                'slug' => $assignment->slug,
                'description' => $assignment->description,
                'instructions' => $assignment->instructions,
                'opens_at' => $assignment->opens_at?->toIso8601String(),
                'due_at' => $assignment->due_at?->toIso8601String(),
            ],
            'registrationResult' => session(
                'assignment_registration_result'
            ),
        ]);
    }

    public function store(
        Request $request,
        string $slug,
        AssignmentGroupRegistrationService $registrationService,
    ): RedirectResponse {
        $assignment = Assignment::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $validated = $request->validate([
            'group_name' => ['nullable', 'string', 'max:255'],

            'members' => ['required', 'array', 'min:1', 'max:5'],

            'members.*.student_number' => [
                'required',
                'string',
                'max:64',
            ],

            'members.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'members.*.program_study' => [
                'nullable',
                'string',
                'max:255',
            ],

            'members.*.institution' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $result = $registrationService->register(
            $assignment,
            $validated['group_name'] ?? null,
            $validated['members'],
        );

        return to_route('assignments.groups.show', [
            'slug' => $assignment->slug,
            'reference_code' => $result['group']->reference_code,
        ])->with('success', 'Pendaftaran grup berhasil.');
    }
}
