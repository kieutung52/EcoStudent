<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PostController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// Protected routes (require authentication via JWT in localStorage)
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('orders.my');
Route::get('/sales-orders', [OrderController::class, 'salesOrders'])->name('orders.sales');
Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');

// Posts routes - IMPORTANT: /posts/create must come before /posts/{id}
Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
Route::get('/posts/{id}', [HomeController::class, 'showPost'])->name('post.show');

// Admin routes
Route::get('/admin', [\App\Http\Controllers\Web\AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/admin/categories', [\App\Http\Controllers\Web\AdminController::class, 'categories'])->name('admin.categories');
Route::get('/admin/universities', [\App\Http\Controllers\Web\AdminController::class, 'universities'])->name('admin.universities');
Route::get('/admin/users', [\App\Http\Controllers\Web\AdminController::class, 'users'])->name('admin.users');
Route::get('/admin/reports', [\App\Http\Controllers\Web\AdminController::class, 'reports'])->name('admin.reports');
