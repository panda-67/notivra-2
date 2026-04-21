<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\HireDraft;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Mengarahkan user ke halaman login Google.
     */
    public function redirect(Request $request)
    {
        // Simpan ID Draft ke session sebelum pindah ke Google
        // Ini penting karena Google akan menghilangkan query string kita saat proses OAuth
        if ($request->has('d')) {
            session(['pending_draft_id' => $request->query('d')]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani data yang dikirim balik oleh Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Gagal login dengan Google.');
        }

        // 1. Cari user berdasarkan email Google
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // 2. Jika user tidak ada, buat user baru (Auto-Register)
            /** @var App\Models\User $user */
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'password'          => Hash::make(Str::random(24)), // Password random
            ]);

            $user->markEmailAsVerified();
        } else {
            // Jika user sudah ada tapi belum diverifikasi, tandai verifikasi sekarang
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }
        }

        // 3. Login User
        Auth::login($user);
        request()->session()->regenerate();

        // 4. Proses Klaim Draft (jika ada di session)
        if (session()->has('pending_draft_id')) {
            $draftId = session()->pull('pending_draft_id');

            $draft = HireDraft::where('id', $draftId)
                ->whereNull('user_id')
                ->first();

            if ($draft) {
                $draft->update(['user_id' => $user->id]);

                // Redirect kembali ke form aplikasi proyek
                return redirect()->route('hire.apply', ['d' => $draft->id])
                    ->with('message', 'Berhasil masuk dengan Google!');
            }
        }

        // 5. Jika tidak ada draft, redirect normal ke Dashboard
        return redirect()->intended(route('dashboard'));
    }
}
