<?php

namespace Tests\Feature\Assignment;

use App\Models\Assignment;
use App\Models\AssignmentIndividualQuizSetting;
use App\Models\AssignmentParticipant;
use App\Models\AssignmentQuizAttempt;
use App\Models\AssignmentQuizQuestion;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentIndividualQuizControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $deviceToken;

    private function createQuiz(): array
    {
        $assignment = Assignment::factory()->create([
            'opens_at' => now()->subDay(),
            'due_at' => now()->addDays(2),
            'published_at' => now()->subMinute(),
            'status' => 'published',
        ]);

        AssignmentIndividualQuizSetting::query()->create([
            'assignment_id' => $assignment->id,
            'duration_minutes' => 50,
        ]);

        $student = Student::factory()->create();

        $participant = AssignmentParticipant::query()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'status' => 'registered',
        ]);

        /*
         * Bank soal:
         *
         *  1–12  = RAL / one-way ANOVA
         * 13–24  = RAK / RCBD
         * 25–36  = RBSL / Latin Square
         * 37–48  = RAK factorial
         * 49–60  = Split plot
         *
         * Masing-masing kelompok memiliki 12 soal.
         * Service akan memilih 4 soal secara acak dari setiap kelompok.
         */
        foreach (range(1, 60) as $position) {
            AssignmentQuizQuestion::query()->create([
                'assignment_id' => $assignment->id,
                'position' => $position,
                'question' => "Pertanyaan {$position}",
                'option_a' => 'Pilihan A',
                'option_b' => 'Pilihan B',
                'option_c' => 'Pilihan C',
                'option_d' => 'Pilihan D',
                'correct_option' => 'B',
            ]);
        }

        $this->deviceToken = bin2hex(random_bytes(32));

        return compact('assignment', 'student', 'participant');
    }

    private function startQuiz(array $data): array
    {
        $response = $this->postJson(
            "/assignments/{$data['assignment']->slug}/individual-quiz/start",
            [
                'student_number' => $data['student']->student_number,
                'device_token' => $this->deviceToken,
            ],
        );

        $response->assertCreated()
            ->assertJsonPath('session.status', 'in_progress')
            ->assertJsonCount(20, 'session.questions');

        $resumeToken = $response->json('resume_token');

        $this->assertIsString($resumeToken);
        $this->assertNotEmpty($resumeToken);

        return [
            'token' => $resumeToken,
            'session' => $response->json('session'),
            'attempt' => AssignmentQuizAttempt::query()
                ->where(
                    'assignment_participant_id',
                    $data['participant']->id,
                )
                ->firstOrFail(),
        ];
    }

    private function quizRequest(string $token)
    {
        return $this->withHeaders([
            'X-Device-Token' => $this->deviceToken,
        ])->withToken($token);
    }

    public function test_participant_can_start_individual_quiz(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        $attempt = $result['attempt']->fresh();

        $this->assertSame('in_progress', $attempt->status);
        $this->assertEmpty($attempt->answers);
        $this->assertNotNull($attempt->started_at);
        $this->assertNotNull($attempt->expires_at);

        $this->assertSame(
            $attempt->started_at->copy()->addMinutes(50)->timestamp,
            $attempt->expires_at->timestamp,
        );

        $this->assertNull($attempt->completed_at);
        $this->assertNull($attempt->expired_at);

        $this->assertCount(20, $attempt->question_ids);
        $this->assertCount(20, array_unique($attempt->question_ids));
    }

    public function test_question_snapshot_contains_four_questions_from_each_group(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        $questionIds = $result['attempt']->fresh()->question_ids;

        $questions = AssignmentQuizQuestion::query()
            ->whereIn('id', $questionIds)
            ->get(['id', 'position']);

        $this->assertCount(20, $questions);

        $ranges = [
            [1, 12],
            [13, 24],
            [25, 36],
            [37, 48],
            [49, 60],
        ];

        foreach ($ranges as [$start, $end]) {
            $count = $questions
                ->whereBetween('position', [$start, $end])
                ->count();

            $this->assertSame(
                4,
                $count,
                "Kelompok posisi {$start}–{$end} harus memiliki tepat 4 soal.",
            );
        }
    }

    public function test_question_snapshot_is_persisted_and_not_reshuffled_on_resume(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        $attempt = $result['attempt']->fresh();

        $originalQuestionIds = $attempt->question_ids;

        $response = $this->postJson(
            "/assignments/{$data['assignment']->slug}/individual-quiz/start",
            [
                'student_number' => $data['student']->student_number,
                'device_token' => $this->deviceToken,
            ],
        );

        $response->assertCreated()
            ->assertJsonPath('session.status', 'in_progress')
            ->assertJsonCount(20, 'session.questions');

        $resumedAttempt = $attempt->fresh();

        $this->assertSame(
            $originalQuestionIds,
            $resumedAttempt->question_ids,
        );

        $resumedQuestionIds = collect(
            $response->json('session.questions')
        )->pluck('id')->values()->all();

        $this->assertSame(
            $originalQuestionIds,
            $resumedQuestionIds,
        );
    }

    public function test_session_does_not_expose_correct_option(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        foreach ($result['session']['questions'] as $question) {
            $this->assertArrayNotHasKey('correct_option', $question);
        }
    }

    public function test_session_contains_only_questions_from_snapshot(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        $snapshotIds = $result['attempt']->fresh()->question_ids;

        $sessionIds = collect($result['session']['questions'])
            ->pluck('id')
            ->values()
            ->all();

        $this->assertSame($snapshotIds, $sessionIds);
    }

    public function test_participant_can_change_answer_while_quiz_is_active(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        $selectedPosition = AssignmentQuizQuestion::query()
            ->whereIn('id', $result['attempt']->fresh()->question_ids)
            ->value('position');

        $this->assertNotNull($selectedPosition);

        $url = "/assignments/{$data['assignment']->slug}"
            . "/individual-quiz/answers/{$selectedPosition}";

        $this->quizRequest($result['token'])
            ->putJson($url, ['answer' => 'A'])
            ->assertOk()
            ->assertJsonPath(
                "session.answers.{$selectedPosition}",
                'A',
            );

        $this->quizRequest($result['token'])
            ->putJson($url, ['answer' => 'C'])
            ->assertOk()
            ->assertJsonPath(
                "session.answers.{$selectedPosition}",
                'C',
            );

        $this->assertSame(
            'C',
            $result['attempt']->fresh()->answers[(string) $selectedPosition],
        );

        $this->assertSame(
            'in_progress',
            $result['attempt']->fresh()->status,
        );
    }

    public function test_answer_for_question_outside_snapshot_is_rejected(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        $snapshotPositions = AssignmentQuizQuestion::query()
            ->whereIn('id', $result['attempt']->fresh()->question_ids)
            ->pluck('position')
            ->all();

        $unselectedPosition = collect(range(1, 60))
            ->first(
                fn($position) => ! in_array(
                    $position,
                    $snapshotPositions,
                    true,
                )
            );

        $this->assertNotNull($unselectedPosition);

        $url = "/assignments/{$data['assignment']->slug}"
            . "/individual-quiz/answers/{$unselectedPosition}";

        $this->quizRequest($result['token'])
            ->putJson($url, ['answer' => 'A'])
            ->assertUnprocessable();

        $attempt = $result['attempt']->fresh();

        $this->assertArrayNotHasKey(
            (string) $unselectedPosition,
            $attempt->answers ?? [],
        );
    }

    public function test_expired_quiz_is_recorded_and_new_answer_is_rejected(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        $selectedPosition = AssignmentQuizQuestion::query()
            ->whereIn('id', $result['attempt']->fresh()->question_ids)
            ->value('position');

        $this->assertNotNull($selectedPosition);

        $url = "/assignments/{$data['assignment']->slug}"
            . "/individual-quiz/answers/{$selectedPosition}";

        $this->quizRequest($result['token'])
            ->putJson($url, ['answer' => 'A'])
            ->assertOk();

        $attempt = $result['attempt']->fresh();
        $deadline = $attempt->expires_at;

        $this->travelTo($deadline);

        $this->quizRequest($result['token'])
            ->putJson($url, ['answer' => 'C'])
            ->assertUnprocessable();

        $attempt->refresh();

        $this->assertSame('expired', $attempt->status);
        $this->assertNotNull($attempt->expired_at);
        $this->assertNull($attempt->completed_at);

        $this->assertSame(
            $deadline->timestamp,
            $attempt->expired_at->timestamp,
        );

        // Jawaban lama tetap tersimpan; jawaban baru ditolak.
        $this->assertSame(
            'A',
            $attempt->answers[(string) $selectedPosition],
        );
    }

    public function test_completing_after_deadline_records_expiration(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        $deadline = $result['attempt']->expires_at;

        $this->travelTo($deadline);

        $response = $this->quizRequest($result['token'])
            ->postJson(
                "/assignments/{$data['assignment']->slug}"
                    . '/individual-quiz/complete',
            );

        $response->assertOk()
            ->assertJsonPath('result.status', 'expired');

        $attempt = $result['attempt']->fresh();

        $this->assertSame('expired', $attempt->status);
        $this->assertNotNull($attempt->expired_at);
        $this->assertNull($attempt->completed_at);
    }

    public function test_score_uses_selected_twenty_questions_including_unanswered_questions(): void
    {
        $data = $this->createQuiz();
        $result = $this->startQuiz($data);

        $baseUrl = "/assignments/{$data['assignment']->slug}"
            . '/individual-quiz';

        $selectedPosition = AssignmentQuizQuestion::query()
            ->whereIn('id', $result['attempt']->fresh()->question_ids)
            ->value('position');

        $this->assertNotNull($selectedPosition);

        // Hanya satu soal dijawab benar.
        $this->quizRequest($result['token'])
            ->putJson(
                "{$baseUrl}/answers/{$selectedPosition}",
                ['answer' => 'B'],
            )
            ->assertOk();

        // Selesaikan sebelum batas waktu; 19 soal lainnya kosong.
        $response = $this->quizRequest($result['token'])
            ->postJson("{$baseUrl}/complete");

        $response->assertOk()
            ->assertJsonPath('result.status', 'completed')
            ->assertJsonPath('result.correct_answers', 1)
            ->assertJsonPath('result.total_questions', 20)
            ->assertJsonPath('result.score_percentage', 5);

        $attempt = $result['attempt']->fresh();

        $this->assertSame('completed', $attempt->status);
        $this->assertNotNull($attempt->completed_at);
        $this->assertNull($attempt->expired_at);
    }
}
