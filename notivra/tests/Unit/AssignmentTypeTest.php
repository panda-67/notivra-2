<?php

namespace Tests\Unit;

use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentIndividualQuizSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_with_individual_quiz_settings_is_individual_quiz(): void
    {
        $assignment = Assignment::factory()->create();

        AssignmentIndividualQuizSetting::create([
            'assignment_id' => $assignment->id,
            'duration_minutes' => 50,
        ]);

        $this->assertSame('individual_quiz', $assignment->fresh()->type);
    }

    public function test_assignment_with_groups_is_group_upload(): void
    {
        $assignment = Assignment::factory()->create();

        AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
        ]);

        $this->assertSame('group_upload', $assignment->fresh()->type);
    }

    public function test_assignment_without_workflow_configuration_is_unconfigured(): void
    {
        $assignment = Assignment::factory()->create();

        $this->assertSame('unconfigured', $assignment->type);
    }
}
