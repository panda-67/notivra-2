<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentVerificationService;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentQuizQuestion;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentQuizControllerTest extends TestCase
{
    use RefreshDatabase;

    private function deviceToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function createQuizSession(): array
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        $student = Student::factory()->create();

        // Membuat member langsung untuk menghindari side effect
        // AssignmentGroupMemberFactory yang membuat group tambahan.
        $member = AssignmentGroupMember::query()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        $verification = AssignmentVerification::factory()->create([
            'assignment_group_member_id' => $member->id,
            'status' => 'pending',
            'answers' => [],
            'started_at' => null,
            'quiz_expires_at' => null,
            'completed_at' => null,
            'start_token_hash' => null,
            'start_token_encrypted' => null,
            'start_token_used_at' => null,
            'token_revoked_at' => null,
        ]);

        foreach (range(1, 10) as $position) {
            AssignmentQuizQuestion::query()->create([
                'assignment_id' => $assignment->id,
                'position' => $position,
                'question' => "Pertanyaan pengujian nomor {$position}",
                'option_a' => 'Pilihan A',
                'option_b' => 'Pilihan B',
                'option_c' => 'Pilihan C',
                'option_d' => 'Pilihan D',
                'correct_option' => 'B',
            ]);
        }

        $startToken = app(AssignmentVerificationService::class)
            ->issueStartToken($verification);

        return [
            'assignment' => $assignment,
            'group' => $group,
            'verification' => $verification,
            'start_token' => $startToken,
        ];
    }

    private function startQuiz(string $startToken): string
    {
        $response = $this->postJson('/assignments/quiz/start', [
            'start_token' => $startToken,
            'device_token' => $this->deviceToken(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('session.status', 'in_progress')
            ->assertJsonPath('session.started_at', null)
            ->assertJsonPath('session.expires_at', null);

        $resumeToken = $response->json('resume_token');

        $this->assertIsString($resumeToken);
        $this->assertNotEmpty($resumeToken);

        return $resumeToken;
    }

    public function test_start_token_can_only_be_used_once(): void
    {
        $data = $this->createQuizSession();

        $this->startQuiz($data['start_token']);

        $this->postJson('/assignments/quiz/start', [
            'start_token' => $data['start_token'],
            'device_token' => $this->deviceToken(),
        ])->assertUnprocessable();

        $data['verification']->refresh();

        $this->assertSame('in_progress', $data['verification']->status);
        $this->assertNotNull($data['verification']->start_token_used_at);
        $this->assertNull($data['verification']->quiz_expires_at);
    }

    public function test_active_quiz_does_not_expose_correct_answers(): void
    {
        $data = $this->createQuizSession();
        $resumeToken = $this->startQuiz($data['start_token']);

        $response = $this->withToken($resumeToken)
            ->getJson('/assignments/quiz/session');

        $response->assertOk()
            ->assertJsonCount(10, 'session.questions');

        foreach ($response->json('session.questions') as $question) {
            $this->assertArrayNotHasKey('correct_option', $question);
        }
    }

    public function test_first_saved_answer_starts_the_50_minute_timer(): void
    {
        $data = $this->createQuizSession();
        $resumeToken = $this->startQuiz($data['start_token']);

        // Sesi baru belum memiliki jawaban.
        $this->assertEmpty(
            $data['verification']->fresh()->answers,
        );

        $this->travelTo(now()->startOfSecond());
        $savedAt = now();

        // Kirim jawaban tepat satu kali.
        $response = $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'B',
            ]);

        $response->assertOk()
            ->assertJsonPath('session.answers.1', 'B');

        $verification = $data['verification']->fresh();

        $this->assertSame('in_progress', $verification->status);
        $this->assertSame('B', $verification->answers['1'] ?? null);

        $this->assertNotNull($verification->started_at);
        $this->assertNotNull($verification->quiz_expires_at);

        $this->assertSame(
            $savedAt->timestamp,
            $verification->started_at->timestamp,
        );

        $this->assertSame(
            $savedAt->copy()->addMinutes(50)->timestamp,
            $verification->quiz_expires_at->timestamp,
        );
    }

    public function test_saved_answer_cannot_be_overwritten(): void
    {
        $data = $this->createQuizSession();
        $resumeToken = $this->startQuiz($data['start_token']);

        // Sesi baru harus dimulai tanpa jawaban.
        $this->assertEmpty(
            $data['verification']->fresh()->answers,
        );

        // Simpan jawaban pertama.
        $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'A',
            ])
            ->assertOk()
            ->assertJsonPath('session.answers.1', 'A');

        $this->assertSame(
            'A',
            $data['verification']->fresh()->answers['1'] ?? null,
        );

        // Jawaban kedua untuk soal yang sama harus ditolak.
        $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'C',
            ])
            ->assertUnprocessable();

        // Jawaban pertama tetap tersimpan.
        $this->assertSame(
            'A',
            $data['verification']->fresh()->answers['1'] ?? null,
        );
    }

    public function test_resuming_quiz_does_not_reset_the_deadline(): void
    {
        $data = $this->createQuizSession();
        $resumeToken = $this->startQuiz($data['start_token']);

        $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'B',
            ])
            ->assertOk();

        $verification = $data['verification']->fresh();
        $originalDeadline = $verification->quiz_expires_at->timestamp;

        $this->travel(5)->minutes();

        $this->withToken($resumeToken)
            ->postJson('/assignments/quiz/resume')
            ->assertOk();

        $verification->refresh();

        $this->assertSame(
            $originalDeadline,
            $verification->quiz_expires_at->timestamp,
        );

        $remainingSeconds = $this->withToken($resumeToken)
            ->getJson('/assignments/quiz/session')
            ->assertOk()
            ->json('session.remaining_seconds');

        $this->assertGreaterThanOrEqual(2699, $remainingSeconds);
        $this->assertLessThanOrEqual(2700, $remainingSeconds);
    }

    public function test_expired_quiz_is_finalized_and_answer_is_not_saved(): void
    {
        $data = $this->createQuizSession();
        $resumeToken = $this->startQuiz($data['start_token']);

        $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'A',
            ])
            ->assertOk();

        $verification = $data['verification']->fresh();
        $deadline = $verification->quiz_expires_at;

        $this->travelTo($deadline);

        $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/2', [
                'answer' => 'C',
            ])
            ->assertUnprocessable();

        $verification->refresh();

        $this->assertSame('expired', $verification->status);
        $this->assertNotNull($verification->token_revoked_at);
        $this->assertArrayNotHasKey('2', $verification->answers);

        $this->assertSame(
            'ready_for_upload',
            $data['group']->fresh()->status,
        );
    }

    public function test_answer_endpoint_rejects_invalid_positions_and_options(): void
    {
        $data = $this->createQuizSession();
        $resumeToken = $this->startQuiz($data['start_token']);

        $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/11', [
                'answer' => 'A',
            ])
            ->assertUnprocessable();

        $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'E',
            ])
            ->assertUnprocessable();

        $this->assertSame(
            [],
            $data['verification']->fresh()->answers,
        );
    }

    public function test_completing_quiz_with_unanswered_questions_revokes_session(): void
    {
        $data = $this->createQuizSession();
        $resumeToken = $this->startQuiz($data['start_token']);

        // Satu jawaban cukup untuk memulai timer.
        $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'B',
            ])
            ->assertOk();

        // Selesaikan kuis meskipun sembilan soal belum dijawab.
        $this->withToken($resumeToken)
            ->postJson('/assignments/quiz/complete')
            ->assertOk()
            ->assertJsonPath('status', 'completed');

        $verification = $data['verification']->fresh();

        $this->assertSame('completed', $verification->status);
        $this->assertNotNull($verification->completed_at);
        $this->assertNotNull($verification->token_revoked_at);

        // Jawaban yang belum diisi tetap kosong.
        $this->assertSame(
            ['1' => 'B'],
            $verification->answers,
        );

        // Token yang sudah dicabut tidak boleh menyimpan jawaban baru.
        $this->withToken($resumeToken)
            ->putJson('/assignments/quiz/answers/2', [
                'answer' => 'A',
            ])
            ->assertUnprocessable();

        $verification->refresh();

        $this->assertSame(
            ['1' => 'B'],
            $verification->answers,
        );

        // Karena semua anggota grup sudah menyelesaikan kuis,
        // grup satu anggota ini harus siap untuk upload.
        $this->assertSame(
            'ready_for_upload',
            $data['group']->fresh()->status,
        );
    }

    public function test_quiz_sessions_are_isolated_between_members(): void
    {
        $first = $this->createQuizSession();
        $firstToken = $this->startQuiz($first['start_token']);

        $second = $this->createQuizSession();
        $secondToken = $this->startQuiz($second['start_token']);

        // Anggota pertama menjawab soal nomor 1.
        $this->withToken($firstToken)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'B',
            ])
            ->assertOk()
            ->assertJsonPath('session.answers.1', 'B');

        // Jawaban anggota kedua tidak boleh ikut berubah.
        $this->assertSame(
            [],
            $second['verification']->fresh()->answers,
        );

        // Token anggota kedua hanya melihat sesi miliknya sendiri.
        $this->withToken($secondToken)
            ->getJson('/assignments/quiz/session')
            ->assertOk()
            ->assertJsonPath('session.answers', []);

        // Selesaikan sesi kedua agar kedua token tetap dapat
        // diverifikasi secara independen pada pengujian ini.
        $this->withToken($secondToken)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'A',
            ])
            ->assertOk();

        $this->assertSame(
            ['1' => 'B'],
            $first['verification']->fresh()->answers,
        );

        $this->assertSame(
            ['1' => 'A'],
            $second['verification']->fresh()->answers,
        );
    }

    public function test_expired_member_is_finalized_without_prematurely_updating_group_readiness(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        // Buat 10 soal untuk assignment yang sama.
        for ($position = 1; $position <= 10; $position++) {
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

        // Anggota pertama.
        $studentA = Student::factory()->create();

        $memberA = AssignmentGroupMember::query()->create([
            'assignment_group_id' => $group->id,
            'assignment_id' => $assignment->id,
            'student_id' => $studentA->id,
        ]);

        $verificationA = AssignmentVerification::factory()->create([
            'assignment_group_member_id' => $memberA->id,
            'status' => 'pending',
            'answers' => [],
            'started_at' => null,
            'quiz_expires_at' => null,
            'completed_at' => null,
            'token_revoked_at' => null,
            'start_token_hash' => null,
            'start_token_used_at' => null,
        ]);

        $startTokenA = app(AssignmentVerificationService::class)
            ->issueStartToken($verificationA);

        // Anggota kedua.
        $studentB = Student::factory()->create();

        $memberB = AssignmentGroupMember::query()->create([
            'assignment_group_id' => $group->id,
            'assignment_id' => $assignment->id,
            'student_id' => $studentB->id,
        ]);

        $verificationB = AssignmentVerification::factory()->create([
            'assignment_group_member_id' => $memberB->id,
            'status' => 'pending',
            'answers' => [],
            'started_at' => null,
            'quiz_expires_at' => null,
            'completed_at' => null,
            'token_revoked_at' => null,
            'start_token_hash' => null,
            'start_token_used_at' => null,
        ]);

        $startTokenB = app(AssignmentVerificationService::class)
            ->issueStartToken($verificationB);

        // Mulai sesi A.
        $responseA = $this->postJson('/assignments/quiz/start', [
            'start_token' => $startTokenA,
            'device_token' => $this->deviceToken(),
        ]);

        $responseA->assertCreated();

        $resumeTokenA = $responseA->json('resume_token');

        // Mulai sesi B.
        $responseB = $this->postJson('/assignments/quiz/start', [
            'start_token' => $startTokenB,
            'device_token' => $this->deviceToken(),
        ]);

        $responseB->assertCreated();

        $resumeTokenB = $responseB->json('resume_token');

        // Jawaban pertama memulai timer A.
        $this->withToken($resumeTokenA)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'B',
            ])
            ->assertSuccessful();

        // Jawaban pertama memulai timer B.
        $this->withToken($resumeTokenB)
            ->putJson('/assignments/quiz/answers/1', [
                'answer' => 'A',
            ])
            ->assertSuccessful();

        $verificationA->refresh();
        $verificationB->refresh();

        $this->assertNotNull($verificationA->quiz_expires_at);
        $this->assertNotNull($verificationB->quiz_expires_at);

        /*
        * Kedaluwarsakan sesi A secara eksplisit berdasarkan deadline A.
        *
        * Deadline B juga ikut terlewati karena kedua timer dimulai
        * hampir bersamaan. Karena itu, setelah A difinalisasi kita
        * tidak langsung menyelesaikan B dari waktu yang sama.
        */
        $this->travelTo(
            $verificationA->quiz_expires_at->copy()->addMinute()
        );

        // Endpoint resume A harus memfinalisasi sesi A.
        $this->withToken($resumeTokenA)
            ->postJson('/assignments/quiz/resume')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quiz');

        $verificationA->refresh();
        $verificationB->refresh();
        $group->refresh();

        // A harus berstatus expired karena waktu kuis habis.
        $this->assertSame('expired', $verificationA->status);
        $this->assertNotNull($verificationA->token_revoked_at);
        $this->assertSame(['1' => 'B'], $verificationA->answers);

        // B belum diproses oleh endpoint expiration.
        $this->assertSame('in_progress', $verificationB->status);

        // Grup belum siap karena B belum selesai.
        $this->assertSame('verification_in_progress', $group->status);

        /*
     * Untuk menyelesaikan B, kita harus memulai kembali waktu
     * pada kondisi yang masih memungkinkan B diselesaikan.
     *
     * Karena deadline B hampir sama dengan A, kita gunakan
     * complete() sebelum melakukan pengecekan expiration B.
     */
        $this->travelTo(
            $verificationB->quiz_expires_at->copy()->subMinute()
        );

        $this->withToken($resumeTokenB)
            ->postJson('/assignments/quiz/complete')
            ->assertSuccessful();

        $verificationB->refresh();
        $group->refresh();

        $this->assertSame('completed', $verificationB->status);

        // Semua anggota sekarang completed.
        $this->assertSame('ready_for_upload', $group->status);
    }
}
