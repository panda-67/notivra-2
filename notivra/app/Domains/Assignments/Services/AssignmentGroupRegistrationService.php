<?php

namespace App\Domains\Assignments\Services;

use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AssignmentGroupRegistrationService
{
    /**
     * Register a group and create a verification session for each member.
     *
     * Resume tokens are issued later when individual students start their quiz.
     */
    public function register(Assignment $assignment, ?string $groupName, array $members,): array
    {
        if ($assignment->status !== 'published') {
            throw ValidationException::withMessages([
                'assignment' => 'This assignment is not accepting registrations.',
            ]);
        }

        if ($assignment->opens_at && $assignment->opens_at->isFuture()) {
            throw ValidationException::withMessages([
                'assignment' => 'Registration has not opened yet.',
            ]);
        }

        if ($assignment->due_at && $assignment->due_at->isPast()) {
            throw ValidationException::withMessages([
                'assignment' => 'The registration deadline has passed.',
            ]);
        }

        if (count($members) < 1) {
            throw ValidationException::withMessages([
                'members' => 'At least one group member is required.',
            ]);
        }

        if (count($members) > 5) {
            throw ValidationException::withMessages([
                'members' => 'A group may contain at most 5 students.',
            ]);
        }

        $studentNumbers = array_map(
            fn(array $member) => trim($member['student_number']),
            $members,
        );

        if (count($studentNumbers) !== count(array_unique($studentNumbers))) {
            throw ValidationException::withMessages([
                'members' => 'A student number may only appear once in a group.',
            ]);
        }

        return DB::transaction(function () use (
            $assignment,
            $groupName,
            $members,
            $studentNumbers,
        ) {
            $group = AssignmentGroup::query()->create([
                'assignment_id' => $assignment->id,
                'group_name' => $groupName,
                'reference_code' => $this->generateReferenceCode(),
                'status' => 'verification_in_progress',
            ]);

            $invitations = [];

            foreach ($members as $index => $memberData) {
                $studentNumber = $studentNumbers[$index];

                $student = Student::query()
                    ->where('student_number', $studentNumber)
                    ->lockForUpdate()
                    ->first();

                if ($student && $student->status !== 'active') {
                    throw ValidationException::withMessages([
                        "members.{$index}.student_number" =>
                        'This student record is inactive.',
                    ]);
                }

                if (! $student) {
                    $student = Student::query()->create([
                        'student_number' => $studentNumber,
                        'name' => trim($memberData['name']),
                        'program_study' => $memberData['program_study'] ?? null,
                        'institution' => $memberData['institution'] ?? null,
                        'status' => 'active',
                    ]);
                }

                $membership = AssignmentGroupMember::query()->create([
                    'assignment_id' => $assignment->id,
                    'assignment_group_id' => $group->id,
                    'student_id' => $student->id,
                ]);

                $verification = AssignmentVerification::query()->create([
                    'assignment_group_member_id' => $membership->id,
                    'status' => 'pending',
                    'answers' => [],
                    'attempt_count' => 0,
                ]);

                app(AssignmentVerificationService::class)->issueStartToken($verification);

                $invitations[] = [
                    'student_number' => $student->student_number,
                    'student_name' => $student->name,
                ];
            }

            return [
                'group' => $group->fresh(),
                'invitations' => $invitations,
            ];
        });
    }

    private function generateReferenceCode(): string
    {
        do {
            $code = Str::upper(Str::random(12));
        } while (
            AssignmentGroup::query()
            ->where('reference_code', $code)
            ->exists()
        );

        return $code;
    }
}
