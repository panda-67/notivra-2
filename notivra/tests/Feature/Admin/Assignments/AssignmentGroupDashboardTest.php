<?php

namespace Tests\Feature\Admin\Assignments;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentQuizDevice;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentVerification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AssignmentGroupDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'inertia.testing.ensure_pages_exist' => false,
        ]);
    }

    private function createAdmin(
        UserRole $role = UserRole::ADMIN_STAFF,
    ): User {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    private function createGroupWithMember(
        ?Assignment $assignment = null,
        string $verificationStatus = 'pending',
    ): array {
        $assignment ??= Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        $student = Student::factory()->create();

        $member = AssignmentGroupMember::factory()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        $verification = AssignmentVerification::factory()->create([
            'assignment_group_member_id' => $member->id,
            'status' => $verificationStatus,
        ]);

        return compact(
            'assignment',
            'group',
            'student',
            'member',
            'verification',
        );
    }

    public function test_admin_can_view_assignment_group_list(): void
    {
        $admin = $this->createAdmin();

        $assignment = Assignment::factory()->create();

        $data = $this->createGroupWithMember($assignment);

        $response = $this->actingAs($admin)->get(
            route('admin.assignments.groups.index', [
                'slug' => $assignment->slug,
            ]),
        );

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Admin/Assignments/Groups/Index')
                    ->where('assignment.slug', $assignment->slug)
                    ->has('groups.data', 1)
                    ->where(
                        'groups.data.0.reference_code',
                        $data['group']->reference_code,
                    )
                    ->where('groups.data.0.members_count', 1)
                    ->where('groups.data.0.verification_summary.incomplete', 1)
            );
    }

    public function test_admin_can_view_group_detail_and_member_status(): void
    {
        $admin = $this->createAdmin();

        $data = $this->createGroupWithMember();

        $response = $this->actingAs($admin)->get(
            route('admin.assignments.groups.show', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
            ]),
        );

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Admin/Assignments/Groups/Show')
                    ->where('assignment.slug', $data['assignment']->slug)
                    ->where(
                        'group.reference_code',
                        $data['group']->reference_code,
                    )
                    ->has('group.members', 1)
                    ->where(
                        'group.members.0.verification_status',
                        'pending',
                    )
                    ->where('group.members.0.has_verification', true)
                    ->where('group.members.0.has_registered_device', false)
            );
    }

    public function test_admin_can_bypass_upload_for_group(): void
    {
        $admin = $this->createAdmin();

        $data = $this->createGroupWithMember();

        $response = $this->actingAs($admin)->post(
            route('admin.assignments.groups.bypass-upload', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
            ]),
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('assignment_groups', [
            'id' => $data['group']->id,
            'status' => 'ready_for_upload',
            'upload_bypassed_by' => $admin->id,
        ]);

        $this->assertNotNull(
            $data['group']->fresh()->upload_bypassed_at,
        );
    }

    public function test_admin_cannot_bypass_group_that_already_has_submission(): void
    {
        $admin = $this->createAdmin();

        $data = $this->createGroupWithMember();

        AssignmentSubmission::factory()->create([
            'assignment_id' => $data['assignment']->id,
            'assignment_group_id' => $data['group']->id,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.assignments.groups.bypass-upload', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
            ]),
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('assignment_groups', [
            'id' => $data['group']->id,
            'status' => 'verification_in_progress',
            'upload_bypassed_by' => null,
        ]);

        $this->assertNull(
            $data['group']->fresh()->upload_bypassed_at,
        );

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $data['assignment']->id,
            'assignment_group_id' => $data['group']->id,
        ]);
    }

    public function test_admin_can_reset_member_quiz_session(): void
    {
        $admin = $this->createAdmin();

        $data = $this->createGroupWithMember(
            verificationStatus: 'in_progress',
        );

        $data['verification']->forceFill([
            'answers' => ['1' => 'A'],
            'start_token_hash' => hash('sha256', 'old-start-token'),
            'start_token_encrypted' => 'old-start-token',
            'start_token_used_at' => now(),
            'resume_token_hash' => hash('sha256', 'old-resume-token'),
            'token_expires_at' => now()->addHour(),
            'started_at' => now()->subMinutes(5),
            'quiz_expires_at' => now()->addMinutes(45),
            'last_seen_at' => now(),
        ])->save();

        $response = $this->actingAs($admin)->post(
            route('admin.assignments.groups.members.reset-session', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
                'member' => $data['member']->id,
            ]),
        );

        $response->assertRedirect();

        $verification = $data['verification']->fresh();

        $this->assertSame('pending', $verification->status);
        $this->assertSame([], $verification->answers);
        $this->assertNull($verification->resume_token_hash);
        $this->assertNull($verification->start_token_used_at);
        $this->assertNotNull($verification->start_token_hash);
        $this->assertNotSame(
            hash('sha256', 'old-start-token'),
            $verification->start_token_hash,
        );
    }

    public function test_admin_can_remove_member_quiz_device(): void
    {
        $admin = $this->createAdmin();

        $data = $this->createGroupWithMember();

        $device = AssignmentQuizDevice::query()->create([
            'assignment_id' => $data['assignment']->id,
            'student_id' => $data['student']->id,
            'device_token_hash' => hash('sha256', 'quiz-device-token'),
            'first_used_at' => now(),
            'last_used_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.assignments.groups.members.remove-device', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
                'member' => $data['member']->id,
            ]),
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('assignment_quiz_devices', [
            'id' => $device->id,
        ]);
    }

    public function test_guest_cannot_access_assignment_group_dashboard(): void
    {
        $assignment = Assignment::factory()->create();

        $response = $this->get(
            route('admin.assignments.groups.index', [
                'slug' => $assignment->slug,
            ]),
        );

        $response->assertRedirect(route('login'));
    }

    public function test_researcher_cannot_access_assignment_group_dashboard(): void
    {
        $researcher = $this->createAdmin(UserRole::RESEARCHER);

        $assignment = Assignment::factory()->create();

        $response = $this->actingAs($researcher)->get(
            route('admin.assignments.groups.index', [
                'slug' => $assignment->slug,
            ]),
        );

        $response->assertForbidden();
    }

    public function test_group_from_another_assignment_cannot_be_opened(): void
    {
        $admin = $this->createAdmin();

        $assignmentA = Assignment::factory()->create();
        $assignmentB = Assignment::factory()->create();

        $groupB = AssignmentGroup::factory()->create([
            'assignment_id' => $assignmentB->id,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.assignments.groups.show', [
                'slug' => $assignmentA->slug,
                'reference_code' => $groupB->reference_code,
            ]),
        );

        $response->assertNotFound();
    }

    public function test_member_from_another_group_cannot_be_reset(): void
    {
        $admin = $this->createAdmin();

        $dataA = $this->createGroupWithMember();
        $dataB = $this->createGroupWithMember($dataA['assignment']);

        $response = $this->actingAs($admin)->post(
            route('admin.assignments.groups.members.reset-session', [
                'slug' => $dataA['assignment']->slug,
                'reference_code' => $dataA['group']->reference_code,
                'member' => $dataB['member']->id,
            ]),
        );

        $response->assertNotFound();

        $this->assertSame(
            'pending',
            $dataB['verification']->fresh()->status,
        );
    }


    public function test_admin_cannot_reset_completed_quiz_session(): void
    {
        $admin = $this->createAdmin();

        $data = $this->createGroupWithMember(
            verificationStatus: 'completed',
        );

        $data['verification']->forceFill([
            'answers' => ['1' => 'A', '2' => 'B'],
            'start_token_hash' => hash('sha256', 'completed-token'),
            'started_at' => now()->subMinutes(30),
        ])->save();

        $response = $this->actingAs($admin)->post(
            route('admin.assignments.groups.members.reset-session', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
                'member' => $data['member']->id,
            ]),
        );

        $response->assertRedirect();

        $verification = $data['verification']->fresh();

        $this->assertSame('completed', $verification->status);
        $this->assertSame(
            ['1' => 'A', '2' => 'B'],
            $verification->answers,
        );
        $this->assertSame(
            hash('sha256', 'completed-token'),
            $verification->start_token_hash,
        );
    }

    public function test_admin_can_reset_pending_quiz_session(): void
    {
        $admin = $this->createAdmin();

        $data = $this->createGroupWithMember(
            verificationStatus: 'pending',
        );

        $data['verification']->forceFill([
            'answers' => ['1' => 'A'],
            'start_token_hash' => hash('sha256', 'old-pending-token'),
        ])->save();

        $response = $this->actingAs($admin)->post(
            route('admin.assignments.groups.members.reset-session', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
                'member' => $data['member']->id,
            ]),
        );

        $response->assertRedirect();

        $verification = $data['verification']->fresh();

        $this->assertSame('pending', $verification->status);
        $this->assertNotSame(
            hash('sha256', 'old-pending-token'),
            $verification->start_token_hash,
        );
    }

    public function test_admin_can_reset_expired_quiz_session(): void
    {
        $admin = $this->createAdmin();

        $data = $this->createGroupWithMember(
            verificationStatus: 'expired',
        );

        $data['verification']->forceFill([
            'answers' => ['1' => 'A'],
            'start_token_hash' => hash('sha256', 'old-expired-token'),
        ])->save();

        $response = $this->actingAs($admin)->post(
            route('admin.assignments.groups.members.reset-session', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
                'member' => $data['member']->id,
            ]),
        );

        $response->assertRedirect();

        $verification = $data['verification']->fresh();

        $this->assertSame('pending', $verification->status);
        $this->assertNotSame(
            hash('sha256', 'old-expired-token'),
            $verification->start_token_hash,
        );
    }

    public function test_admin_can_remove_device_when_member_has_no_device(): void
    {
        $admin = $this->createAdmin();

        $data = $this->createGroupWithMember();

        $response = $this->actingAs($admin)->delete(
            route('admin.assignments.groups.members.remove-device', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
                'member' => $data['member']->id,
            ]),
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('assignment_quiz_devices', [
            'assignment_id' => $data['assignment']->id,
            'student_id' => $data['student']->id,
        ]);
    }

    public function test_super_admin_can_access_assignment_group_dashboard(): void
    {
        $admin = $this->createAdmin(UserRole::SUPER_ADMIN);

        $data = $this->createGroupWithMember();

        $indexResponse = $this->actingAs($admin)->get(
            route('admin.assignments.groups.index', [
                'slug' => $data['assignment']->slug,
            ]),
        );

        $indexResponse->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Admin/Assignments/Groups/Index')
                    ->where('assignment.slug', $data['assignment']->slug),
            );

        $showResponse = $this->actingAs($admin)->get(
            route('admin.assignments.groups.show', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
            ]),
        );

        $showResponse->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Admin/Assignments/Groups/Show')
                    ->where(
                        'group.reference_code',
                        $data['group']->reference_code,
                    ),
            );
    }

    public function test_researcher_cannot_perform_assignment_group_mutations(): void
    {
        $researcher = $this->createAdmin(UserRole::RESEARCHER);

        $data = $this->createGroupWithMember();

        $device = AssignmentQuizDevice::create([
            'assignment_id' => $data['assignment']->id,
            'student_id' => $data['student']->id,
            'device_token_hash' => hash('sha256', 'researcher-test-device'),
            'first_used_at' => now(),
            'last_used_at' => now(),
        ]);

        $bypassResponse = $this->actingAs($researcher)->post(
            route('admin.assignments.groups.bypass-upload', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
            ]),
        );

        $bypassResponse->assertForbidden();

        $resetResponse = $this->actingAs($researcher)->post(
            route('admin.assignments.groups.members.reset-session', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
                'member' => $data['member']->id,
            ]),
        );

        $resetResponse->assertForbidden();

        $removeDeviceResponse = $this->actingAs($researcher)->delete(
            route('admin.assignments.groups.members.remove-device', [
                'slug' => $data['assignment']->slug,
                'reference_code' => $data['group']->reference_code,
                'member' => $data['member']->id,
            ]),
        );

        $removeDeviceResponse->assertForbidden();

        $this->assertDatabaseHas('assignment_groups', [
            'id' => $data['group']->id,
            'status' => 'verification_in_progress',
            'upload_bypassed_by' => null,
        ]);

        $this->assertSame(
            'pending',
            $data['verification']->fresh()->status,
        );

        $this->assertDatabaseHas('assignment_quiz_devices', [
            'id' => $device->id,
        ]);
    }
}
