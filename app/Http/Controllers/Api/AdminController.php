<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Report;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalPosts = Post::count();
        $pendingReports = Report::where('status', 'pending')->count();
        $totalOrders = Order::count();

        $recentPosts = Post::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'total_users' => $totalUsers,
            'total_posts' => $totalPosts,
            'pending_reports' => $pendingReports,
            'total_orders' => $totalOrders,
            'recent_posts' => $recentPosts
        ]);
    }
}
