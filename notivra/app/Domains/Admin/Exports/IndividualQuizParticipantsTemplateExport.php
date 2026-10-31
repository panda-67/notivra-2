<?php

namespace App\Domains\Admin\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class IndividualQuizParticipantsTemplateExport implements
    FromArray,
    WithHeadings,
    WithColumnFormatting,
    ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['', '', ''],
        ];
    }

    public function headings(): array
    {
        return [
            'student_number',
            'name',
            'program_study',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
