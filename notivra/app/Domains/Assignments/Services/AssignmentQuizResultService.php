<?php

namespace App\Domains\Assignments\Services;

use App\Models\AssignmentGroupMember;
use Illuminate\Support\Collection;

class AssignmentQuizResultService
{
    public function __construct(
        private readonly AssignmentGroupUnitParser $unitParser,
    ) {}

    /**
     * Mengambil hasil kuis seluruh anggota kelompok.
     *
     * Filter yang didukung:
     * - assignment_id
     * - unit
     * - status
     * - search
     */
    public function rows(array $filters = []): Collection
    {
        $members = AssignmentGroupMember::query()
            ->with([
                'student',
                'group.assignment.quizQuestions',
                'verification',
            ])
            ->when(
                ! empty($filters['assignment_id']),
                fn($query) => $query->where(
                    'assignment_id',
                    $filters['assignment_id']
                )
            )
            ->whereHas('group.assignment')
            ->orderBy('assignment_id')
            ->orderBy('assignment_group_id')
            ->orderBy('id')
            ->get();

        $questionsByAssignment = [];

        $rows = $members->map(function ($member) use (
            &$questionsByAssignment
        ) {
            $group = $member->group;
            $assignment = $group?->assignment;
            $student = $member->student;
            $verification = $member->verification;

            if (! $assignment || ! $group) {
                return null;
            }

            $assignmentId = $assignment->id;

            if (! isset($questionsByAssignment[$assignmentId])) {
                $questionsByAssignment[$assignmentId] =
                    $assignment->quizQuestions
                    ->sortBy('position')
                    ->values();
            }

            $questions = $questionsByAssignment[$assignmentId];
            $answers = $verification?->answers ?? [];

            $totalQuestions = $questions->count();
            $correct = 0;
            $incorrect = 0;
            $unanswered = 0;

            foreach ($questions as $question) {
                $position = (string) $question->position;
                $answer = strtoupper(
                    trim((string) ($answers[$position] ?? ''))
                );

                if ($answer === '') {
                    $unanswered++;
                } elseif (
                    $answer === strtoupper(
                        (string) $question->correct_option
                    )
                ) {
                    $correct++;
                } else {
                    $incorrect++;
                }
            }

            $status = $verification?->status ?? 'pending';

            // Skor final hanya ditampilkan untuk sesi yang selesai
            // atau kedaluwarsa.
            $isFinal = in_array(
                $status,
                ['completed', 'expired'],
                true
            ) && $totalQuestions > 0;

            $score = $isFinal
                ? round(($correct / $totalQuestions) * 100, 2)
                : null;

            return [
                'assignment_id' => $assignment->id,
                'assignment_title' => $assignment->title,
                'assignment_slug' => $assignment->slug,

                'unit' => $this->unitParser->parse(
                    $group->group_name
                ),
                'unit_label' => $this->unitParser->label(
                    $group->group_name
                ),

                'group_id' => $group->id,
                'group_name' => $group->group_name,
                'reference_code' => $group->reference_code,

                'student_id' => $student?->id,
                'student_name' => $student?->name ?? '-',
                'student_number' => $student?->student_number ?? '-',
                'program_study' => $student?->program_study ?? '-',

                'verification_status' => $status,
                'total_questions' => $totalQuestions,
                'correct' => $correct,
                'incorrect' => $incorrect,
                'unanswered' => $unanswered,
                'score' => $score,
                'score_is_final' => $isFinal,

                'started_at' => $verification?->started_at,
                'quiz_expires_at' => $verification?->quiz_expires_at,
                'completed_at' => $verification?->completed_at,
            ];
        })->filter()->values();

        if (isset($filters['unit']) && $filters['unit'] !== '') {
            $unit = (int) $filters['unit'];

            $rows = $rows->filter(
                fn(array $row) => $row['unit'] === $unit
            )->values();
        }

        if (! empty($filters['status'])) {
            $status = $filters['status'];

            $rows = $rows->filter(
                fn(array $row) =>
                $row['verification_status'] === $status
            )->values();
        }

        if (! empty($filters['search'])) {
            $search = mb_strtolower(trim($filters['search']));

            $rows = $rows->filter(function (array $row) use ($search) {
                return str_contains(
                    mb_strtolower($row['student_name']),
                    $search
                ) || str_contains(
                    mb_strtolower($row['student_number']),
                    $search
                ) || str_contains(
                    mb_strtolower($row['group_name']),
                    $search
                );
            })->values();
        }

        return $rows;
    }

    /**
     * Menghitung jumlah nama kelompok yang tidak cocok
     * dengan pola Unit 1-3.
     */
    public function unmappedGroupNames(
        ?int $assignmentId = null
    ): Collection {
        return \App\Models\AssignmentGroup::query()
            ->when(
                $assignmentId,
                fn($query) => $query->where(
                    'assignment_id',
                    $assignmentId
                )
            )
            ->get(['group_name'])
            ->filter(
                fn($group) =>
                $this->unitParser->parse($group->group_name) === null
            )
            ->pluck('group_name')
            ->unique()
            ->values();
    }
}
