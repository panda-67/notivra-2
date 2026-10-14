<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Inertia\Inertia;

class ServiceController extends Controller
{
    /**
     * Menampilkan daftar semua layanan.
     */
    public function index()
    {
        $services = Service::latest()->get();

        return Inertia::render('Services/Index', [
            'services' => $services
        ]);
    }

    /**
     * Menampilkan detail satu layanan.
     */
    public function show(Service $service)
    {
        return Inertia::render('Services/Show', [
            'service' => $service
        ]);
    }
}
