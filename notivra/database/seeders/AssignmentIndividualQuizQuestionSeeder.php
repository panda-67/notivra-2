<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentQuizQuestion;
use Illuminate\Database\Seeder;

class AssignmentIndividualQuizQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $assignment = Assignment::query()
            ->where('slug', 'kuis-saintek-biologi-unsam')
            ->firstOrFail();

        $questions = [

            /*
            |--------------------------------------------------------------------------
            | BAB 1 — RAL DAN ANOVA SATU ARAH (POSITION 1–12)
            |--------------------------------------------------------------------------
            */

            [
                'question' => 'Seorang peneliti ingin membandingkan rata-rata pertumbuhan lima varietas tanaman yang ditanam pada kondisi lingkungan relatif homogen. Rancangan yang paling sesuai adalah ...',
                'option_a' => 'Rancangan Acak Lengkap (RAL)',
                'option_b' => 'Rancangan Acak Kelompok (RAK)',
                'option_c' => 'Rancangan Bujur Sangkar Latin (RBSL)',
                'option_d' => 'Rancangan Petak Terbagi',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Prinsip utama yang mendasari penggunaan Rancangan Acak Lengkap adalah ...',
                'option_a' => 'Setiap perlakuan harus ditempatkan pada kelompok yang berbeda',
                'option_b' => 'Unit percobaan relatif homogen sehingga perlakuan dapat diacak sepenuhnya',
                'option_c' => 'Setiap unit percobaan harus menerima dua perlakuan sekaligus',
                'option_d' => 'Pengelompokan dilakukan berdasarkan dua sumber keragaman',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Dalam RAL dengan empat perlakuan dan lima ulangan untuk setiap perlakuan, jumlah seluruh unit percobaan adalah ...',
                'option_a' => '9 unit',
                'option_b' => '15 unit',
                'option_c' => '20 unit',
                'option_d' => '25 unit',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Pada ANOVA satu arah, hipotesis nol (H₀) yang tepat untuk membandingkan empat rata-rata perlakuan adalah ...',
                'option_a' => 'Seluruh rata-rata perlakuan sama',
                'option_b' => 'Seluruh rata-rata perlakuan berbeda',
                'option_c' => 'Setidaknya satu varians perlakuan sama dengan nol',
                'option_d' => 'Seluruh pengamatan memiliki nilai yang identik',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Dalam ANOVA satu arah, nilai F hitung diperoleh dari perbandingan ...',
                'option_a' => 'Jumlah kuadrat perlakuan dengan jumlah seluruh pengamatan',
                'option_b' => 'Rata-rata kuadrat perlakuan dengan rata-rata kuadrat galat',
                'option_c' => 'Rata-rata perlakuan dengan rata-rata keseluruhan',
                'option_d' => 'Varians terbesar dengan varians terkecil secara langsung',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Jika hasil ANOVA menunjukkan p-value = 0,012 dengan α = 0,05, keputusan yang tepat adalah ...',
                'option_a' => 'Gagal menolak H₀ karena p-value lebih kecil dari α',
                'option_b' => 'Menerima seluruh perlakuan memiliki rata-rata sama',
                'option_c' => 'Menolak H₀ karena terdapat bukti perbedaan rata-rata perlakuan',
                'option_d' => 'Menyimpulkan seluruh pasangan perlakuan berbeda nyata',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Setelah ANOVA menunjukkan perbedaan rata-rata yang signifikan pada lebih dari dua perlakuan, tujuan uji lanjut Tukey HSD adalah ...',
                'option_a' => 'Menentukan pasangan rata-rata perlakuan yang berbeda nyata',
                'option_b' => 'Menguji kembali apakah data memiliki distribusi normal',
                'option_c' => 'Menghitung jumlah ulangan yang diperlukan',
                'option_d' => 'Menghilangkan pengaruh perlakuan dari model',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Asumsi normalitas dalam ANOVA terutama berkaitan dengan distribusi ...',
                'option_a' => 'Seluruh nilai variabel perlakuan',
                'option_b' => 'Rata-rata setiap kelompok secara terpisah',
                'option_c' => 'Residual atau galat model',
                'option_d' => 'Jumlah ulangan pada setiap perlakuan',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Uji Levene dalam analisis ANOVA umumnya digunakan untuk mengevaluasi ...',
                'option_a' => 'Normalitas residual',
                'option_b' => 'Homogenitas varians antarkelompok',
                'option_c' => 'Independensi antarperlakuan',
                'option_d' => 'Perbedaan rata-rata keseluruhan',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Mengapa pengacakan perlakuan diperlukan dalam RAL?',
                'option_a' => 'Agar setiap perlakuan menghasilkan rata-rata yang sama',
                'option_b' => 'Agar jumlah kuadrat galat selalu bernilai nol',
                'option_c' => 'Untuk mengurangi potensi bias sistematis dalam penempatan perlakuan',
                'option_d' => 'Untuk memastikan semua asumsi statistik terpenuhi secara otomatis',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Seorang peneliti memperoleh hasil ANOVA yang tidak signifikan. Interpretasi yang paling tepat adalah ...',
                'option_a' => 'Tidak terdapat bukti statistik yang cukup untuk menyatakan rata-rata perlakuan berbeda',
                'option_b' => 'Semua perlakuan terbukti memiliki efek yang identik',
                'option_c' => 'Seluruh data penelitian pasti tidak normal',
                'option_d' => 'Penelitian harus selalu diulang dengan perlakuan berbeda',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Dalam R, perintah yang sesuai untuk melakukan ANOVA satu arah dengan respons pertumbuhan dan faktor varietas adalah ...',
                'option_a' => 'lm(varietas ~ pertumbuhan, data = data)',
                'option_b' => 'aov(pertumbuhan ~ varietas, data = data)',
                'option_c' => 'cor(pertumbuhan, varietas)',
                'option_d' => 'chisq.test(pertumbuhan, varietas)',
                'correct_option' => 'B',
            ],

            /*
            |--------------------------------------------------------------------------
            | BAB 2 — RAK / RCBD (POSITION 13–24)
            |--------------------------------------------------------------------------
            */

            [
                'question' => 'RAK paling tepat digunakan ketika unit percobaan memiliki ...',
                'option_a' => 'Kondisi yang sepenuhnya homogen tanpa sumber keragaman lingkungan',
                'option_b' => 'Satu sumber keragaman lingkungan yang dapat dikendalikan melalui pengelompokan',
                'option_c' => 'Dua sumber keragaman pengganggu yang saling tegak lurus',
                'option_d' => 'Dua faktor perlakuan yang harus diuji interaksinya',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Tujuan utama pembentukan kelompok (blok) dalam RAK adalah ...',
                'option_a' => 'Meningkatkan jumlah perlakuan',
                'option_b' => 'Menghilangkan seluruh variasi biologis',
                'option_c' => 'Mengendalikan keragaman yang berasal dari perbedaan antarblok',
                'option_d' => 'Menggantikan fungsi pengacakan perlakuan',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Dalam percobaan pertumbuhan tanaman, kondisi kesuburan tanah berbeda sepanjang gradien lahan. Strategi rancangan yang sesuai adalah ...',
                'option_a' => 'Membuat blok berdasarkan keseragaman kondisi tanah',
                'option_b' => 'Mengabaikan gradien karena perlakuan telah diacak',
                'option_c' => 'Menempatkan seluruh perlakuan pada satu blok',
                'option_d' => 'Menggunakan RBSL tanpa mempertimbangkan sumber keragaman',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Sebuah RAK memiliki lima perlakuan dan empat blok lengkap. Berapa jumlah unit percobaan yang diperlukan?',
                'option_a' => '9 unit',
                'option_b' => '16 unit',
                'option_c' => '20 unit',
                'option_d' => '25 unit',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Dalam RAK, setiap blok lengkap harus memuat ...',
                'option_a' => 'Satu perlakuan yang dipilih secara acak',
                'option_b' => 'Seluruh perlakuan masing-masing satu kali',
                'option_c' => 'Dua ulangan untuk setiap perlakuan',
                'option_d' => 'Hanya perlakuan dengan respons tertinggi',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Model aditif RAK tanpa interaksi dapat dituliskan sebagai ...',
                'option_a' => 'Yij = μ + τi + βj + εij',
                'option_b' => 'Yij = μ + τi × βj + εij',
                'option_c' => 'Yij = μ + εij saja',
                'option_d' => 'Yij = τi + βj tanpa galat',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Dalam model RAK, simbol βj umumnya merepresentasikan ...',
                'option_a' => 'Pengaruh perlakuan ke-j',
                'option_b' => 'Pengaruh blok ke-j',
                'option_c' => 'Rata-rata keseluruhan',
                'option_d' => 'Interaksi perlakuan dan blok',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Jika hasil ANOVA RAK menunjukkan pengaruh perlakuan signifikan, langkah berikutnya untuk membandingkan rata-rata perlakuan adalah ...',
                'option_a' => 'Menghapus faktor blok dari data mentah',
                'option_b' => 'Melakukan uji lanjut seperti Tukey HSD pada rata-rata perlakuan',
                'option_c' => 'Mengulang uji normalitas terhadap setiap perlakuan sampai signifikan',
                'option_d' => 'Membandingkan seluruh nilai pengamatan tanpa memperhatikan blok',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Keuntungan pengelompokan yang tepat dalam RAK adalah ...',
                'option_a' => 'Mengurangi variasi galat dengan memisahkan sumber keragaman blok',
                'option_b' => 'Meningkatkan variasi antarunit dalam blok',
                'option_c' => 'Membuat semua perlakuan memiliki efek yang sama',
                'option_d' => 'Menghilangkan kebutuhan terhadap replikasi',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Dalam RAK dengan t perlakuan dan b blok lengkap, derajat bebas untuk perlakuan adalah ...',
                'option_a' => 'b - 1',
                'option_b' => 't - 1',
                'option_c' => '(t - 1)(b - 1)',
                'option_d' => 'tb - 1',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Dalam R, formula yang tepat untuk ANOVA RAK dengan respons hasil, perlakuan sebagai faktor, dan blok sebagai faktor adalah ...',
                'option_a' => 'aov(hasil ~ blok, data = data)',
                'option_b' => 'aov(perlakuan ~ hasil + blok, data = data)',
                'option_c' => 'aov(hasil ~ perlakuan + blok, data = data)',
                'option_d' => 'aov(hasil ~ perlakuan * blok, data = data)',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Jika variasi antarblok sangat besar tetapi blok telah dibentuk dengan tepat, dampak yang diharapkan terhadap pengujian perlakuan adalah ...',
                'option_a' => 'Sebagian variasi lingkungan dapat dijelaskan oleh faktor blok sehingga galat berpotensi lebih kecil',
                'option_b' => 'Seluruh variasi perlakuan akan masuk ke dalam galat',
                'option_c' => 'Derajat bebas perlakuan menjadi nol',
                'option_d' => 'Pengaruh perlakuan otomatis menjadi signifikan',
                'correct_option' => 'A',
            ],

            /*
            |--------------------------------------------------------------------------
            | BAB 3 — RBSL / LATIN SQUARE DESIGN (POSITION 25–36)
            |--------------------------------------------------------------------------
            */

            [
                'question' => 'Rancangan Bujur Sangkar Latin (RBSL) digunakan terutama ketika terdapat ...',
                'option_a' => 'Satu faktor perlakuan dan tidak ada sumber keragaman pengganggu',
                'option_b' => 'Satu faktor perlakuan dan dua sumber keragaman pengganggu',
                'option_c' => 'Dua faktor perlakuan yang harus diuji interaksinya',
                'option_d' => 'Dua tingkat galat akibat petak utama dan anak petak',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Dalam RBSL berukuran 4 × 4, berapa jumlah perlakuan yang digunakan?',
                'option_a' => '2 perlakuan',
                'option_b' => '4 perlakuan',
                'option_c' => '8 perlakuan',
                'option_d' => '16 perlakuan',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Karakteristik utama susunan perlakuan dalam RBSL adalah ...',
                'option_a' => 'Setiap perlakuan muncul tepat satu kali pada setiap baris dan setiap kolom',
                'option_b' => 'Setiap perlakuan muncul dua kali pada setiap baris',
                'option_c' => 'Setiap perlakuan hanya muncul pada satu kolom',
                'option_d' => 'Penempatan perlakuan tidak memerlukan pengacakan',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Dalam penelitian pertumbuhan tanaman, terdapat gradien kesuburan dari utara ke selatan dan gradien kelembapan dari timur ke barat. Rancangan yang dapat mengendalikan kedua sumber keragaman tersebut adalah ...',
                'option_a' => 'RAL',
                'option_b' => 'RAK',
                'option_c' => 'RBSL',
                'option_d' => 'RAL faktorial',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Jika RBSL menggunakan lima perlakuan, berapa jumlah baris dan kolom yang diperlukan?',
                'option_a' => '5 baris dan 5 kolom',
                'option_b' => '5 baris dan 10 kolom',
                'option_c' => '10 baris dan 5 kolom',
                'option_d' => '25 baris dan 25 kolom',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Model aditif RBSL dapat dituliskan sebagai ...',
                'option_a' => 'Yijk = μ + τi + ρj + κk + εijk',
                'option_b' => 'Yijk = μ + τi × ρj × κk + εijk',
                'option_c' => 'Yijk = μ + τi + εijk',
                'option_d' => 'Yijk = μ + ρj + κk tanpa perlakuan',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Dalam model RBSL, ρj dan κk masing-masing menunjukkan ...',
                'option_a' => 'Pengaruh perlakuan dan galat',
                'option_b' => 'Pengaruh baris dan pengaruh kolom',
                'option_c' => 'Interaksi baris dan kolom',
                'option_d' => 'Rata-rata perlakuan dan rata-rata keseluruhan',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Mengapa model RBSL standar umumnya tidak memasukkan interaksi perlakuan × baris dan perlakuan × kolom?',
                'option_a' => 'Karena interaksi tersebut selalu bernilai nol dalam semua percobaan',
                'option_b' => 'Karena struktur rancangan dan keterbatasan derajat bebas membuat model standar menggunakan asumsi aditif',
                'option_c' => 'Karena perlakuan tidak memiliki pengaruh terhadap respons',
                'option_d' => 'Karena baris dan kolom tidak boleh dimasukkan ke model',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Pada RBSL dengan lima perlakuan, derajat bebas total adalah ...',
                'option_a' => '15',
                'option_b' => '20',
                'option_c' => '24',
                'option_d' => '25',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Dalam RBSL berukuran 4 × 4, derajat bebas galat adalah ...',
                'option_a' => '3',
                'option_b' => '6',
                'option_c' => '9',
                'option_d' => '12',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Formula R yang sesuai untuk model aditif RBSL dengan respons hasil, perlakuan, baris, dan kolom adalah ...',
                'option_a' => 'aov(hasil ~ perlakuan + baris + kolom, data = data)',
                'option_b' => 'aov(hasil ~ perlakuan * baris * kolom, data = data)',
                'option_c' => 'aov(perlakuan ~ hasil + baris + kolom, data = data)',
                'option_d' => 'aov(hasil ~ baris * kolom, data = data)',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Salah satu keterbatasan penting RBSL adalah ...',
                'option_a' => 'Tidak dapat menguji pengaruh perlakuan',
                'option_b' => 'Jumlah perlakuan harus sama dengan jumlah baris dan kolom sehingga ukuran rancangan dapat menjadi besar',
                'option_c' => 'Tidak dapat mengendalikan variasi lingkungan',
                'option_d' => 'Tidak dapat menggunakan analisis varians',
                'correct_option' => 'B',
            ],

            /*
            |--------------------------------------------------------------------------
            | BAB 4 — RAK FAKTORIAL (POSITION 37–48)
            |--------------------------------------------------------------------------
            */

            [
                'question' => 'Dalam percobaan faktorial, peneliti mempelajari pengaruh dua faktor atau lebih secara bersamaan. Keunggulan utamanya adalah ...',
                'option_a' => 'Hanya dapat menguji satu faktor dalam satu percobaan',
                'option_b' => 'Dapat mengevaluasi pengaruh utama dan interaksi antar faktor',
                'option_c' => 'Tidak membutuhkan pengacakan',
                'option_d' => 'Selalu menghasilkan jumlah unit percobaan lebih sedikit daripada percobaan satu faktor',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Penelitian menguji tiga dosis pupuk dan dua tingkat irigasi. Berapa kombinasi perlakuan faktorial yang terbentuk?',
                'option_a' => '3 kombinasi',
                'option_b' => '5 kombinasi',
                'option_c' => '6 kombinasi',
                'option_d' => '9 kombinasi',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Dalam RAK faktorial dengan faktor A memiliki tiga taraf dan faktor B memiliki dua taraf, jumlah kombinasi perlakuan adalah ...',
                'option_a' => '5',
                'option_b' => '6',
                'option_c' => '8',
                'option_d' => '12',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Pengaruh utama faktor A dalam percobaan faktorial menggambarkan ...',
                'option_a' => 'Pengaruh faktor A yang dirata-ratakan pada seluruh taraf faktor B',
                'option_b' => 'Pengaruh faktor B pada satu taraf faktor A saja',
                'option_c' => 'Pengaruh interaksi antara blok dan galat',
                'option_d' => 'Perbedaan seluruh kombinasi tanpa memperhatikan faktor',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Interaksi antara faktor pupuk dan irigasi terjadi ketika ...',
                'option_a' => 'Kedua faktor memiliki jumlah taraf yang sama',
                'option_b' => 'Pengaruh pupuk terhadap respons bergantung pada tingkat irigasi',
                'option_c' => 'Kedua faktor tidak memiliki pengaruh terhadap respons',
                'option_d' => 'Seluruh kombinasi perlakuan menghasilkan rata-rata yang identik',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Hasil ANOVA faktorial menunjukkan interaksi faktor A × B signifikan. Interpretasi yang paling tepat adalah ...',
                'option_a' => 'Pengaruh faktor A selalu sama pada semua taraf faktor B',
                'option_b' => 'Pengaruh salah satu faktor bergantung pada taraf faktor lainnya sehingga interaksi perlu ditelaah',
                'option_c' => 'Kedua faktor harus dikeluarkan dari model',
                'option_d' => 'Seluruh rata-rata kombinasi perlakuan pasti berbeda nyata',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Jika interaksi A × B signifikan, pendekatan interpretasi yang tepat adalah ...',
                'option_a' => 'Langsung menyimpulkan pengaruh utama tanpa analisis tambahan',
                'option_b' => 'Mengabaikan interaksi karena pengaruh utama lebih penting',
                'option_c' => 'Mengkaji efek sederhana atau perbandingan pada kombinasi taraf faktor yang relevan',
                'option_d' => 'Menghapus seluruh pengamatan yang memiliki nilai ekstrem',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Dalam RAK faktorial, model yang memasukkan pengaruh blok, faktor A, faktor B, dan interaksi A × B adalah ...',
                'option_a' => 'Yijk = μ + βk + αi + γj + (αγ)ij + εijk',
                'option_b' => 'Yijk = μ + αi + εijk',
                'option_c' => 'Yijk = μ + βk + εijk',
                'option_d' => 'Yijk = μ + αi × γj tanpa galat',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Dalam R, formula yang sesuai untuk menguji faktor A, faktor B, dan interaksi keduanya dalam RAK faktorial adalah ...',
                'option_a' => 'aov(hasil ~ blok + A + B, data = data)',
                'option_b' => 'aov(hasil ~ blok + A * B, data = data)',
                'option_c' => 'aov(A * B ~ hasil + blok, data = data)',
                'option_d' => 'aov(hasil ~ A / B, data = data)',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Jika interaksi A × B tidak signifikan, sedangkan pengaruh utama A signifikan, interpretasi yang paling sesuai adalah ...',
                'option_a' => 'Rata-rata respons berbeda antar taraf A setelah dirata-ratakan terhadap taraf B, tanpa bukti interaksi yang signifikan',
                'option_b' => 'Faktor A tidak dapat diinterpretasikan',
                'option_c' => 'Faktor B pasti memiliki pengaruh yang signifikan',
                'option_d' => 'Seluruh kombinasi A dan B memiliki rata-rata yang sama',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Dalam percobaan faktorial dengan faktor A sebanyak 3 taraf, faktor B sebanyak 4 taraf, dan 3 blok, jumlah unit percobaan adalah ...',
                'option_a' => '12 unit',
                'option_b' => '24 unit',
                'option_c' => '36 unit',
                'option_d' => '48 unit',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Untuk membandingkan kombinasi perlakuan setelah ditemukan interaksi yang signifikan, analisis yang dapat digunakan adalah ...',
                'option_a' => 'Menguji korelasi antara nomor perlakuan dan blok',
                'option_b' => 'Melakukan perbandingan efek sederhana atau estimated marginal means dengan penyesuaian perbandingan berganda',
                'option_c' => 'Menghapus faktor dengan p-value terbesar',
                'option_d' => 'Mengganti seluruh analisis dengan statistik deskriptif',
                'correct_option' => 'B',
            ],

            /*
            |--------------------------------------------------------------------------
            | BAB 5 — RANCANGAN PETAK TERBAGI / SPLIT PLOT (POSITION 49–60)
            |--------------------------------------------------------------------------
            */

            [
                'question' => 'Rancangan split plot umumnya digunakan ketika ...',
                'option_a' => 'Seluruh faktor perlakuan dapat diacak dengan tingkat kemudahan yang sama',
                'option_b' => 'Terdapat faktor yang lebih sulit atau mahal untuk diterapkan sehingga memerlukan pengacakan pada petak utama',
                'option_c' => 'Penelitian hanya memiliki satu perlakuan dan satu ulangan',
                'option_d' => 'Tidak terdapat variasi antarunit percobaan',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Dalam percobaan split plot yang menguji sistem irigasi dan varietas tanaman, sistem irigasi lebih sulit diterapkan. Faktor sistem irigasi sebaiknya ditempatkan sebagai ...',
                'option_a' => 'Faktor anak petak',
                'option_b' => 'Faktor petak utama',
                'option_c' => 'Faktor galat',
                'option_d' => 'Faktor blok',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Dalam split plot, faktor yang diterapkan pada unit percobaan yang lebih kecil di dalam setiap petak utama disebut ...',
                'option_a' => 'Faktor petak utama',
                'option_b' => 'Faktor blok',
                'option_c' => 'Faktor anak petak',
                'option_d' => 'Faktor pengganggu',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Mengapa analisis split plot memiliki dua strata galat?',
                'option_a' => 'Karena seluruh faktor harus diuji dengan dua ulangan',
                'option_b' => 'Karena pengacakan faktor petak utama dan anak petak berlangsung pada tingkat unit percobaan yang berbeda',
                'option_c' => 'Karena setiap perlakuan harus memiliki dua nilai rata-rata',
                'option_d' => 'Karena model split plot tidak menggunakan faktor blok',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Dalam split plot, pengaruh faktor petak utama umumnya diuji menggunakan ...',
                'option_a' => 'Galat petak utama (Error A)',
                'option_b' => 'Galat anak petak (Error B)',
                'option_c' => 'Jumlah kuadrat total',
                'option_d' => 'Varians dari faktor blok saja tanpa galat',
                'correct_option' => 'A',
            ],
            [
                'question' => 'Dalam split plot, pengaruh faktor anak petak dan interaksi petak utama × anak petak umumnya diuji menggunakan ...',
                'option_a' => 'Galat petak utama saja',
                'option_b' => 'Galat anak petak',
                'option_c' => 'Varians antarblok tanpa residual',
                'option_d' => 'Jumlah kuadrat total',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Sebuah penelitian menggunakan tiga sistem irigasi sebagai petak utama, empat varietas sebagai anak petak, dan tiga blok. Berapa jumlah unit anak petak yang diamati?',
                'option_a' => '12 unit',
                'option_b' => '24 unit',
                'option_c' => '36 unit',
                'option_d' => '48 unit',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Dalam split plot dengan faktor petak utama A sebanyak 3 taraf, faktor anak petak B sebanyak 4 taraf, dan 3 blok, berapa jumlah kombinasi perlakuan A × B pada setiap blok?',
                'option_a' => '7 kombinasi',
                'option_b' => '9 kombinasi',
                'option_c' => '12 kombinasi',
                'option_d' => '36 kombinasi',
                'correct_option' => 'C',
            ],
            [
                'question' => 'Dalam R, struktur model split plot yang menggunakan fungsi aov() dan faktor blok serta faktor A sebagai petak utama dan B sebagai anak petak dapat ditulis sebagai ...',
                'option_a' => 'aov(hasil ~ A + B + Error(blok), data = data)',
                'option_b' => 'aov(hasil ~ A * B + Error(blok/A), data = data)',
                'option_c' => 'aov(hasil ~ blok + A * B, data = data)',
                'option_d' => 'aov(A * B ~ hasil + Error(blok/A), data = data)',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Dalam pendekatan model campuran menggunakan lmer(), struktur efek acak yang sesuai untuk blok dan petak utama yang tersarang dalam blok adalah ...',
                'option_a' => 'lmer(hasil ~ A * B + (1 | blok), data = data)',
                'option_b' => 'lmer(hasil ~ A * B + (1 | blok/A), data = data)',
                'option_c' => 'lmer(hasil ~ blok * A * B, data = data)',
                'option_d' => 'lmer(A * B ~ hasil + (1 | blok), data = data)',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Jika faktor petak utama memiliki pengaruh yang tidak signifikan, sedangkan faktor anak petak signifikan, kesimpulan yang tepat adalah ...',
                'option_a' => 'Faktor anak petak tidak dapat diinterpretasikan',
                'option_b' => 'Tidak terdapat bukti pengaruh faktor petak utama, tetapi terdapat bukti pengaruh faktor anak petak',
                'option_c' => 'Seluruh faktor dalam penelitian harus dinyatakan tidak signifikan',
                'option_d' => 'Model split plot tidak dapat digunakan',
                'correct_option' => 'B',
            ],
            [
                'question' => 'Dalam analisis split plot, fungsi emmeans dapat digunakan untuk ...',
                'option_a' => 'Menghapus efek acak dari model',
                'option_b' => 'Menghitung jumlah unit percobaan secara otomatis',
                'option_c' => 'Mengestimasi rata-rata marginal dan melakukan perbandingan terencana atau uji lanjut',
                'option_d' => 'Menggantikan seluruh pengujian asumsi model',
                'correct_option' => 'C',
            ],
        ];

        if (count($questions) !== 60) {
            throw new \RuntimeException(
                'Bank soal UNSAM harus berjumlah tepat 60 soal.'
            );
        }

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
