<?php

namespace App\Http\Controllers;

class GalleryController extends Controller
{
    public function index()
    {
        $path = resource_path('js/Data/outputs.json');
        $outputs = collect(json_decode(file_get_contents($path), true));

        return inertia('Gallery/Index', compact('outputs'));
    }

    public function show($slug)
    {
        $path = resource_path('js/Data/outputs.json');
        $outputs = collect(json_decode(file_get_contents($path), true));
        $output = $outputs->firstWhere('slug', $slug);

        return inertia('Gallery/Show', compact('output'));
    }
}
