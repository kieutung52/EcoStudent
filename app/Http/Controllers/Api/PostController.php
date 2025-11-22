<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\ImageUploadTrait;

class PostController extends Controller
{
    use ImageUploadTrait;

    // Lấy danh sách bài viết (Newsfeed)
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

        return response()->json($query->paginate(10));
    }

    /**
     * Lấy danh sách bài viết của user hiện tại
     * GET /api/my-posts
     */
    public function myPosts(Request $request)
    {
        $query = Post::with(['products', 'university', 'likes'])
                     ->where('user_id', Auth::id())
                     ->orderBy('created_at', 'desc');

        return response()->json($query->paginate(10));
    }

    // Tạo bài viết mới (Kèm sản phẩm + Ảnh)
    public function store(Request $request)
    {
        // Validate dữ liệu phức tạp (Mảng products)
        $request->validate([
            'title' => 'required|string|max:255',
            'university_id' => 'required|exists:universities,id',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate ảnh
        ]);

        DB::beginTransaction(); // Bắt đầu Transaction
        try {
            // 1. Tạo Post
            $post = Post::create([
                'user_id' => Auth::id(),
                'university_id' => $request->university_id,
                'title' => $request->title,
                'content' => $request->content,
                'status' => 'pending' // Chờ duyệt
            ]);

            // 2. Tạo Products loop
            foreach ($request->products as $index => $prodData) {
                $imagePath = null;
                
                // Xử lý upload ảnh từ mảng request
                // Lưu ý: Laravel xử lý file array input hơi khác
                if ($request->hasFile("products.$index.image")) {
                    $file = $request->file("products.$index.image");
                    $imagePath = $this->uploadImage($file, 'products');
                }

                Product::create([
                    'post_id' => $post->id,
                    'category_id' => $prodData['category_id'] ?? null,
                    'name' => $prodData['name'],
                    'price' => $prodData['price'],
                    'quantity' => $prodData['quantity'],
                    'description' => $prodData['description'] ?? '',
                    'image' => $imagePath
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Đăng bài thành công', 'data' => $post->load('products')], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    // Chi tiết bài viết
    public function show($id)
    {
        $post = Post::with(['user', 'products', 'comments.user', 'university'])->findOrFail($id);
        // Tăng view
        $post->increment('view_count');
        return response()->json($post);
    }

    // Xóa bài viết (Chỉ owner hoặc admin)
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Check quyền
        if (Auth::id() !== $post->user_id && Auth::user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Không có quyền xóa bài này'], 403);
        }

        // Logic xóa ảnh sản phẩm đã được xử lý trong Model Product boot() (nhờ cascade delete hoặc loop xóa thủ công nếu soft delete không trigger)
        // Vì Post dùng SoftDeletes, nên ảnh chưa xóa ngay. 
        // Nếu muốn xóa vĩnh viễn (Force Delete) để dọn rác:
        
        foreach($post->products as $product) {
            $this->deleteImage($product->image); // Xóa file vật lý
        }
        
        $post->forceDelete(); // Xóa cả DB

        return response()->json(['message' => 'Đã xóa bài viết và hình ảnh liên quan']);
    }

    /**
     * Cập nhật bài viết (Chỉ owner)
     * PUT /api/posts/{id}
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Check quyền
        if (Auth::id() !== $post->user_id) {
            return response()->json(['message' => 'Không có quyền sửa bài này'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'university_id' => 'sometimes|exists:universities,id',
            'status' => 'sometimes|in:pending,approved,rejected,hidden,sold_out'
        ]);

        $post->update($request->only(['title', 'content', 'university_id', 'status']));

        return response()->json([
            'message' => 'Cập nhật bài viết thành công',
            'data' => $post->load(['user', 'products', 'university'])
        ]);
    }

    /**
     * Like/Unlike bài viết
     * POST /api/posts/{id}/like
     */
    public function toggleLike($id)
    {
        $post = Post::findOrFail($id);
        $userId = Auth::id();

        $like = \App\Models\PostLike::where('user_id', $userId)
            ->where('post_id', $id)
            ->first();

        if ($like) {
            $like->delete();
            $message = 'Đã bỏ like';
        } else {
            \App\Models\PostLike::create([
                'user_id' => $userId,
                'post_id' => $id
            ]);
            $message = 'Đã like bài viết';
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Duyệt bài viết (Admin only)
     * PUT /api/admin/posts/{id}/approve
     */
    public function approve($id)
    {
        $post = Post::findOrFail($id);
        $post->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Đã duyệt bài viết',
            'data' => $post->load(['user', 'products', 'university'])
        ]);
    }

    /**
     * Từ chối bài viết (Admin only)
     * PUT /api/admin/posts/{id}/reject
     */
    public function reject($id)
    {
        $post = Post::findOrFail($id);
        $post->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Đã từ chối bài viết',
            'data' => $post->load(['user', 'products', 'university'])
        ]);
    }
}