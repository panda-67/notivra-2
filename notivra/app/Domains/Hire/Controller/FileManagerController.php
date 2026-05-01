<?php

namespace App\Domains\Hire\Controller;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends Controller
{
    public function show(Project $project)
    {
        $data = $project->load(['files']);

        return inertia('Hire/FileManager', compact('data'));
    }

    public function store(Project $project, Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,docx,xlsx,png,jpg|max:10240', // Maks 10MB
        ]);

        $file = $request->file('files');
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("research_draft/$project->id", $fileName, 'local');

        File::create([
            'project_id'    => $project->id,
            'user_id'       => Auth::id(),
            'name'          => $request->file('file')->getClientOriginalName(),
            'path'          => $path,
            'mime_type'     => $request->file('file')->getMimeType(),
            'size'          => $request->file('file')->getSize(),
        ]);

        return back()->with('success', 'File berhasil diunggah.');
    }

    public function destroy(File $file)
    {
        if (Storage::disk('local')->exists($file->path)) {
            Storage::disk('local')->delete($file->path);
        }

        $file->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }
}
