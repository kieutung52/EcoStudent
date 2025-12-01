<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\University;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Trang chủ - Newsfeed
     */
    public function index(Request $request)
    {
        $query = Post::with(['user', 'products', 'university', 'likes'])
            ->where('status', '!=', 'hidden')
            ->orderBy('created_at', 'desc');

        // Filter theo trường ĐH
        if ($request->has('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        // Tìm kiếm
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where('title', 'like', "%$keyword%");
        }

        $posts = $query->paginate(10);
        $universities = University::all();
        $categories = Category::all();

        return view('home', compact('posts', 'universities', 'categories'));
    }

    /**
     * Chi tiết bài viết
     */
    public function showPost($id)
    {
        $post = Post::with(['user', 'products', 'university', 'likes'])
            ->findOrFail($id);
        
        // Tăng view
        $post->increment('view_count');

        return view('post-detail', compact('post'));
    }

    public function showSellerProfile($id)
    {
        $seller = \App\Models\User::with('university')->findOrFail($id);
        
        $posts = Post::with(['user', 'products', 'university', 'likes'])
            ->where('user_id', $id)
            ->where('status', 'APPROVED')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('profile.seller', compact('seller', 'posts'));
    }
}

