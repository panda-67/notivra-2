<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::insert([
            [
                'id' => Str::uuid(),
                'slug' => 'data-analysis',
                'icon' => '📊',
                'title' => json_encode([
                    'en' => 'Statistical Analysis & Visualization',
                    'id' => 'Analisis Statistik & Visualisasi',
                ]),
                'summary' => json_encode([
                    'en' => 'Accurate, clear analysis and visuals.',
                    'id' => 'Analisis dan visualisasi yang akurat dan jelas.',
                ]),
                'description' => json_encode([
                    'en' => 'We provide statistical analysis using R, Python, or SPSS. Our visuals speak science—clear, relevant, and publication-ready.',
                    'id' => 'Kami menyediakan analisis statistik menggunakan R, Python, atau SPSS. Visualisasi kami berbicara sains — jelas, relevan, dan siap publikasi.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'research-design',
                'icon' => '🧪',
                'title' => json_encode([
                    'en' => 'Research Design & Methodology',
                    'id' => 'Desain & Metodologi Penelitian',
                ]),
                'summary' => json_encode([
                    'en' => 'From surveys to lab design.',
                    'id' => 'Dari survei hingga desain laboratorium.',
                ]),
                'description' => json_encode([
                    'en' => 'We help you plan your research—from hypothesis to instruments—ensuring sound methodology and real-world relevance.',
                    'id' => 'Kami membantu merancang penelitian Anda—dari hipotesis hingga instrumen—dengan metodologi yang kuat dan relevansi nyata.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'mapping-gis',
                'icon' => '🗺️',
                'title' => json_encode([
                    'en' => 'Mapping & GIS',
                    'id' => 'Pemetaan & GIS',
                ]),
                'summary' => json_encode([
                    'en' => 'Publication-ready maps and spatial insights.',
                    'id' => 'Peta siap publikasi dan wawasan spasial.',
                ]),
                'description' => json_encode([
                    'en' => 'Leverage GIS tools to map, analyze, and present spatial data. Ideal for ecology, geography, conservation, and more.',
                    'id' => 'Manfaatkan alat GIS untuk memetakan, menganalisis, dan menyajikan data spasial. Cocok untuk ekologi, geografi, konservasi, dan lainnya.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'consultation',
                'icon' => '🎓',
                'title' => json_encode([
                    'en' => 'Consultation & Mentoring',
                    'id' => 'Konsultasi & Pendampingan',
                ]),
                'summary' => json_encode([
                    'en' => 'Personalized academic guidance.',
                    'id' => 'Bimbingan akademik yang dipersonalisasi.',
                ]),
                'description' => json_encode([
                    'en' => 'We offer one-on-one academic support, including publishing strategy, data interpretation, and thesis mentoring.',
                    'id' => 'Kami menyediakan dukungan akademik personal, termasuk strategi publikasi, interpretasi data, dan pendampingan skripsi/tesis.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
