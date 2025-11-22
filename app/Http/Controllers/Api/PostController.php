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

    // Lấy danh sách bài viết (Newsfeed) - Public route, chỉ hiển thị bài đã duyệt
    public function index(Request $request)
    {
        $query = Post::with(['user', 'products', 'university', 'likes'])
                     ->where('status', 'approved'); // Chỉ hiển thị bài đã duyệt

        // Filter theo trường ĐH
        if ($request->has('university_id') && $request->university_id) {
            $query->where('university_id', $request->university_id);
        }

        // Filter theo danh mục (Category)
        if ($request->has('category_id') && $request->category_id) {
            $query->whereHas('products', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter theo khoảng giá (Price Range)
        if ($request->has('price_min') && is_numeric($request->price_min)) {
            $query->whereHas('products', function ($q) use ($request) {
                $q->where('price', '>=', $request->price_min);
            });
        }
        if ($request->has('price_max') && is_numeric($request->price_max)) {
            $query->whereHas('products', function ($q) use ($request) {
                $q->where('price', '<=', $request->price_max);
            });
        }

        // Tìm kiếm
        if ($request->has('keyword') && $request->keyword) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%$keyword%")
                  ->orWhere('content', 'like', "%$keyword%")
                  ->orWhereHas('products', function($pq) use ($keyword) {
                      $pq->where('name', 'like', "%$keyword%");
                  });
            });
        }

        // Sắp xếp (Sort)
        $sortBy = $request->input('sort_by', 'newest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_viewed':
                $query->orderBy('view_count', 'desc');
                break;
            case 'most_liked':
                $query->withCount('likes')->orderBy('likes_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return response()->json($query->paginate(10));
    }

    /**
     * Lấy tất cả bài viết cho admin (bao gồm cả pending)
     * GET /api/admin/posts
     */
    public function adminIndex(Request $request)
    {
        $query = Post::with(['user', 'products', 'university', 'violations.rule'])
                     ->orderBy('created_at', 'desc');

        // Filter theo status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
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

        // Nếu có filter status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Nếu có filter approved_only (cho profile)
        if ($request->has('approved_only') && $request->approved_only) {
            $query->where('status', 'approved');
        }

        return response()->json($query->paginate(10));
    }

    /**
     * Lấy thống kê bài viết của user hiện tại
     * GET /api/my-posts/statistics
     */
    public function myPostsStatistics()
    {
        $userId = Auth::id();
        
        $pending = Post::where('user_id', $userId)->where('status', 'pending')->count();
        $rejected = Post::where('user_id', $userId)->where('status', 'rejected')->count();
        $approved = Post::where('user_id', $userId)->where('status', 'approved')->count();

        return response()->json([
            'pending' => $pending,
            'rejected' => $rejected,
            'approved' => $approved
        ]);
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

        $data = $request->only(['title', 'content', 'university_id']);
        $data['status'] = 'pending'; // Luôn reset về pending khi update

        $post->update($data);

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
    public function reject(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'rule_ids' => 'nullable|array',
            'rule_ids.*' => 'exists:rules,id',
            'note' => 'nullable|string|max:1000'
        ]);

        $post->update(['status' => 'rejected']);

        $user = $post->user;
        $hasViolations = $request->has('rule_ids') && !empty($request->rule_ids);

        // Lưu các vi phạm nếu có
        if ($hasViolations) {
            foreach ($request->rule_ids as $ruleId) {
                \App\Models\PostViolation::create([
                    'post_id' => $post->id,
                    'rule_id' => $ruleId,
                    'admin_id' => Auth::id(),
                    'note' => $request->note
                ]);
            }

            // Kiểm tra và cập nhật trạng thái user
            $oneWeekAgo = now()->subWeek();
            
            // Đếm số bài viết bị từ chối do vi phạm trong vòng 1 tuần (bao gồm bài hiện tại)
            $violationsCount = Post::where('user_id', $user->id)
                ->where('status', 'rejected')
                ->whereHas('violations')
                ->where('created_at', '>=', $oneWeekAgo)
                ->count();

            if ($user->status === 'WARNING' && $violationsCount >= 2) {
                // Đã bị WARNING và có vi phạm mới trong 1 tuần (>= 2 bài bị từ chối) -> BANNED
                $user->update([
                    'status' => 'BANNED',
                    'is_active' => false
                ]);
            } elseif ($user->status === 'ACTIVE') {
                // Lần đầu vi phạm -> WARNING
                $user->update([
                    'status' => 'WARNING'
                ]);
            }
        }

        return response()->json([
            'message' => 'Đã từ chối bài viết' . ($hasViolations ? '. Người đăng đã bị cảnh báo.' : ''),
            'data' => $post->load(['user', 'products', 'university', 'violations.rule'])
        ]);
    }
}