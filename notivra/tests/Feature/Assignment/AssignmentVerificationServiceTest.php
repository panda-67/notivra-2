<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentVerificationService;
use App\Models\Assignment;
use App\Models\AssignmentQuizDevice;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AssignmentVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_issuing_resume_token_stores_only_its_hash(): void
    {
        $verification = AssignmentVerification::factory()->create();

        $service = app(AssignmentVerificationService::class);

        $token = $service->issueResumeToken($verification);

        $verification->refresh();
        $this->assertSame('in_progress', $verification->status);
        $this->assertSame(hash('sha256', $token), $verification->resume_token_hash);
        $this->assertNotSame($token, $verification->resume_token_hash);

        // Timer belum dimulai sebelum jawaban pertama disimpan.
        $this->assertNull($verification->started_at);
        $this->assertNull($verification->quiz_expires_at);

        $this->assertNotNull($verification->token_expires_at);
        $this->assertNull($verification->token_revoked_at);
    }

    public function test_valid_resume_token_returns_verification_session(): void
    {
        $token = bin2hex(random_bytes(32));

        $verification = AssignmentVerification::factory()->create([
            'status' => 'in_progress',
            'resume_token_hash' => hash('sha256', $token),
            'token_expires_at' => now()->addHour(),
            'token_revoked_at' => null,
        ]);

        $result = app(AssignmentVerificationService::class)->resume($token);

        $this->assertSame($verification->id, $result->id);
        $this->assertNotNull($result->last_seen_at);
    }

    public function test_invalid_resume_token_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        app(AssignmentVerificationService::class)->resume('invalid-token');
    }

    public function test_expired_resume_token_is_rejected_and_session_marked_expired(): void
    {
        $token = bin2hex(random_bytes(32));

        $verification = AssignmentVerification::factory()->create([
            'status' => 'in_progress',
            'resume_token_hash' => hash('sha256', $token),
            'token_expires_at' => now()->subMinute(),
            'token_revoked_at' => null,
        ]);

        try {
            app(AssignmentVerificationService::class)->resume($token);
            $this->fail('An expired token should be rejected.');
        } catch (ValidationException) {
            // Expected.
        }

        $verification->refresh();

        $this->assertSame('expired', $verification->status);
        $this->assertNotNull($verification->token_revoked_at);
    }

    public function test_revoked_resume_token_is_rejected(): void
    {
        $token = bin2hex(random_bytes(32));

        AssignmentVerification::factory()->create([
            'status' => 'in_progress',
            'resume_token_hash' => hash('sha256', $token),
            'token_expires_at' => now()->addHour(),
            'token_revoked_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        app(AssignmentVerificationService::class)->resume($token);
    }

    public function test_completed_verification_cannot_be_resumed(): void
    {
        $token = bin2hex(random_bytes(32));

        AssignmentVerification::factory()->completed()->create([
            'resume_token_hash' => hash('sha256', $token),
            'token_expires_at' => now()->addHour(),
            'token_revoked_at' => now(),
        ]);

        $this->expectException(ValidationException::class);

        app(AssignmentVerificationService::class)->resume($token);
    }

    public function test_completing_verification_revokes_resume_token(): void
    {
        $verification = AssignmentVerification::factory()->create([
            'status' => 'in_progress',
            'started_at' => now()->subMinutes(5),
            'token_expires_at' => now()->addHour(),
        ]);

        app(AssignmentVerificationService::class)->complete($verification);

        $verification->refresh();

        $this->assertSame('completed', $verification->status);
        $this->assertNotNull($verification->completed_at);
        $this->assertNotNull($verification->token_revoked_at);
    }

    public function test_pending_verification_cannot_be_completed_directly(): void
    {
        $verification = AssignmentVerification::factory()->create([
            'status' => 'pending',
        ]);

        $this->expectException(ValidationException::class);

        app(AssignmentVerificationService::class)->complete($verification);
    }

    public function test_resume_token_can_be_revoked(): void
    {
        $verification = AssignmentVerification::factory()->create([
            'status' => 'in_progress',
            'token_revoked_at' => null,
        ]);

        app(AssignmentVerificationService::class)
            ->revokeResumeToken($verification);

        $verification->refresh();

        $this->assertNotNull($verification->token_revoked_at);
    }

    public function test_reset_session_clears_answers_and_issues_new_start_token(): void
    {
        $oldStartToken = bin2hex(random_bytes(32));
        $oldResumeToken = bin2hex(random_bytes(32));

        $verification = AssignmentVerification::factory()->create([
            'status' => 'in_progress',
            'answers' => [
                '1' => 'A',
                '2' => 'C',
            ],
            'start_token_hash' => hash('sha256', $oldStartToken),
            'start_token_encrypted' => $oldStartToken,
            'start_token_used_at' => now()->subMinutes(10),
            'resume_token_hash' => hash('sha256', $oldResumeToken),
            'token_expires_at' => now()->addHours(12),
            'token_revoked_at' => null,
            'started_at' => now()->subMinutes(10),
            'quiz_expires_at' => now()->addMinutes(40),
            'last_seen_at' => now(),
            'completed_at' => null,
        ]);

        $result = app(AssignmentVerificationService::class)
            ->resetSession($verification);

        $result->refresh();

        $newStartToken = $result->start_token_encrypted;

        $this->assertSame('pending', $result->status);
        $this->assertSame([], $result->answers);

        $this->assertNotEmpty($newStartToken);
        $this->assertNotSame($oldStartToken, $newStartToken);
        $this->assertSame(
            hash('sha256', $newStartToken),
            $result->start_token_hash
        );

        $this->assertNull($result->start_token_used_at);
        $this->assertNull($result->resume_token_hash);
        $this->assertNull($result->token_expires_at);
        $this->assertNull($result->token_revoked_at);
        $this->assertNull($result->started_at);
        $this->assertNull($result->quiz_expires_at);
        $this->assertNull($result->last_seen_at);
        $this->assertNull($result->completed_at);
    }

    public function test_reset_session_rejects_completed_verification(): void
    {
        $verification = AssignmentVerification::factory()
            ->completed()
            ->create();

        $this->expectException(ValidationException::class);

        app(AssignmentVerificationService::class)
            ->resetSession($verification);
    }

    public function test_remove_device_deletes_only_matching_assignment_and_student(): void
    {
        $assignment = Assignment::factory()->create();
        $otherAssignment = Assignment::factory()->create();

        $student = Student::factory()->create();
        $otherStudent = Student::factory()->create();

        $targetDevice = AssignmentQuizDevice::query()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'device_token_hash' => hash('sha256', 'target-device'),
            'first_used_at' => now(),
            'last_used_at' => now(),
        ]);

        $otherStudentDevice = AssignmentQuizDevice::query()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $otherStudent->id,
            'device_token_hash' => hash('sha256', 'other-student-device'),
            'first_used_at' => now(),
            'last_used_at' => now(),
        ]);

        $otherAssignmentDevice = AssignmentQuizDevice::query()->create([
            'assignment_id' => $otherAssignment->id,
            'student_id' => $student->id,
            'device_token_hash' => hash('sha256', 'other-assignment-device'),
            'first_used_at' => now(),
            'last_used_at' => now(),
        ]);

        $deleted = app(AssignmentVerificationService::class)
            ->removeDevice($assignment->id, $student->id);

        $this->assertSame(1, $deleted);

        $this->assertDatabaseMissing('assignment_quiz_devices', [
            'id' => $targetDevice->id,
        ]);

        $this->assertDatabaseHas('assignment_quiz_devices', [
            'id' => $otherStudentDevice->id,
        ]);

        $this->assertDatabaseHas('assignment_quiz_devices', [
            'id' => $otherAssignmentDevice->id,
        ]);
    }

    public function test_remove_device_returns_zero_when_no_device_exists(): void
    {
        $assignment = Assignment::factory()->create();
        $student = Student::factory()->create();

        $deleted = app(AssignmentVerificationService::class)
            ->removeDevice($assignment->id, $student->id);

        $this->assertSame(0, $deleted);
    }
}
