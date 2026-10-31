<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentParticipant;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentIndividualQuizParticipantSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            /*
             * 1. Ambil assignment Individual Quiz.
             */
            $assignment = Assignment::query()
                ->where('slug', 'uts-saintek-biologi-unsam')
                ->first();

            if (! $assignment) {
                $this->command?->error(
                    'Assignment Kuis SAINTEK Biologi Unsam belum tersedia.'
                );

                return;
            }

            /*
             * 2. Pastikan assignment memiliki pengaturan Individual Quiz.
             */
            if (! $assignment->individualQuizSettings) {
                $this->command?->error(
                    'Pengaturan Individual Quiz belum tersedia untuk assignment ini.'
                );

                return;
            }

            /*
             * 3. Data mahasiswa uji.
             */
            $students = [
                [
                    'student_number' => 'TESTBIO2026001',
                    'name' => 'Ahmad Fauzan',
                    'program_study' => 'Biologi',
                ],
                [
                    'student_number' => 'TESTBIO2026002',
                    'name' => 'Siti Rahma',
                    'program_study' => 'Biologi',
                ],
                [
                    'student_number' => 'TESTBIO2026003',
                    'name' => 'Muhammad Rizki',
                    'program_study' => 'Biologi',
                ],
                [
                    'student_number' => 'TESTBIO2026004',
                    'name' => 'Cut Aisyah',
                    'program_study' => 'Biologi',
                ],
                [
                    'student_number' => 'TESTBIO2026005',
                    'name' => 'Fajar Maulana',
                    'program_study' => 'Biologi',
                ],
            ];

            /*
             * 4. Buat mahasiswa dan daftarkan sebagai peserta.
             */
            foreach ($students as $data) {
                $student = Student::query()->firstOrCreate(
                    [
                        'student_number' => $data['student_number'],
                    ],
                    [
                        'name' => $data['name'],
                        'program_study' => $data['program_study'],
                    ]
                );

                AssignmentParticipant::query()->firstOrCreate(
                    [
                        'assignment_id' => $assignment->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'status' => 'registered',
                    ]
                );
            }

            $this->command?->info(
                '   Seeder Individual Quiz berhasil dijalankan.'
            );

            $this->command?->line(
                '   Assignment: ' . $assignment->title
            );

            $this->command?->line(
                '   Jumlah mahasiswa uji: ' . count($students)
            );

            $this->command?->line(
                '   Slug: ' . $assignment->slug
            );
        });
    }
}
