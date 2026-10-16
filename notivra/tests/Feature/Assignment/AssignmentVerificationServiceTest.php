<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentVerificationService;
use App\Models\AssignmentVerification;
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
}
