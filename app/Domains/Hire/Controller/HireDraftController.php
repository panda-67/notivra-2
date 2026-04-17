<?php

namespace App\Domains\Hire\Controller;

use App\Http\Controllers\Controller;
use App\Models\HireDraft;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HireDraftController extends Controller
{
    /**
     * Menampilkan form (Halaman utama form multi-step)
     */
    public function create(Request $request)
    {
        // Jika ada draft_id di URL, ambil datanya untuk melanjutkan
        $draft = null;
        if ($request->has('d')) {
            $draft = HireDraft::find($request->input('d'));
        }

        $locale = App::getLocale();

        return Inertia::render('Hire/HireForm', [
            'draft' => $draft,
            'services' => Service::all()->map(function ($service) use ($locale) {
                return [
                    'id' => $service->id,
                    'title' => $service->title[$locale] ?? $service->title['id'] ?? 'No Title'
                ];
            })
        ]);
    }

    /**
     * Membuat draft baru di database (Inisialisasi)
     */
    public function store(Request $request)
    {
        $validated = $this->validateStep($request);

        $draft = HireDraft::create([
            'step' => 2,
            'data' => $validated,
            'status' => 'draft'
        ]);

        return redirect()->route('hire.apply', ['d' => $draft->id]);
    }

    /**
     * Update data per step
     */
    public function update(Request $request, HireDraft $draft)
    {
        $validated = $this->validateStep($request);

        $newStep = $request->input('next_step', $draft->step);

        $draft->update([
            'data' => array_merge($draft->data ?? [], $validated),
            'step' => $newStep,
        ]);

        return redirect()->back();
    }

    /**
     * Submit akhir: Memindahkan data draft ke tabel utama (hire_requests)
     */
    public function submit(Request $request, HireDraft $draft)
    {
        $validated = $this->validateStep($request);

        try {
            DB::transaction(function () use ($draft, $validated) {
                // Contoh logika: Pindahkan data dari draft ke tabel asli
                // $hireRequest = HireRequest::create([
                //    'client_name' => $draft->data['name'],
                //    'service_id'  => $draft->data['service'],
                //    'topic'       => $draft->data['topic'],
                //    'deadline'    => $draft->data['deadline'],
                //    'user_id'     => auth()->id(),
                // ]);

                // Update status draft
                $draft->update([
                    'data' => array_merge($draft->data ?? [], $validated),
                    'status' => 'submitted'
                ]);
            });

            // Redirect ke halaman sukses atau dashboard
            return redirect()->route('dashboard')->with('success', 'Permintaan berhasil dikirim!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'message' => 'Terjadi kesalahan sistem.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Logic Validasi dipisahkan per step
     */
    private function validateStep(Request $request)
    {
        return match ((int) $request->step) {
            1 => $request->validate([
                'name' => 'required|string|max:255',
                'service' => 'required|uuid|exists:services,id',
            ]),
            2 => $request->validate([
                'topic' => 'required|string|min:10',
            ]),
            3 => $request->validate([
                'deadline' => 'required|date|after:today',
            ]),
            default => [],
        };
    }
}
