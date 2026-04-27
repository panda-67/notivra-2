<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:subscribers,email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
        ]);

        try {
            Subscriber::create($validated);

            Log::info("User berlangganan: " . $request->email);

            return response()->json(['success' => 'Terima kasih telah berlangganan!'], 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => ['email' => ['Gagal menyimpan data.']]], 500);
        }
    }
}
