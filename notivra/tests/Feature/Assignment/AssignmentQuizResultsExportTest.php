<?php

namespace Tests\Feature\Assignment;

use App\Domains\Admin\Exports\AssignmentQuizResultsExport;
use App\Domains\Admin\Exports\UnitQuizResultsSheet;
use PHPUnit\Framework\TestCase;

class AssignmentQuizResultsExportTest extends TestCase
{
    public function test_it_creates_three_unit_sheets(): void
    {
        $export = new AssignmentQuizResultsExport(
            collect()
        );

        $sheets = $export->sheets();

        $this->assertCount(3, $sheets);

        $this->assertSame(
            ['Unit 1', 'Unit 2', 'Unit 3'],
            array_map(
                fn(UnitQuizResultsSheet $sheet) => $sheet->title(),
                $sheets,
            ),
        );
    }

    public function test_each_sheet_contains_only_its_unit_rows(): void
    {
        $rows = collect([
            $this->makeRow(1, 'Kelompok 1 Unit 1'),
            $this->makeRow(2, 'Kelompok 1 Unit 2'),
            $this->makeRow(3, 'Kelompok 1 Unit 3'),
            $this->makeRow(1, 'Kelompok 2 Unit 1'),
        ]);

        foreach ([1, 2, 3] as $unit) {
            $sheet = new UnitQuizResultsSheet($rows, $unit);

            $filteredRows = $sheet->collection();

            $this->assertCount(
                $unit === 1 ? 2 : 1,
                $filteredRows,
            );

            foreach ($filteredRows as $row) {
                $this->assertSame($unit, $row['unit']);
            }
        }
    }

    public function test_each_sheet_has_expected_headings(): void
    {
        $sheet = new UnitQuizResultsSheet(collect(), 1);

        $headings = $sheet->headings();

        $this->assertCount(17, $headings);
        $this->assertSame('No.', $headings[0]);
        $this->assertSame('Nama Mahasiswa', $headings[5]);
        $this->assertSame('NIM', $headings[6]);
        $this->assertSame('Skor', $headings[13]);
        $this->assertSame('Waktu Selesai', $headings[16]);
    }

    private function makeRow(int $unit, string $groupName): array
    {
        return [
            'assignment_title' => 'Tugas Statistika Inferensial',
            'unit' => $unit,
            'unit_label' => "Unit {$unit}",
            'group_name' => $groupName,
            'reference_code' => 'REF-' . $unit,
            'student_name' => 'Mahasiswa ' . $unit,
            'student_number' => '202600' . $unit,
            'program_study' => 'Pendidikan Matematika',
            'verification_status' => 'completed',
            'total_questions' => 10,
            'correct' => 8,
            'incorrect' => 1,
            'unanswered' => 1,
            'score' => 80.0,
            'started_at' => null,
            'quiz_expires_at' => null,
            'completed_at' => null,
        ];
    }
}
