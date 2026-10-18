<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentDuplicateChecker;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentDuplicateCheckerTest extends TestCase
{
    use RefreshDatabase;

    private AssignmentDuplicateChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checker = app(AssignmentDuplicateChecker::class);
    }

    public function test_submission_without_matching_hash_is_marked_clear(): void
    {
        $assignment = Assignment::factory()->create();

        $submission = $this->createSubmission(
            $assignment,
            hash('sha256', 'document A'),
        );

        $result = $this->checker->check($submission);

        $this->assertSame('clear', $result->duplicate_status);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'duplicate_status' => 'clear',
        ]);

        $this->assertContains(
            'no_match',
            array_column($result->duplicate_findings, 'result'),
        );
    }

    public function test_identical_hashes_in_same_assignment_flag_both_submissions(): void
    {
        $assignment = Assignment::factory()->create();

        $hash = hash('sha256', 'identical document');

        $first = $this->createSubmission($assignment, $hash);
        $second = $this->createSubmission($assignment, $hash);

        $this->checker->check($second);

        $this->assertSame(
            'flagged',
            $second->fresh()->duplicate_status,
        );

        $this->assertSame(
            'flagged',
            $first->fresh()->duplicate_status,
        );

        $firstFindings = $first->fresh()->duplicate_findings;
        $secondFindings = $second->fresh()->duplicate_findings;

        $this->assertNotEmpty($firstFindings);
        $this->assertNotEmpty($secondFindings);

        $this->assertSame(
            $second->id,
            $firstFindings[0]['matched_submission_id'],
        );

        $this->assertSame(
            $first->id,
            $secondFindings[0]['matched_submission_id'],
        );
    }

    public function test_identical_hash_in_another_assignment_is_not_flagged(): void
    {
        $firstAssignment = Assignment::factory()->create();
        $secondAssignment = Assignment::factory()->create();

        $hash = hash('sha256', 'same bytes');

        $first = $this->createSubmission($firstAssignment, $hash);
        $second = $this->createSubmission($secondAssignment, $hash);

        $this->checker->check($second);

        $this->assertSame(
            'clear',
            $second->fresh()->duplicate_status,
        );

        $this->assertSame(
            'pending',
            $first->fresh()->duplicate_status,
        );
    }

    public function test_rechecking_submission_does_not_duplicate_findings(): void
    {
        $assignment = Assignment::factory()->create();

        $hash = hash('sha256', 'repeated document');

        $first = $this->createSubmission($assignment, $hash);
        $second = $this->createSubmission($assignment, $hash);

        $this->checker->check($second);

        $firstFindingsBefore = $first->fresh()->duplicate_findings;
        $secondFindingsBefore = $second->fresh()->duplicate_findings;

        $this->checker->check($second->fresh());

        $firstFindingsAfter = $first->fresh()->duplicate_findings;
        $secondFindingsAfter = $second->fresh()->duplicate_findings;

        $this->assertSame(
            $firstFindingsBefore,
            $firstFindingsAfter,
            'Pemeriksaan ulang tidak boleh menambahkan temuan berulang pada submission pertama.',
        );

        $this->assertSame(
            $secondFindingsBefore,
            $secondFindingsAfter,
            'Pemeriksaan ulang tidak boleh mengubah daftar temuan secara tidak perlu.',
        );
    }

    private function createSubmission(
        Assignment $assignment,
        string $sha256,
    ): AssignmentSubmission {
        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'submitted',
        ]);
        return AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'original_filename' => 'report.docx',
            'file_path' => 'assignment-submissions/test/report.docx',
            'file_size' => 1024,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'sha256' => $sha256,
            'duplicate_status' => 'pending',
            'metadata' => [],
            'duplicate_findings' => [],
            'submitted_at' => now(),
        ]);
    }
}
