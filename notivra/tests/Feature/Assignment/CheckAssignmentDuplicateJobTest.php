<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Jobs\CheckAssignmentDuplicateJob;
use App\Domains\Assignments\Services\AssignmentDuplicateChecker;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentSubmission;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use RuntimeException;
use Tests\TestCase;

class CheckAssignmentDuplicateJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_uses_assignments_queue_and_expected_retry_settings(): void
    {
        $job = new CheckAssignmentDuplicateJob(123);

        $this->assertSame('assignments', $job->queue);
        $this->assertSame(3, $job->tries);
        $this->assertSame(60, $job->timeout);
        $this->assertSame([10, 30, 60], $job->backoff);
    }

    public function test_job_uses_submission_specific_overlap_middleware(): void
    {
        $submissionId = 42;
        $job = new CheckAssignmentDuplicateJob($submissionId);

        $middleware = $job->middleware();

        $this->assertCount(1, $middleware);
        $this->assertInstanceOf(WithoutOverlapping::class, $middleware[0]);
        $this->assertSame(
            "assignment-submission-{$submissionId}",
            $middleware[0]->key,
        );
        $this->assertSame(10, $middleware[0]->releaseAfter);
        $this->assertSame(120, $middleware[0]->expiresAfter);
    }

    public function test_job_calls_duplicate_checker_for_submission(): void
    {
        $submission = $this->createSubmission();
        $job = new CheckAssignmentDuplicateJob($submission->id);

        $checker = $this->mock(AssignmentDuplicateChecker::class);

        $checker->shouldReceive('check')
            ->once()
            ->withArgs(
                fn(AssignmentSubmission $actual): bool =>
                $actual->is($submission),
            )
            ->andReturn($submission);

        $job->handle($checker);
    }

    public function test_job_throws_when_submission_does_not_exist(): void
    {
        $job = new CheckAssignmentDuplicateJob(999999);
        $checker = $this->mock(AssignmentDuplicateChecker::class);

        $checker->shouldNotReceive('check');

        $this->expectException(ModelNotFoundException::class);

        $job->handle($checker);
    }

    public function test_failed_job_moves_pending_submission_to_under_review(): void
    {
        $submission = $this->createSubmission([
            'duplicate_status' => 'pending',
        ]);

        $job = new CheckAssignmentDuplicateJob($submission->id);

        $job->failed(new RuntimeException('Simulated queue failure'));

        $submission->refresh();

        $this->assertSame('under_review', $submission->duplicate_status);
        $this->assertIsArray($submission->duplicate_findings);
        $this->assertSame(
            'processing_error',
            $submission->duplicate_findings[0]['type'],
        );
        $this->assertSame(
            'manual_review_required',
            $submission->duplicate_findings[0]['result'],
        );
    }

    public function test_failed_job_does_not_overwrite_non_pending_status(): void
    {
        $originalFindings = [
            [
                'type' => 'exact_sha256',
                'result' => 'no_match',
            ],
        ];

        $submission = $this->createSubmission([
            'duplicate_status' => 'clear',
            'duplicate_findings' => $originalFindings,
        ]);

        $job = new CheckAssignmentDuplicateJob($submission->id);

        $job->failed(new RuntimeException('Simulated queue failure'));

        $submission->refresh();

        $this->assertSame('clear', $submission->duplicate_status);
        $this->assertSame($originalFindings, $submission->duplicate_findings);
    }

    public function test_failed_job_does_nothing_if_submission_was_deleted(): void
    {
        $job = new CheckAssignmentDuplicateJob(999999);

        // Tidak boleh melempar exception jika record sudah tidak ada.
        $job->failed(new RuntimeException('Simulated queue failure'));

        $this->assertDatabaseMissing('assignment_submissions', [
            'id' => 999999,
        ]);
    }

    private function createSubmission(array $overrides = []): AssignmentSubmission
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'submitted',
        ]);

        return AssignmentSubmission::query()->create(array_merge([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'original_filename' => 'report.docx',
            'file_path' => 'assignment-submissions/test/report.docx',
            'file_size' => 1024,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'sha256' => hash('sha256', 'test document'),
            'duplicate_status' => 'pending',
            'metadata' => [],
            'duplicate_findings' => [],
            'submitted_at' => now(),
        ], $overrides));
    }
}
