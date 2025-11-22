<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ChatController;

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

// Posts (Xem công khai)
Route::get('/posts', [PostController::class, 'index']); // Newsfeed - có thể filter
Route::get('/posts/{id}', [PostController::class, 'show']); // Chi tiết bài viết

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
    Route::post('/posts', [PostController::class, 'store']); // Tạo bài viết
    Route::put('/posts/{id}', [PostController::class, 'update']); // Cập nhật bài viết
    Route::delete('/posts/{id}', [PostController::class, 'destroy']); // Xóa bài viết
    Route::post('/posts/{id}/like', [PostController::class, 'toggleLike']); // Like/Unlike

    // ========== COMMENTS ==========
    Route::get('/posts/{postId}/comments', [CommentController::class, 'index']); // Lấy comments
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store']); // Tạo comment
    Route::put('/comments/{id}', [CommentController::class, 'update']); // Cập nhật comment
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']); // Xóa comment

    // ========== CART (Giỏ hàng) ==========
    Route::get('/cart', [CartController::class, 'index']); // Lấy giỏ hàng
    Route::post('/cart', [CartController::class, 'store']); // Thêm vào giỏ
    Route::delete('/cart/{id}', [CartController::class, 'destroy']); // Xóa khỏi giỏ

    // ========== ORDERS (Đơn hàng) ==========
    Route::post('/checkout', [OrderController::class, 'store']); // Checkout từ giỏ hàng
    Route::get('/my-orders', [OrderController::class, 'myOrders']); // Đơn mình mua
    Route::get('/sales-orders', [OrderController::class, 'salesOrders']); // Đơn mình bán
    Route::get('/orders/{id}', [OrderController::class, 'show']); // Chi tiết đơn hàng
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']); // Cập nhật trạng thái (người bán)

    // ========== REVIEWS (Đánh giá) ==========
    Route::get('/users/{userId}/reviews', [ReviewController::class, 'index']); // Lấy đánh giá của user
    Route::post('/orders/{orderId}/reviews', [ReviewController::class, 'store']); // Tạo đánh giá
    Route::put('/reviews/{id}', [ReviewController::class, 'update']); // Cập nhật đánh giá
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']); // Xóa đánh giá

    // ========== REPORTS (Báo cáo) ==========
    Route::post('/posts/{postId}/reports', [ReportController::class, 'store']); // Báo cáo bài viết

    // ========== CHAT (Tin nhắn) ==========
    Route::get('/conversations', [ChatController::class, 'index']); // Lấy danh sách hội thoại
    Route::post('/messages', [ChatController::class, 'sendMessage']); // Gửi tin nhắn
    Route::get('/conversations/{id}/messages', [ChatController::class, 'getMessages']); // Lấy tin nhắn của hội thoại

    // ========== ADMIN ROUTES (Yêu cầu role ADMIN) ==========
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        
        // Universities Management
        Route::post('/universities', [UniversityController::class, 'store']);
        Route::put('/universities/{id}', [UniversityController::class, 'update']);
        Route::delete('/universities/{id}', [UniversityController::class, 'destroy']);

        // Categories Management
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Posts Moderation
        Route::put('/posts/{id}/approve', [PostController::class, 'approve']); // Duyệt bài
        Route::put('/posts/{id}/reject', [PostController::class, 'reject']); // Từ chối bài

        // Reports Management
        Route::get('/reports', [ReportController::class, 'index']); // Danh sách báo cáo
        Route::put('/reports/{id}', [ReportController::class, 'update']); // Cập nhật trạng thái báo cáo
        Route::delete('/reports/{id}', [ReportController::class, 'destroy']); // Xóa báo cáo

        // Users Management
        Route::get('/users', [AuthController::class, 'listUsers']); // Danh sách users
        Route::put('/users/{id}/status', [AuthController::class, 'updateUserStatus']); // Cập nhật trạng thái user
        Route::put('/users/{id}/ban', [AuthController::class, 'banUser']); // Khóa tài khoản
    });
});