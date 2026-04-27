<?php

namespace App\Http\Controllers;

use App\Content\Repository\BlogRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(
        protected BlogRepository $blogRepository
    ) {}

    /**
     * Menampilkan daftar blog
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $blogs = $this->blogRepository->paginate(6, $search);

        return view('blog.index', compact('blogs'));
    }

    /**
     * Menampilkan isi blog berdasarkan slug
     */
    public function show($slug): View
    {
        $post = $this->blogRepository->find($slug);

        $related = $this->blogRepository->getRelated(
            $slug,
            $post['meta']['tags'] ?? []
        );
        return view('blog.show', compact('post', 'related'));
    }
}
