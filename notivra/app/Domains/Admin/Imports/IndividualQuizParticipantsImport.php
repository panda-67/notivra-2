<?php

namespace App\Domains\Admin\Imports;

use App\Domains\Assignments\Services\IndividualQuizParticipantImportService;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Throwable;

class IndividualQuizParticipantsImport implements OnEachRow, WithHeadingRow
{
    use RemembersRowNumber;

    public array $summary = [
        'processed' => 0,
        'new_students' => 0,
        'new_participants' => 0,
        'skipped' => 0,
        'failed' => 0,
        'failures' => [],
    ];

    public function __construct(
        private readonly int $assignmentId,
        private readonly IndividualQuizParticipantImportService $service,
    ) {}

    public function onRow(Row $row): void
    {
        $rowNumber = $row->getIndex();
        $data = $row->toArray();

        // Abaikan baris yang seluruh kolomnya kosong.
        if (collect($data)->every(
            fn($value) => $value === null || trim((string) $value) === ''
        )) {
            return;
        }

        $this->summary['processed']++;

        try {
            $result = $this->service->importRow(
                $this->assignmentId,
                $data,
            );

            if ($result['status'] === 'skipped') {
                $this->summary['skipped']++;

                return;
            }

            if ($result['new_student']) {
                $this->summary['new_students']++;
            }

            if ($result['new_participant']) {
                $this->summary['new_participants']++;
            }
        } catch (Throwable $exception) {
            $this->summary['failed']++;

            $this->summary['failures'][] = [
                'row' => $rowNumber,
                'student_number' => $data['student_number'] ?? null,
                'reason' => $this->errorMessage($exception),
            ];
        }
    }

    private function errorMessage(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return collect($exception->errors())
                ->flatten()
                ->first() ?? 'Data tidak valid.';
        }

        report($exception);

        return 'Gagal memproses baris ini. Periksa data dan pastikan NIM tidak bermasalah.';
    }
}
