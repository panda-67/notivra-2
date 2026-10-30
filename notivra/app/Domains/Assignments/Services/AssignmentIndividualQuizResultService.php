<?php

namespace App\Domains\Assignments\Services;

use App\Models\AssignmentParticipant;
use Illuminate\Support\Collection;

class AssignmentIndividualQuizResultService
{
    public function rows(array $filters = []): Collection
    {
        $participants = AssignmentParticipant::query()
            ->with([
                'student',
                'assignment.quizQuestions',
                'attempt',
            ])
            ->when(
                ! empty($filters['assignment_id']),
                fn($query) => $query->where(
                    'assignment_id',
                    $filters['assignment_id']
                )
            )
            ->whereHas('assignment')
            ->orderBy('assignment_id')
            ->orderBy('id')
            ->get();

        $questionsByAssignment = [];

        $rows = $participants->map(function ($participant) use (
            &$questionsByAssignment
        ) {
            $assignment = $participant->assignment;
            $student = $participant->student;
            $attempt = $participant->attempt;

            if (! $assignment) {
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
            $answers = $attempt?->answers ?? [];

            $total = $questions->count();
            $correct = 0;
            $incorrect = 0;
            $unanswered = 0;

            foreach ($questions as $question) {
                $answer = strtoupper(trim((string) (
                    $answers[(string) $question->position] ?? ''
                )));

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

            $status = $attempt?->status ?? 'pending';

            $isFinal = in_array(
                $status,
                ['completed', 'expired'],
                true
            ) && $total > 0;

            return [
                'assignment_id' => $assignment->id,
                'assignment_title' => $assignment->title,
                'participant_id' => $participant->id,
                'student_id' => $student?->id,
                'student_name' => $student?->name ?? '-',
                'student_number' => $student?->student_number ?? '-',
                'participant_status' => $participant->status,
                'quiz_status' => $status,
                'total_questions' => $total,
                'correct' => $correct,
                'incorrect' => $incorrect,
                'unanswered' => $unanswered,
                'score' => $isFinal
                    ? round(($correct / $total) * 100, 2)
                    : null,
                'score_is_final' => $isFinal,
                'started_at' => $attempt?->started_at,
                'expires_at' => $attempt?->expires_at,
                'completed_at' => $attempt?->completed_at,
                'expired_at' => $attempt?->expired_at,
            ];
        })->filter()->values();

        if (! empty($filters['status'])) {
            $status = $filters['status'];

            $rows = $rows->filter(
                fn(array $row) => $row['quiz_status'] === $status
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
                );
            })->values();
        }

        return $rows;
    }
}
