<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentQuizQuestion;
use Illuminate\Database\Seeder;

class AssignmentQuizQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $assignment = Assignment::query()
            ->where('slug', 'statistika-inferensial-fkip-matematika-usk')
            ->firstOrFail();

        $questions = [
            [
                'question' => 'Dalam penelitian, apa yang dimaksud dengan populasi?',
                'option_a' => 'Sebagian anggota yang dipilih untuk diteliti',
                'option_b' => 'Seluruh objek atau individu yang menjadi sasaran penelitian',
                'option_c' => 'Hasil perhitungan statistik dari sampel',
                'option_d' => 'Sekumpulan data yang selalu berdistribusi normal',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Apa fungsi utama hipotesis nol (H₀) dalam pengujian hipotesis?',
                'option_a' => 'Menyatakan bahwa selalu terdapat perbedaan',
                'option_b' => 'Menyatakan bahwa semua data berdistribusi normal',
                'option_c' => 'Menjadi pernyataan awal yang diuji, mengatakan tidak adanya perbedaan',
                'option_d' => 'Menentukan ukuran sampel secara otomatis',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Dalam pengujian hipotesis, diperoleh nilai p = 0,03 dengan taraf signifikansi α = 0,05. Apa keputusan yang tepat?',
                'option_a' => 'Menolak H₀ karena nilai p kurang dari α',
                'option_b' => 'Menerima H₀ karena nilai p kurang dari α',
                'option_c' => 'Menolak H₀ hanya jika ukuran sampel diperbesar',
                'option_d' => 'Tidak dapat mengambil keputusan tanpa mengetahui nilai rata-rata',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Kesalahan tipe I terjadi ketika peneliti ...',
                'option_a' => 'Gagal menolak H₀ yang sebenarnya salah',
                'option_b' => 'Menggunakan sampel yang terlalu besar',
                'option_c' => 'Menghitung statistik deskriptif dengan benar',
                'option_d' => 'Menolak H₀ padahal H₀ sebenarnya benar',
                'correct_option' => 'D',
            ],
            [
                'question' => 'Peneliti ingin membandingkan rata-rata nilai dua kelompok mahasiswa yang berbeda dan saling independen. Data numerik memenuhi prasyarat yang diperlukan untuk uji parametrik. Uji yang sesuai adalah ...',
                'option_a' => 'Uji chi-kuadrat independensi',
                'option_b' => 'Uji t dua sampel independen',
                'option_c' => 'Uji korelasi Pearson',
                'option_d' => 'Uji t berpasangan',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Uji chi-kuadrat independensi umumnya digunakan untuk mengetahui ...',
                'option_a' => 'Perbedaan dua rata-rata populasi',
                'option_b' => 'Kekuatan hubungan linear dua variabel numerik',
                'option_c' => 'Hubungan antara dua variabel kategorik',
                'option_d' => 'Besarnya simpangan baku populasi',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Koefisien korelasi Pearson terutama digunakan untuk mengukur ...',
                'option_a' => 'Arah dan kekuatan hubungan linear antara dua variabel numerik',
                'option_b' => 'Perbedaan proporsi dua kelompok kategorik',
                'option_c' => 'Perbedaan median dua kelompok tanpa asumsi apa pun',
                'option_d' => 'Peluang bahwa hipotesis nol benar',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Interpretasi yang paling tepat untuk interval kepercayaan 95% adalah ...',
                'option_a' => '95% data sampel pasti berada di dalam interval tersebut',
                'option_b' => 'Parameter populasi berubah-ubah mengikuti interval sampel',
                'option_c' => 'Peluang hipotesis nol benar selalu sebesar 5%',
                'option_d' => 'Dalam pengambilan sampel berulang, sekitar 95% interval yang dibentuk dengan metode tersebut akan mencakup parameter populasi atau Kesalahan dalam pengambilan data yang diperbolahkan adalah sebesar 5%',
                'correct_option' => 'D',
            ],
            [
                'question' => 'Peneliti mengukur nilai mahasiswa yang sama sebelum dan sesudah mengikuti suatu pelatihan. Jika selisih skor memenuhi asumsi uji parametrik, uji yang sesuai untuk membandingkan rata-ratanya adalah ...',
                'option_a' => 'Uji t dua sampel independen',
                'option_b' => 'Uji t berpasangan',
                'option_c' => 'Uji chi-kuadrat independensi',
                'option_d' => 'Uji korelasi Pearson',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Dua kelompok mahasiswa saling independen. Peneliti ingin membandingkan distribusi atau kecenderungan peringkat skor ordinal antara kedua kelompok tanpa mengandalkan asumsi normalitas atau data tidak tersebar normal. Uji yang dapat dipertimbangkan adalah ...',
                'option_a' => 'Uji t berpasangan',
                'option_b' => 'Uji korelasi Pearson',
                'option_c' => 'Uji Mann–Whitney U',
                'option_d' => 'Uji chi-kuadrat goodness-of-fit',
                'correct_option' => 'C',
            ],
        ];

        foreach ($questions as $index => $question) {
            AssignmentQuizQuestion::query()->updateOrCreate(
                [
                    'assignment_id' => $assignment->id,
                    'position' => $index + 1,
                ],
                $question,
            );
        }
    }
}
