<?php

namespace App\Domains\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Inertia\Response;

class DashboardController extends Controller
{
    #[Middleware('auth')]
    public function index(): Response
    {
        // Sementara kita pakai data dummy dulu untuk testing layout
        $stats = [
            ['label' => 'Total Draf Aktif', 'value' => '12', 'icon' => 'document'],
            ['label' => 'Permintaan Layanan', 'value' => '4', 'icon' => 'bell'],
            ['label' => 'Penyimpanan Terpakai', 'value' => '1.4 GB / 5 GB', 'icon' => 'database'],
        ];

        return inertia('Admin/Dashboard', [
            'stats' => $stats
        ]);
    }
}
