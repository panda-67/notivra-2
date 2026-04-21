<?php

namespace App\Http\Controllers;

use App\Content\Repository\BlogRepository;

class BlogController extends Controller
{
    public function __construct(
        protected BlogRepository $blogRepository
    ) {}

    /**
     * Menampilkan daftar blog
     */
    public function index()
    {
        $blogs = $this->blogRepository->all();

        return view('blog.index', compact('blogs'));
    }

    /**
     * Menampilkan isi blog berdasarkan slug
     */
    public function show($slug)
    {
        $post = $this->blogRepository->find($slug);

        return view('blog.show', $post);
    }
}
