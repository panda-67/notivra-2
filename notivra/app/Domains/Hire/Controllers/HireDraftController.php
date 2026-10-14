<?php

namespace App\Domains\Hire\Controllers;

use App\Domains\Hire\Repositories\ProjectRepository;
use App\Domains\Hire\Requests\ProjectRequest;
use App\Http\Controllers\Controller;
use App\Mail\ProjectSubmittedMail;
use App\Models\HireDraft;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class HireDraftController extends Controller
{
    public function __construct(
        protected ProjectRepository $projectRepo,
    ) {}

    /**
     * Menampilkan form (Halaman utama form multi-step)
     */
    public function create(Request $request)
    {
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
        abort_unless(
            (string) $draft->user_id === (string) Auth::id(),
            403
        );

        $validated = $request->validated();

        $nextStep = (int) ($validated['next_step'] ?? $draft->step);
        unset($validated['step'], $validated['next_step']);

        $currentData = $draft->data ?? [];
        $oldFilePath = $currentData['files'] ?? null;
        $newFilePath = null;

        if ($request->hasFile('files')) {
            $file = $request->file('files');

            $fileName = $draft->id
                . '_'
                . Str::uuid()
                . '.'
                . $file->getClientOriginalExtension();

            $newFilePath = $file->storeAs('drafts/attachments', $fileName, 'local');

            if ($newFilePath === false) {
                return redirect()->back()->withErrors([
                    'files' => 'File gagal disimpan. Silakan coba kembali.',
                ]);
            }

            $validated['files'] = $newFilePath;
        }

        try {
            $draft->update([
                'data' => array_merge($currentData, $validated),
                'step' => $nextStep,
            ]);
        } catch (\Throwable $e) {
            if ($newFilePath !== null) {
                Storage::disk('local')->delete($newFilePath);
            }

            throw $e;
        }

        if ($newFilePath !== null && $oldFilePath !== null && $oldFilePath !== $newFilePath) {
            Storage::disk('local')->delete($oldFilePath);
        }

        return redirect()->back()->with('success', 'Progres disimpan.');
    }

    /**
     * Submit akhir: Memindahkan data draft ke tabel utama (hire_requests)
     */
    #[Middleware('auth')]
    public function submit(ProjectRequest $request, HireDraft $draft)
    {
        abort_unless(
            (string) $draft->user_id === (string) Auth::id(),
            403
        );

        $validated = $request->validated();
        unset($validated['step']);

        if ($draft->step < 3) {
            return redirect()->back()->withErrors(['message' => 'Lengkapi semua step sebelum mengirim.']);
        }

        $filePath = $draft->data['files'] ?? null;

        if ($filePath !== null && ! Storage::disk('local')->exists($filePath)) {
            return redirect()->back()->withErrors([
                'files' => 'Lampiran tidak ditemukan. Silakan unggah kembali.',
            ]);
        }

        try {
            /**@var \App\Models\Project $project */
            $project = $this->projectRepo->submitProject($draft, $validated);

            if ($project === null) {
                return redirect()->route('dashboard')->with('info', 'Draft ini sudah pernah dikirim.');
            }

            try {
                Mail::to($project->email)->queue(new ProjectSubmittedMail($project));
            } catch (\Throwable $mailException) {
                report($mailException);

                return redirect()->route('dashboard')->with(
                    'success',
                    'Permintaan berhasil dikirim. Notifikasi email belum dapat dikirim.'
                );
            }

            return redirect()->route('dashboard')->with('success', 'Permintaan berhasil dikirim!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Terjadi kesalahan sistem.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }
}
