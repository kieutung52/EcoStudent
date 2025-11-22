<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Hiển thị trang tạo bài viết
     */
    public function create()
    {
        $universities = \App\Models\University::all();
        $categories = \App\Models\Category::all();
        return view('posts.create', compact('universities', 'categories'));
    }
}

