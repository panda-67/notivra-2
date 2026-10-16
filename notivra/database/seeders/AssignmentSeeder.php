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
                    'A. KETENTUAN PENDAFTARAN KELOMPOK',
                    '1. Setiap kelompok terdiri atas 1–5 mahasiswa.',
                    '2. Masukkan nama, NIM, program studi, dan institusi setiap anggota dengan benar. Data ini digunakan untuk identifikasi dan verifikasi peserta.',
                    '3. Pastikan seluruh anggota telah terdaftar sebelum melanjutkan ke pengerjaan tugas.',
                    '4. Setiap anggota bertanggung jawab atas pengerjaan kuisnya sendiri. Anggota kelompok tidak diperkenankan mengerjakan kuis atas nama anggota lain.',

                    'B. KETENTUAN KUIS STATISTIKA INFERENSIAL',
                    '1. Setiap anggota wajib menyelesaikan kuis secara individual.',
                    '2. Kuis terdiri atas 10 soal pilihan ganda. Setiap soal memiliki empat pilihan jawaban: A, B, C, dan D.',
                    '3. Waktu pengerjaan maksimal 50 menit sejak peserta memulai kuis.',
                    '4. Waktu pengerjaan dihitung oleh sistem berdasarkan waktu server, bukan berdasarkan jam perangkat peserta.',
                    '5. Peserta dapat melanjutkan sesi yang sama menggunakan tautan atau token pemulihan sesi yang valid apabila sesi belum berakhir.',
                    '6. Jawaban dapat dikirim selama sesi masih aktif. Peserta harus menyelesaikan dan mengirimkan kuis sebelum batas waktu berakhir.',
                    '7. Apabila batas waktu berakhir, sistem akan menutup kuis secara otomatis. Soal yang belum dijawab akan dianggap salah.',
                    '8. Peserta bertanggung jawab memastikan jawaban telah tersimpan dan kuis telah berstatus selesai.',
                    '9. Status selesai menunjukkan bahwa proses pengerjaan kuis telah ditutup oleh sistem. Status ini tidak berarti seluruh jawaban peserta benar.',

                    'C. KETENTUAN UNGGAH DOKUMEN TUGAS',
                    '1. Fitur unggah dokumen tugas baru tersedia setelah seluruh anggota kelompok berstatus verifikasi completed.',
                    '2. Setiap anggota wajib menyelesaikan kuisnya sendiri agar kelompok memenuhi persyaratan unggah.',
                    '3. Persyaratan unggah didasarkan pada penyelesaian kuis seluruh anggota, bukan pada keharusan memperoleh nilai sempurna.',
                    '4. Kelompok bertanggung jawab atas kebenaran data anggota dan dokumen yang diunggah.',

                    'D. BATAS WAKTU TUGAS',
                    '1. Perhatikan tanggal pembukaan dan tenggat pengumpulan yang ditampilkan pada halaman tugas.',
                    '2. Selesaikan seluruh persyaratan dan unggah dokumen sebelum tenggat pengumpulan.',
                    '3. Apabila terdapat kendala teknis, dokumentasikan kendala tersebut dan hubungi pengelola tugas.',
                ]),

                'opens_at' => '2026-10-15 00:00:00',
                'due_at' => '2026-10-26 00:00:00',
                'published_at' => now(),
                'status' => 'published',
                'created_by' => null,
            ],
        );
    }
}
