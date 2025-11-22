<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Quản lý danh mục
     */
    public function categories()
    {
        return view('admin.categories');
    }

    /**
     * Quản lý trường đại học
     */
    public function universities()
    {
        return view('admin.universities');
    }

    /**
     * Quản lý người dùng
     */
    public function users()
    {
        return view('admin.users');
    }

    /**
     * Quản lý báo cáo
     */
    public function reports()
    {
        return view('admin.reports');
    }
}

