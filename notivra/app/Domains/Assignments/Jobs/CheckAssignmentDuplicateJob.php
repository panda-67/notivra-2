<?php

namespace App\Domains\Assignments\Jobs;

use App\Domains\Assignments\Services\AssignmentDuplicateChecker;
use App\Models\AssignmentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CheckAssignmentDuplicateJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public readonly int $submissionId,
    ) {
        $this->onQueue('assignments');
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("assignment-submission-{$this->submissionId}"))
                ->releaseAfter(10)
                ->expireAfter(120),
        ];
    }

    public function handle(
        AssignmentDuplicateChecker $checker,
    ): void {
        $submission = AssignmentSubmission::query()
            ->findOrFail($this->submissionId);

        $checker->check($submission);
    }

    public function failed(?Throwable $exception): void
    {
        $submission = AssignmentSubmission::query()
            ->find($this->submissionId);

        if (! $submission || $submission->duplicate_status !== 'pending') {
            return;
        }

        $submission->forceFill([
            'duplicate_status' => 'under_review',
            'duplicate_findings' => [
                [
                    'type' => 'processing_error',
                    'result' => 'manual_review_required',
                    'message' => 'Pemeriksaan otomatis gagal setelah percobaan ulang.',
                    'failed_at' => now()->toIso8601String(),
                ],
            ],
        ])->save();
    }
}
