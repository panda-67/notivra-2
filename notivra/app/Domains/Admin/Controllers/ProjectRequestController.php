<?php

namespace App\Domains\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class ProjectRequestController extends Controller
{
    #[Middleware('auth')]
    public function index(): Response
    {
        // Data tiruan yang merepresentasikan antrean proyek akademik di Notivra
        $requests = [
            [
                'id' => 'SR-2026-001',
                'project_name' => 'Analisis Filogenetik Isolat ABR Sekuens DNA',
                'client' => 'Dr. Fahmi',
                'service_type' => 'Review & Validasi Draf',
                'cost' => 'Rp 3.500.000',
                'status' => 'Processing',
                'created_at' => '12 Mei 2026'
            ],
            [
                'id' => 'SR-2026-002',
                'project_name' => 'Manuskrip Hubungan Ekologi Rawa Singkil',
                'client' => 'Siti Aminah, M.Si',
                'service_type' => 'Penyusunan Subtitle & Format Publikasi',
                'cost' => 'Rp 2.000.000',
                'status' => 'Completed',
                'created_at' => '10 Mei 2026'
            ],
            [
                'id' => 'SR-2026-003',
                'project_name' => 'Drafting Proyek Education Sciences (MDPI Submission)',
                'client' => 'Universitas Syiah Kuala',
                'service_type' => 'Pendampingan Submit & APC Tracker',
                'cost' => '2.000 CHF', // Contoh akomodasi mata uang asing untuk jurnal internasional
                'status' => 'Pending',
                'created_at' => '16 Mei 2026'
            ],
        ];

        return Inertia::render('Admin/Project/Index', [
            'serviceRequests' => $requests
        ]);
    }

    #[Middleware('auth')]
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Processing,Completed,Cancelled'
        ]);

        // Logika update ke database ditaruh di sini nantinya
        // Contoh: Project::findOrFail($id)->update(['status' => $request->status]);

        return back()->with('success', 'Status layanan berhasil diperbarui.');
    }
}
