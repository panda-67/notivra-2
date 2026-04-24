<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\HireDraft;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show(Request $request)
    {
        $d = $request->query('d');

        $draft = null;
        $userExists = false;

        if ($d) {
            $draft = HireDraft::where('id', $d)
                ->whereNull('user_id')
                ->first();

            if ($draft) {
                $email = $draft->data['email'] ?? null;
                $userExists = User::where('email', $email)->exists();
            } else {
                $d = null;
            }
        }

        return inertia('Auth/Login', [
            'draft'          => $d,
            'isValid'        => !!$draft,
            'prefilledEmail' => $draft->data['email'] ?? null,
            'username'       => $draft->data['name'] ?? null,
            'isUserExist'    => $userExists,
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi dasar
        // Jika ada draft, email tidak wajib diisi manual (karena ada di draft).
        // Jika tidak ada draft, email wajib diisi.
        $request->validate([
            'email'    => [Rule::requiredIf(!$request->filled('draft')), 'nullable', 'email'],
            'password' => ['required', 'min:8'],
            'draft'    => ['nullable', 'exists:hire_drafts,id'],
        ]);

        $draftId = $request->input('draft');
        $draft = $draftId ? HireDraft::find($draftId) : null;

        // 2. Tentukan email yang akan digunakan
        // Prioritas: Email dari draft, jika tidak ada baru dari input manual
        $email = $draft ? ($draft->data['email'] ?? null) : $request->input('email');

        if (!$email) {
            throw ValidationException::withMessages(['email' => 'Alamat email diperlukan.']);
        }

        // 3. Cari User
        $user = User::where('email', $email)->first();

        if ($user) {
            // --- PROSES LOGIN (User Lama) ---
            if (!Auth::attempt(['email' => $email, 'password' => $request->password], $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'password' => 'Password salah untuk email ini.',
                ]);
            }
        } else {
            // --- PROSES REGISTER OTOMATIS (Hanya jika ada Draft) ---
            // Jika user tidak ada dan tidak ada draft, berarti ini login biasa yang gagal
            if (!$draft) {
                throw ValidationException::withMessages([
                    'email' => 'Akun tidak ditemukan. Silakan register atau buat pesanan terlebih dahulu.',
                ]);
            }

            // Buat user baru dari data draft
            $user = User::create([
                'name'     => $draft->data['name'] ?? 'Klien Notivra',
                'email'    => $email,
                'password' => Hash::make($request->password),
            ]);

            Auth::login($user);
        }

        $request->session()->regenerate();

        // 4. Klaim Draft & Redirect
        if ($draft && is_null($draft->user_id)) {
            $draft->update(['user_id' => $user->id]);

            return redirect()->route('hire.apply', ['d' => $draft->id])
                ->with('success', 'Login berhasil, silakan lanjutkan proyek Anda.');
        }

        return redirect()->intended(route('dashboard'));
    }

    #[Middleware('auth')]
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return to_route('dashboard');
    }
}
