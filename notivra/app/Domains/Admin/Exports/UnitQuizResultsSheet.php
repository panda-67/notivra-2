<?php

namespace App\Domains\Admin\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class UnitQuizResultsSheet implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        private readonly Collection $rows,
        private readonly int $unit,
    ) {}

    public function collection(): Collection
    {
        return $this->rows
            ->where('unit', $this->unit)
            ->values()
            ->map(function (array $row, int $index) {
                $row['_number'] = $index + 1;

                return $row;
            });
    }

    public function title(): string
    {
        return "Unit {$this->unit}";
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tugas',
            'Unit',
            'Kelompok',
            'Kode Referensi',
            'Nama Mahasiswa',
            'NIM',
            'Program Studi',
            'Status Kuis',
            'Jumlah Soal',
            'Benar',
            'Salah',
            'Belum Dijawab',
            'Skor',
            'Waktu Mulai',
            'Batas Waktu',
            'Waktu Selesai',
        ];
    }

    public function map($row): array
    {
        return [
            $row['_number'] ?? '',
            $row['assignment_title'],
            $row['unit_label'],
            $row['group_name'],
            $row['reference_code'],
            $row['student_name'],
            $row['student_number'],
            $row['program_study'],
            $row['verification_status'],
            $row['total_questions'],
            $row['correct'],
            $row['incorrect'],
            $row['unanswered'],
            $row['score'] === null ? 'Belum final' : $row['score'],
            $row['started_at']?->format('Y-m-d H:i:s'),
            $row['quiz_expires_at']?->format('Y-m-d H:i:s'),
            $row['completed_at']?->format('Y-m-d H:i:s'),
        ];
    }
}
