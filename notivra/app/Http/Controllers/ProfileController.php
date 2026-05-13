<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return inertia('Profile', [
            'status' => session('status'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,' . $request->user()->id // Abaikan ID user saat ini
            ],
        ]);

        // 2. Isi data yang divalidasi
        $request->user()->fill($request->only('name', 'email'));

        // 3. Jika email berubah, reset verifikasi email (opsional)
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // 4. Simpan ke database
        $request->user()->save();

        // 5. Redirect kembali dengan flash message
        return to_route('profile.edit')->with('success', 'Profile diperbarui.');
    }
}
