<?php

namespace App\Domains\Admin\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AssignmentIndividualQuizResultsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        private readonly Collection $rows,
    ) {}

    public function collection(): Collection
    {
        return $this->rows->values()->map(
            function (array $row, int $index) {
                $row['_number'] = $index + 1;

                return $row;
            }
        );
    }

    public function title(): string
    {
        return 'Kuis Individual';
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tugas',
            'Nama Mahasiswa',
            'NIM',
            'Status Peserta',
            'Status Kuis',
            'Jumlah Soal',
            'Benar',
            'Salah',
            'Belum Dijawab',
            'Skor (%)',
            'Waktu Mulai',
            'Batas Waktu',
            'Waktu Selesai',
            'Waktu Kedaluwarsa',
        ];
    }

    public function map($row): array
    {
        return [
            $row['_number'] ?? '',
            $row['assignment_title'],
            $row['student_name'],
            $row['student_number'],
            $row['participant_status'],
            $row['quiz_status'],
            $row['total_questions'],
            $row['correct'],
            $row['incorrect'],
            $row['unanswered'],
            $row['score'] === null ? 'Belum final' : $row['score'],
            $row['started_at']?->format('Y-m-d H:i:s'),
            $row['expires_at']?->format('Y-m-d H:i:s'),
            $row['completed_at']?->format('Y-m-d H:i:s'),
            $row['expired_at']?->format('Y-m-d H:i:s'),
        ];
    }
}
