<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        // 2. Bảng Posts (Bài đăng)
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('university_id')->nullable()->constrained('universities')->onDelete('set null');
            $table->string('title');
            $table->text('content')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'hidden', 'sold_out'])->default('pending');
            $table->integer('view_count')->default(0);
            $table->softDeletes(); // Hỗ trợ thùng rác
            $table->timestamps();
        });

        // 3. Bảng Products (Sản phẩm trong bài đăng)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->integer('quantity')->default(1);
            $table->string('image')->nullable(); // Lưu đường dẫn ảnh
            $table->text('description')->nullable();
            $table->boolean('is_sold')->default(false);
            $table->timestamps();
        });

        // 4. Bảng Orders (Đơn hàng)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Người mua
            $table->foreignId('seller_id')->constrained('users'); // Người bán
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method')->default('COD');
            $table->string('shipping_address');
            $table->string('phone_number');
            $table->string('status')->default('pending'); // pending, confirmed, shipping, completed, cancelled
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 5. Bảng Order Items (Chi tiết đơn hàng)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('product_name'); // Snapshot tên
            $table->decimal('product_price', 12, 2); // Snapshot giá
            $table->integer('quantity');
            $table->timestamps();
        });

        // 6. Bảng Comments (Bình luận)
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade'); // Reply
            $table->timestamps();
        });

        // 7. Bảng Post Likes
        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_likes');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
    }
};