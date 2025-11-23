<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;

use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Tất cả routes sử dụng JWT authentication thay vì Sanctum
| Middleware 'auth:api' sẽ verify JWT token từ header Authorization: Bearer {token}
|
*/

// ==================== PUBLIC ROUTES (Không cần đăng nhập) ====================

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Master Data (Danh mục công khai)
Route::get('/universities', [UniversityController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/rules', [\App\Http\Controllers\Api\RuleController::class, 'index']); // Luật lệ công khai

// Posts (Xem công khai)
Route::get('/posts', [PostController::class, 'index']); // Newsfeed - có thể filter
Route::get('/posts/{id}', [PostController::class, 'show']); // Chi tiết bài viết
Route::get('/users/{userId}/reviews', [ReviewController::class, 'index']); // Lấy đánh giá của user (Public)

// ==================== PROTECTED ROUTES (Yêu cầu JWT authentication) ====================
Route::middleware('auth:api')->group(function () {

    // ========== AUTHENTICATION & PROFILE ==========
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']); // Refresh JWT token
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // ========== POSTS MANAGEMENT ==========
    Route::get('/my-posts', [PostController::class, 'myPosts']); // Lấy bài viết của user hiện tại
    Route::get('/my-posts/statistics', [PostController::class, 'myPostsStatistics']); // Thống kê bài viết
    Route::post('/posts', [PostController::class, 'store']); // Tạo bài viết
    Route::put('/posts/{id}', [PostController::class, 'update']); // Cập nhật bài viết
    Route::delete('/posts/{id}', [PostController::class, 'destroy']); // Xóa bài viết
    Route::post('/posts/{id}/like', [PostController::class, 'toggleLike']); // Like/Unlike



    // ========== CART (Giỏ hàng) ==========
    Route::get('/cart', [CartController::class, 'index']); // Lấy giỏ hàng
    Route::post('/cart', [CartController::class, 'store']); // Thêm vào giỏ
    Route::put('/cart/{id}', [CartController::class, 'update']); // Cập nhật số lượng
    Route::delete('/cart/{id}', [CartController::class, 'destroy']); // Xóa khỏi giỏ

    // ========== ORDERS (Đơn hàng) ==========
    Route::post('/checkout', [OrderController::class, 'store']); // Checkout từ giỏ hàng
    Route::get('/my-orders', [OrderController::class, 'myOrders']); // Đơn mình mua
    Route::get('/sales-orders', [OrderController::class, 'salesOrders']); // Đơn mình bán
    Route::get('/orders/{id}', [OrderController::class, 'show']); // Chi tiết đơn hàng
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']); // Cập nhật trạng thái (người bán)
    Route::post('/orders/{id}/confirm-received', [OrderController::class, 'confirmReceived']); // Xác nhận đã nhận hàng (người mua)

    // ========== REVIEWS (Đánh giá) ==========

    Route::post('/orders/{orderId}/reviews', [ReviewController::class, 'store']); // Tạo đánh giá
    Route::put('/reviews/{id}', [ReviewController::class, 'update']); // Cập nhật đánh giá
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']); // Xóa đánh giá

    // ========== REPORTS (Báo cáo) ==========
    Route::post('/posts/{postId}/reports', [ReportController::class, 'store']); // Báo cáo bài viết

    // ========== REPORTS (Báo cáo) ==========
    Route::post('/posts/{postId}/reports', [ReportController::class, 'store']); // Báo cáo bài viết

    // ========== ADMIN ROUTES (Yêu cầu role ADMIN) ==========
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Api\AdminController::class, 'dashboard']);
        
        // Universities Management
        Route::post('/universities', [UniversityController::class, 'store']);
        Route::put('/universities/{id}', [UniversityController::class, 'update']);
        Route::delete('/universities/{id}', [UniversityController::class, 'destroy']);

        // Categories Management
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Posts Moderation
        Route::get('/posts', [PostController::class, 'adminIndex']); // Danh sách bài viết (admin)
        Route::put('/posts/{id}/approve', [PostController::class, 'approve']); // Duyệt bài
        Route::put('/posts/{id}/reject', [PostController::class, 'reject']); // Từ chối bài

        // Rules Management
        Route::get('/rules', [\App\Http\Controllers\Api\RuleController::class, 'indexAdmin']); // Danh sách luật lệ (admin)
        Route::post('/rules', [\App\Http\Controllers\Api\RuleController::class, 'store']); // Tạo luật lệ
        Route::put('/rules/{id}', [\App\Http\Controllers\Api\RuleController::class, 'update']); // Cập nhật luật lệ
        Route::delete('/rules/{id}', [\App\Http\Controllers\Api\RuleController::class, 'destroy']); // Xóa luật lệ

        // Reports Management
        Route::get('/reports', [ReportController::class, 'index']); // Danh sách báo cáo
        Route::put('/reports/{id}', [ReportController::class, 'update']); // Cập nhật trạng thái báo cáo
        Route::post('/reports/{id}/ban', [ReportController::class, 'ban']); // Ban bài viết từ báo cáo
        Route::delete('/reports/{id}', [ReportController::class, 'destroy']); // Xóa báo cáo

        // Users Management
        Route::get('/users', [AuthController::class, 'listUsers']); // Danh sách users
        Route::put('/users/{id}/status', [AuthController::class, 'updateUserStatus']); // Cập nhật trạng thái user
        Route::put('/users/{id}/ban', [AuthController::class, 'banUser']); // Khóa tài khoản
    });
});