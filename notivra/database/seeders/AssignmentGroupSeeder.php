<?php

namespace Database\Seeders;

use App\Domains\Assignments\Services\AssignmentVerificationService;
use App\Models\Assignment;
use App\Models\AssignmentGroup;
use App\Models\AssignmentGroupMember;
use App\Models\AssignmentVerification;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentGroupSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $assignment = Assignment::query()->first();

            if (! $assignment) {
                $this->command?->error(
                    'Assignment belum tersedia. Jalankan seeder assignment terlebih dahulu.'
                );

                return;
            }

            /*
             * 1. Buat grup uji.
             */
            $group = AssignmentGroup::firstOrCreate(
                [
                    'assignment_id' => $assignment->id,
                    'reference_code' => 'GRP-' . $assignment->id . '-TEST',
                ],
                [
                    'group_name' => 'Kelompok Uji Statistika',
                    'status' => 'draft',
                ]
            );

            /*
             * 2. Data mahasiswa uji.
             */
            $students = [
                [
                    'student_number' => 'TEST2026001',
                    'name' => 'Ahmad Fauzan',
                    'program_study' => 'Biologi',
                ],
                [
                    'student_number' => 'TEST2026002',
                    'name' => 'Siti Rahma',
                    'program_study' => 'Biologi',
                ],
                [
                    'student_number' => 'TEST2026003',
                    'name' => 'Muhammad Rizki',
                    'program_study' => 'Pendidikan Matematika',
                ],
                [
                    'student_number' => 'TEST2026004',
                    'name' => 'Cut Aisyah',
                    'program_study' => 'Statistika',
                ],
                [
                    'student_number' => 'TEST2026005',
                    'name' => 'Fajar Maulana',
                    'program_study' => 'Pendidikan Matematika',
                ],
            ];

            $verificationService = app(
                AssignmentVerificationService::class
            );

            /*
             * 3. Buat mahasiswa, anggota, verifikasi, dan token kuis.
             */
            foreach ($students as $data) {
                $student = Student::firstOrCreate(
                    [
                        'student_number' => $data['student_number'],
                    ],
                    $data
                );

                $member = AssignmentGroupMember::firstOrCreate([
                    'assignment_id' => $assignment->id,
                    'assignment_group_id' => $group->id,
                    'student_id' => $student->id,
                ]);

                $verification = AssignmentVerification::firstOrCreate(
                    [
                        'assignment_group_member_id' => $member->id,
                    ],
                    [
                        'status' => 'pending',
                    ]
                );

                /*
                 * Terbitkan token hanya jika sesi masih pending
                 * dan token awal belum tersedia.
                 */
                if (
                    $verification->status === 'pending' &&
                    (
                        ! $verification->start_token_hash ||
                        ! $verification->start_token_encrypted
                    )
                ) {
                    $verificationService->issueStartToken(
                        $verification
                    );
                }
            }

            $this->command?->info(
                '   Seeder selesai: grup uji, lima mahasiswa, anggota, verifikasi, dan token kuis telah disiapkan.'
            );

            $this->command?->line(
                '   Kode grup: ' . $group->reference_code
            );
        });
    }
}
