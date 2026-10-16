<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentGroupRegistrationService;
use App\Models\Assignment;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AssignmentGroupRegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_group_members_and_verification_sessions(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'opens_at' => now()->subHour(),
            'due_at' => now()->addDays(7),
        ]);

        $result = app(AssignmentGroupRegistrationService::class)->register(
            $assignment,
            'Kelompok A',
            [
                [
                    'student_number' => '20260001',
                    'name' => 'Mahasiswa Satu',
                    'program_study' => 'Biologi',
                    'institution' => 'Universitas Contoh',
                ],
                [
                    'student_number' => '20260002',
                    'name' => 'Mahasiswa Dua',
                    'program_study' => 'Biologi',
                    'institution' => 'Universitas Contoh',
                ],
            ],
        );

        $this->assertDatabaseHas('assignment_groups', [
            'id' => $result['group']->id,
            'assignment_id' => $assignment->id,
            'status' => 'verification_in_progress',
        ]);

        $this->assertSame(2, AssignmentGroupMember::query()
            ->where('assignment_group_id', $result['group']->id)
            ->count());

        $this->assertSame(2, AssignmentVerification::query()
            ->whereHas(
                'member',
                fn($query) =>
                $query->where('assignment_group_id', $result['group']->id)
            )
            ->count());

        $this->assertCount(2, $result['invitations']);

        foreach ($result['invitations'] as $invitation) {
            $verification = AssignmentVerification::query()
                ->whereHas(
                    'member.student',
                    fn($query) =>
                    $query->where('student_number', $invitation['student_number'])
                )
                ->firstOrFail();

            $this->assertNotEmpty($verification->start_token_hash);
            $this->assertNotEmpty($verification->start_token_encrypted);
            $this->assertNull($verification->start_token_used_at);

            $this->assertSame('pending', $verification->status);
            $this->assertNull($verification->resume_token_hash);
        }
    }

    public function test_registration_rejects_unpublished_assignment(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'draft',
        ]);

        $this->expectException(ValidationException::class);

        app(AssignmentGroupRegistrationService::class)->register(
            $assignment,
            null,
            [
                ['student_number' => '20260001', 'name' => 'Mahasiswa Satu'],
            ],
        );
    }

    public function test_registration_rejects_duplicate_student_numbers_in_same_group(): void
    {
        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'opens_at' => now()->subHour(),
            'due_at' => now()->addDays(7),
        ]);

        $this->expectException(ValidationException::class);

        app(AssignmentGroupRegistrationService::class)->register(
            $assignment,
            null,
            [
                ['student_number' => '20260001', 'name' => 'Mahasiswa Satu'],
                ['student_number' => '20260001', 'name' => 'Mahasiswa Satu'],
            ],
        );
    }

    public function test_registration_rejects_inactive_student(): void
    {
        Student::factory()->create([
            'student_number' => '20260001',
            'name' => 'Mahasiswa Nonaktif',
            'status' => 'inactive',
        ]);

        $assignment = Assignment::factory()->create([
            'status' => 'published',
            'opens_at' => now()->subHour(),
            'due_at' => now()->addDays(7),
        ]);

        $this->expectException(ValidationException::class);

        app(AssignmentGroupRegistrationService::class)->register(
            $assignment,
            null,
            [
                ['student_number' => '20260001', 'name' => 'Mahasiswa Nonaktif'],
            ],
        );
    }
}
