<?php

namespace App\Domains\Hire\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\HireDraft;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    public function store(ProjectRequest $request)
    {
        $validated = $request->validated();
        unset($validated['step']);

        $draft = HireDraft::create([
            'user_id' => Auth::id(),
            'step' => 2,
            'data' => $validated,
            'status' => 'draft'
        ]);

        if (!Auth::check()) {
            // Simpan tujuan akhir ke session agar setelah login balik lagi ke form
            session(['url.intended' => route('hire.apply', ['d' => $draft->id])]);

            return redirect()->route('login', ['d' => $draft->id])
                ->with('success', 'Data awal disimpan! Silakan login untuk melanjutkan ke detail proyek.');
        }

        return redirect()->route('hire.apply', ['d' => $draft->id])->with('success', 'Draft disimpan.');
    }

    /**
     * Update data per step
     */
    #[Middleware('auth')]
    public function update(ProjectRequest $request, HireDraft $draft)
    {
        $validated = $request->validated();
        $currentData = $draft->data;
        unset($validated['step']);

        if ($request->hasFile('files')) {
            if (isset($currentData['files'])) {
                Storage::disk('public')->delete($currentData['files']);
            }

            $file = $request->file('files');
            $fileName = $draft->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs('drafts/attachments', $fileName, 'public');
            $validated['files'] = $path;
        }

        $draft->update([
            'data' => array_merge($draft->data ?? [], $validated),
            'step' => (int) $request->input('next_step', $draft->step),
        ]);

        return redirect()->back()->with('success', 'Progres disimpan.');
    }

    /**
     * Submit akhir: Memindahkan data draft ke tabel utama (hire_requests)
     */
    #[Middleware('auth')]
    public function submit(ProjectRequest $request, HireDraft $draft)
    {
        $validated = $request->validated();
        unset($validated['step']);

        if ($draft->step < 3) {
            return redirect()->back()->withErrors(['message' => 'Lengkapi semua step sebelum mengirim.']);
        }

        try {
            DB::transaction(function () use ($draft, $validated) {
                $draft->update([
                    'data' => array_merge($draft->data ?? [], $validated),
                    'status' => 'submitted'
                ]);

                $data = $draft->fresh()->data;

                $project = Project::create([
                    'service_id'    => $data['service_id'],
                    'name'          => Auth::user()->name ?? $data['name'],
                    'email'         => Auth::user()->email ?? $data['email'],
                    'phone'         => $data['phone'] ?? null,
                    'institution'   => $data['institution'] ?? null,
                    'level'         => $data['level'] ?? null,
                    'field'         => $data['field'] ?? null,
                    'topic'         => $data['topic'] ?? null,
                    'description'   => $data['description'] ?? null,
                    'output'        => $data['output'] ?? null,
                    'deadline'      => $data['deadline'],
                    'address'       => $data['address'] ?? null,
                    'city'          => $data['city'] ?? null,
                    'postal_code'   => $data['postal_code'] ?? null,
                    'willing_dp'    => $data['willing_dp'] ?? false,
                    'budget'        => $data['budget'] ?? 0,
                    'files'         => $data['files'] ?? null,
                    'user_id'       => Auth::id() ?? null, // Link ke user yang sedang login
                    'status'        => 'pending',
                ]);

                if ($project->files) {
                    $newPath = str_replace('drafts/', 'projects/', $project->files);
                    Storage::disk('public')->move($project->files, $newPath);
                    $project->update(['files' => $newPath]);
                }
            });

            // Redirect ke halaman sukses atau dashboard
            return redirect()->route('dashboard')->with('success', 'Permintaan berhasil dikirim!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Terjadi kesalahan sistem.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }
}
