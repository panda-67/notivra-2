<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\AssignmentQuizResultService;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentQuizQuestion;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentQuizResultServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createResultRow(
        array $options = [],
    ): array {
        $assignment = $options['assignment']
            ?? Assignment::factory()->create();

        $group = AssignmentGroup::factory()->create([
            'assignment_id' => $assignment->id,
            'group_name' => $options['group_name']
                ?? 'Kelompok 1 Unit 1',
            'status' => 'verification_in_progress',
        ]);

        $student = Student::factory()->create([
            'name' => $options['student_name'] ?? 'Mahasiswa Uji',
            'student_number' => $options['student_number']
                ?? ('TEST' . fake()->unique()->numerify('########')),
            'program_study' => $options['program_study'] ?? 'Pendidikan Matematika',
        ]);

        $member = AssignmentGroupMember::query()->create([
            'assignment_id' => $assignment->id,
            'assignment_group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        $verification = AssignmentVerification::factory()->create([
            'assignment_group_member_id' => $member->id,
            'status' => $options['status'] ?? 'completed',
            'answers' => $options['answers'] ?? [],
            'started_at' => $options['started_at'] ?? now()->subMinutes(30),
            'quiz_expires_at' => $options['quiz_expires_at'] ?? now()->addMinutes(20),
            'completed_at' => $options['completed_at'] ?? now(),
        ]);

        $questionCount = $options['question_count'] ?? 3;

        for ($position = 1; $position <= $questionCount; $position++) {
            AssignmentQuizQuestion::query()->create([
                'assignment_id' => $assignment->id,
                'position' => $position,
                'question' => "Pertanyaan uji {$position}",
                'option_a' => 'Pilihan A',
                'option_b' => 'Pilihan B',
                'option_c' => 'Pilihan C',
                'option_d' => 'Pilihan D',
                'correct_option' => 'B',
            ]);
        }

        return [
            'assignment' => $assignment,
            'group' => $group,
            'student' => $student,
            'member' => $member,
            'verification' => $verification,
        ];
    }

    public function test_it_calculates_correct_incorrect_unanswered_and_final_score(): void
    {
        $this->createResultRow([
            'status' => 'completed',
            'answers' => [
                '1' => 'b',
                '2' => 'A',
            ],
        ]);

        $rows = app(AssignmentQuizResultService::class)->rows();

        $this->assertCount(1, $rows);

        $row = $rows->first();

        $this->assertSame(3, $row['total_questions']);
        $this->assertSame(1, $row['correct']);
        $this->assertSame(1, $row['incorrect']);
        $this->assertSame(1, $row['unanswered']);
        $this->assertSame(33.33, $row['score']);
        $this->assertTrue($row['score_is_final']);
        $this->assertSame('completed', $row['verification_status']);
        $this->assertSame(1, $row['unit']);
        $this->assertSame('Unit 1', $row['unit_label']);
    }

    public function test_it_trims_and_ignores_answer_letter_case(): void
    {
        $this->createResultRow([
            'status' => 'completed',
            'answers' => [
                '1' => ' b ',
                '2' => 'B',
                '3' => 'b',
            ],
        ]);

        $row = app(AssignmentQuizResultService::class)
            ->rows()
            ->first();

        $this->assertSame(3, $row['correct']);
        $this->assertSame(0, $row['incorrect']);
        $this->assertSame(0, $row['unanswered']);
        $this->assertSame(100.0, $row['score']);
    }

    public function test_it_does_not_show_final_score_for_pending_or_in_progress_sessions(): void
    {
        foreach (['pending', 'in_progress'] as $status) {
            $this->createResultRow([
                'status' => $status,
                'answers' => ['1' => 'B'],
            ]);
        }

        $rows = app(AssignmentQuizResultService::class)->rows();

        $this->assertCount(2, $rows);

        foreach ($rows as $row) {
            $this->assertNull($row['score']);
            $this->assertFalse($row['score_is_final']);
        }
    }

    public function test_it_does_not_calculate_score_when_assignment_has_no_questions(): void
    {
        $this->createResultRow([
            'status' => 'completed',
            'question_count' => 0,
        ]);

        $row = app(AssignmentQuizResultService::class)
            ->rows()
            ->first();

        $this->assertSame(0, $row['total_questions']);
        $this->assertNull($row['score']);
        $this->assertFalse($row['score_is_final']);
    }

    public function test_it_filters_rows_by_assignment_id(): void
    {
        $first = Assignment::factory()->create();
        $second = Assignment::factory()->create();

        $this->createResultRow(['assignment' => $first]);
        $this->createResultRow(['assignment' => $second]);

        $rows = app(AssignmentQuizResultService::class)->rows([
            'assignment_id' => $first->id,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame($first->id, $rows->first()['assignment_id']);
    }

    public function test_it_filters_rows_by_unit(): void
    {
        $this->createResultRow([
            'group_name' => 'Kelompok 1 Unit 1',
        ]);

        $this->createResultRow([
            'group_name' => 'Kelompok 2 Unit 2',
        ]);

        $rows = app(AssignmentQuizResultService::class)->rows([
            'unit' => 2,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame(2, $rows->first()['unit']);
        $this->assertSame('Kelompok 2 Unit 2', $rows->first()['group_name']);
    }

    public function test_it_filters_rows_by_verification_status(): void
    {
        $this->createResultRow(['status' => 'completed']);
        $this->createResultRow(['status' => 'expired']);
        $this->createResultRow(['status' => 'pending']);

        $rows = app(AssignmentQuizResultService::class)->rows([
            'status' => 'expired',
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('expired', $rows->first()['verification_status']);
    }

    public function test_it_searches_student_name_student_number_and_group_name(): void
    {
        $this->createResultRow([
            'student_name' => 'Andi Saputra',
            'student_number' => '2308100001',
            'group_name' => 'Kelompok 1 Unit 1',
        ]);

        $this->createResultRow([
            'student_name' => 'Budi Santoso',
            'student_number' => '2308100002',
            'group_name' => 'Kelompok 2 Unit 2',
        ]);

        $service = app(AssignmentQuizResultService::class);

        $byName = $service->rows(['search' => 'ANDI']);
        $byNumber = $service->rows(['search' => '2308100002']);
        $byGroup = $service->rows(['search' => 'kelompok 1 unit 1']);

        $this->assertCount(1, $byName);
        $this->assertSame('Andi Saputra', $byName->first()['student_name']);

        $this->assertCount(1, $byNumber);
        $this->assertSame('2308100002', $byNumber->first()['student_number']);

        $this->assertCount(1, $byGroup);
        $this->assertSame('Kelompok 1 Unit 1', $byGroup->first()['group_name']);
    }

    public function test_it_returns_unmapped_group_names_and_can_filter_by_assignment(): void
    {
        $first = Assignment::factory()->create();
        $second = Assignment::factory()->create();

        AssignmentGroup::factory()->create([
            'assignment_id' => $first->id,
            'group_name' => 'Kelompok Khusus',
        ]);

        AssignmentGroup::factory()->create([
            'assignment_id' => $first->id,
            'group_name' => 'Kelompok 1 Unit 1',
        ]);

        AssignmentGroup::factory()->create([
            'assignment_id' => $second->id,
            'group_name' => 'Kelompok Tanpa Unit',
        ]);

        $service = app(AssignmentQuizResultService::class);

        $allUnmapped = $service->unmappedGroupNames();

        $this->assertEqualsCanonicalizing(
            ['Kelompok Khusus', 'Kelompok Tanpa Unit'],
            $allUnmapped->all(),
        );

        $firstUnmapped = $service->unmappedGroupNames($first->id);

        $this->assertSame(
            ['Kelompok Khusus'],
            $firstUnmapped->all(),
        );
    }
}
