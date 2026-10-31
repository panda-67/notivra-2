<?php

namespace App\Domains\Assignments\Services;

use App\Models\AssignmentParticipant;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IndividualQuizParticipantImportService
{
    public function importRow(int $assignmentId, array $row): array
    {
        $studentNumber = trim((string) ($row['student_number'] ?? ''));
        $name = trim((string) ($row['name'] ?? ''));
        $programStudy = trim((string) ($row['program_study'] ?? ''));

        if ($studentNumber === '') {
            throw ValidationException::withMessages([
                'student_number' => 'Student number wajib diisi.',
            ]);
        }

        if (mb_strlen($studentNumber) > 32) {
            throw ValidationException::withMessages([
                'student_number' => 'Student number maksimal 32 karakter.',
            ]);
        }

        return DB::transaction(function () use (
            $assignmentId,
            $studentNumber,
            $name,
            $programStudy,
        ) {
            $student = Student::query()
                ->where('student_number', $studentNumber)
                ->lockForUpdate()
                ->first();

            if ($student && $student->status === 'inactive') {
                throw ValidationException::withMessages([
                    'student_number' => 'Mahasiswa berstatus inactive dan tidak dapat didaftarkan.',
                ]);
            }

            $isNewStudent = false;

            if (! $student) {
                if ($name === '') {
                    throw ValidationException::withMessages([
                        'name' => 'Nama wajib diisi untuk mahasiswa baru.',
                    ]);
                }

                $student = Student::query()->create([
                    'student_number' => $studentNumber,
                    'name' => $name,
                    'program_study' => $programStudy !== '' ? $programStudy : null,
                    'institution' => null,
                    'status' => 'active',
                ]);

                $isNewStudent = true;
            }

            $participant = AssignmentParticipant::query()
                ->where('assignment_id', $assignmentId)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if ($participant) {
                return [
                    'status' => 'skipped',
                    'reason' => $participant->status === 'disabled'
                        ? 'Peserta sudah terdaftar tetapi berstatus disabled. Status dipertahankan.'
                        : 'Peserta sudah terdaftar.',
                    'new_student' => false,
                    'new_participant' => false,
                ];
            }

            AssignmentParticipant::query()->create([
                'assignment_id' => $assignmentId,
                'student_id' => $student->id,
                'status' => 'registered',
            ]);

            return [
                'status' => 'success',
                'reason' => null,
                'new_student' => $isNewStudent,
                'new_participant' => true,
            ];
        });
    }
}
