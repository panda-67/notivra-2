<?php

namespace Database\Factories;

use App\Models\AssignmentGroup;
use App\Models\AssignmentSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentSubmission>
 */
class AssignmentSubmissionFactory extends Factory
{
    public function definition(): array
    {
        $group = AssignmentGroup::factory()->create();

        return [
            'assignment_id' => $group->assignment_id,
            'assignment_group_id' => $group->id,
            'uploaded_by_member_id' => null,
            'file_path' => 'assignments/test/' . fake()->uuid() . '.docx',
            'original_filename' => 'assignment.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'file_size' => fake()->numberBetween(1000, 500000),
            'sha256' => hash('sha256', fake()->uuid()),
            'content_fingerprint' => null,
            'structure_fingerprint' => null,
            'metadata_fingerprint' => null,
            'duplicate_status' => 'pending',
            'metadata' => [],
            'duplicate_findings' => [],
            'submitted_at' => now(),
        ];
    }

    public function flagged(): static
    {
        return $this->state(fn() => [
            'duplicate_status' => 'flagged',
            'duplicate_findings' => [
                ['type' => 'content_similarity', 'status' => 'review_required'],
            ],
        ]);
    }
}
