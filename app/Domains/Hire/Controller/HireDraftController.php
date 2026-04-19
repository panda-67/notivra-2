<?php

namespace App\Domains\Hire\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Models\HireDraft;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\Request;
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
    public function store(StoreProjectRequest $request)
    {
        $validated = $request->validated();
        unset($validated['step']);

        $draft = HireDraft::create([
            'step' => 2,
            'data' => $validated,
            'status' => 'draft'
        ]);

        return redirect()->route('hire.apply', ['d' => $draft->id])->with('message', 'Draft disimpan.');
    }

    /**
     * Update data per step
     */
    public function update(StoreProjectRequest $request, HireDraft $draft)
    {
        /* dd($request->all()); */

        $validated = $request->validated();
        $currentData = $draft->data;
        unset($validated['step']);

        if ($request->hasFile('files')) {
            // Hapus file lama jika user mengupload ulang
            if (isset($currentData['files'])) {
                Storage::disk('public')->delete($currentData['files']);
            }

            // Simpan file baru
            $path = $request->file('files')->store('drafts/attachments', 'public');
            $validated['files'] = $path;
        }

        $draft->update([
            'data' => array_merge($draft->data ?? [], $validated),
            'step' => (int) $request->input('next_step', $draft->step),
        ]);

        return redirect()->back()->with('message', 'Progres disimpan.');
    }

    /**
     * Submit akhir: Memindahkan data draft ke tabel utama (hire_requests)
     */
    public function submit(StoreProjectRequest $request, HireDraft $draft)
    {
        $validated = $request->validated();
        unset($validated['step']);

        if ($draft->step < 3) {
            return redirect()->back()->withErrors(['message' => 'Lengkapi semua step sebelum mengirim.']);
        }

        try {
            DB::transaction(function () use ($draft, $validated) {
                // Update status draft
                $draft->update([
                    'data' => array_merge($draft->data ?? [], $validated),
                    'status' => 'submitted'
                ]);

                $data = $draft->fresh()->data;

                $project = Project::create([
                    'service_id'    => $data['service_id'],
                    'name'          => $data['name'],
                    'email'         => $data['email'] ?? Auth::user()->email,
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
            return redirect()->route('dashboard')->with('message', 'Permintaan berhasil dikirim!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'message' => 'Terjadi kesalahan sistem.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }
}
