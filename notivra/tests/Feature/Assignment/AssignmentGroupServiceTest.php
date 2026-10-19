<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentGroupService;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentVerification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AssignmentGroupServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createMember(
        Assignment $assignment,
        AssignmentGroup $group,
        string $verificationStatus,
    ): AssignmentGroupMember {
        $student = Student::factory()->create();

        $member = AssignmentGroupMember::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        AssignmentVerification::factory()->create([
            'assignment_group_member_id' => $member->id,
            'status' => $verificationStatus,
        ]);

        return $member;
    }

    public function test_group_becomes_ready_when_all_members_are_verified(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        $this->createMember($assignment, $group, 'completed');
        $this->createMember($assignment, $group, 'completed');

        $result = app(AssignmentGroupService::class)
            ->refreshUploadReadiness($group);

        $this->assertSame('ready_for_upload', $result->status);
    }

    public function test_group_is_not_ready_if_any_member_is_unverified(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'draft',
        ]);

        $this->createMember($assignment, $group, 'completed');
        $this->createMember($assignment, $group, 'pending');

        $result = app(AssignmentGroupService::class)
            ->refreshUploadReadiness($group);

        $this->assertSame('verification_in_progress', $result->status);
    }

    public function test_empty_group_cannot_be_ready_for_upload(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'ready_for_upload',
        ]);

        $result = app(AssignmentGroupService::class)
            ->refreshUploadReadiness($group);

        $this->assertSame('draft', $result->status);
    }

    public function test_submitted_group_status_is_not_overwritten(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'submitted',
        ]);

        $this->createMember($assignment, $group, 'completed');

        $result = app(AssignmentGroupService::class)
            ->refreshUploadReadiness($group);

        $this->assertSame('submitted', $result->status);
    }

    public function test_bypass_upload_sets_group_ready_and_records_admin(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        $admin = User::factory()->create();

        $result = app(AssignmentGroupService::class)
            ->bypassUpload($group, $admin);

        $result->refresh();

        $this->assertSame('ready_for_upload', $result->status);
        $this->assertNotNull($result->upload_bypassed_at);
        $this->assertSame($admin->id, $result->upload_bypassed_by);
    }

    public function test_bypass_upload_rejects_invalid_group_status(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'submitted',
        ]);

        $admin = User::factory()->create();

        try {
            app(AssignmentGroupService::class)
                ->bypassUpload($group, $admin);

            $this->fail('Bypass harus ditolak untuk grup yang sudah submitted.');
        } catch (ValidationException) {
            // Expected.
        }

        $group->refresh();

        $this->assertSame('submitted', $group->status);
        $this->assertNull($group->upload_bypassed_at);
        $this->assertNull($group->upload_bypassed_by);
    }

    public function test_bypassed_group_remains_ready_after_readiness_refresh(): void
    {
        $assignment = Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        $admin = User::factory()->create();

        $service = app(AssignmentGroupService::class);

        $service->bypassUpload($group, $admin);

        // Grup memiliki anggota yang belum menyelesaikan verifikasi.
        $this->createMember($assignment, $group, 'pending');

        $result = $service->refreshUploadReadiness($group);

        $this->assertSame('ready_for_upload', $result->status);
        $this->assertNotNull($result->upload_bypassed_at);
    }
}
