<?php

namespace App\Domains\Hire\Controller;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileManagerController extends Controller
{
    public function show()
    {
        $files = File::where('user_id', Auth::id())->get();
        return inertia('Hire/FileManager', compact('files'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,docx,xlsx,png,jpg|max:10240', // Maks 10MB
        ]);

        $path = $request->file('file')->store('research_drafts', 'local');

        $file = File::create([
            'user_id' => Auth::id(),
            'name' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
        ]);

        return back()->with('message', 'File berhasil diunggah.');
    }
}
