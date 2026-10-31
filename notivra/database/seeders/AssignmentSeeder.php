<?php

namespace Database\Seeders;

use App\Models\Assignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Assignment::query()->updateOrCreate(
            [
                'slug' => 'statistika-inferensial-fkip-matematika-usk',
            ],
            [
                'title' => 'Tugas Statistika Inferensial',
                'description' => 'Tugas mata kuliah Statistika Inferensial untuk mahasiswa FKIP Matematika, Universitas Syiah Kuala (USK).',
                'instructions' => implode("\n\n", [
                    'A. PENDAFTARAN KELOMPOK',
                    '1. Setiap kelompok terdiri atas 1–5 mahasiswa.',
                    '2. Pastikan data seluruh anggota benar dan lengkap.',
                    '3. Setiap anggota wajib mengerjakan kuis secara individual.',

                    'B. KETENTUAN KUIS',
                    '1. Kuis terdiri atas 10 soal pilihan ganda dengan waktu maksimal 50 menit.',
                    '2. Waktu dimulai saat jawaban pertama disimpan, bukan saat halaman kuis dibuka.',
                    '3. Urutan menjawab soal dibebaskan, bisa pilih soal secara acak melalui navigasi nomor soal.',
                    '4. Soal yang sudah dijawab tidak dapat diubah jawabannya.',
                    '5. Waktu dihitung berdasarkan server dan tidak diatur ulang saat sesi dipulihkan.',
                    '6. Status completed berarti kuis diselesaikan sebelum waktu habis, sedangkan expired berarti waktu habis secara otomatis.',
                    '7. Jawaban yang sudah tersimpan tetap diperhitungkan. Soal yang belum dijawab tidak memperoleh nilai.',

                    'C. PERANGKAT DAN PEMULIHAN SESI',
                    '1. Gunakan perangkat dan peramban yang sama selama mengerjakan kuis.',
                    '2. Jika sesi terputus, gunakan fitur pemulihan yang tersedia selama sesi masih dapat dipulihkan.',
                    '3. Jika sesi tidak dapat dipulihkan, hubungi pengelola tugas.',

                    'D. PENGUMPULAN TUGAS',
                    '1. Fitur unggah tersedia setelah seluruh anggota berstatus completed atau expired.',
                    '2. Unggah dokumen sebelum tenggat tugas yang ditentukan.',
                ]),

                'opens_at' => '2026-10-15 00:00:00',
                'due_at' => '2026-10-26 00:00:00',
                'published_at' => now(),
                'status' => 'published',
                'created_by' => null,
            ],
        );

        $individualAssignment = Assignment::query()->updateOrCreate(
            [
                'slug' => 'uts-saintek-biologi-unsam',
            ],
            [
                'title' => 'UTS SAINTEK Biologi Unsam',
                'description' => 'Ujian Tengah Semester untuk mahasiswa SAINTEK Program Studi Biologi, Universitas Samudra.',
                'instructions' => implode("\n\n", [
                    'A. KETENTUAN UTS',
                    '1. UTS dikerjakan secara individual.',
                    '2. Peserta harus terdaftar pada sistem sebelum memulai UTS.',
                    '3. UTS terdiri atas soal pilihan ganda dengan durasi maksimal 30 menit.',
                    '4. Waktu dimulai ketika sesi UTS berhasil dibuka.',
                    '5. Waktu pengerjaan dihitung berdasarkan waktu server.',
                    '6. Jawaban dapat diubah selama sesi masih berlangsung.',
                    '7. Jawaban yang tersimpan akan dinilai ketika UTS diselesaikan atau waktu habis.',
                    '8. Soal yang tidak dijawab tidak memperoleh nilai.',

                    'B. PERANGKAT DAN SESI',
                    '1. Gunakan perangkat dan peramban yang sama selama mengerjakan UTS.',
                    '2. Sesi dapat dilanjutkan selama masih berlaku dan menggunakan perangkat yang sama.',
                    '3. Sesi yang telah selesai atau kedaluwarsa tidak dapat dimulai ulang.',
                    '4. Hubungi administrator jika mengalami kendala pada sesi UTS.',
                ]),
                'opens_at' => '2026-10-31 00:00:00',
                'due_at' => '2026-11-15 12:59:59',
                'published_at' => now(),
                'status' => 'published',
                'created_by' => null,
            ],
        );

        $individualAssignment->individualQuizSettings()->updateOrCreate(
            [
                'assignment_id' => $individualAssignment->id,
            ],
            [
                'duration_minutes' => 30,
            ],
        );
    }
}
