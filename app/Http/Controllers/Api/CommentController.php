<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Lấy danh sách comment của một bài đăng
     * GET /api/posts/{postId}/comments
     */
    public function index($postId)
    {
        $comments = Comment::where('post_id', $postId)
            ->whereNull('parent_id') // Chỉ lấy comment gốc
            ->with(['user:id,name,avatar', 'replies.user:id,name,avatar'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    /**
     * Tạo comment mới
     * POST /api/posts/{postId}/comments
     */
    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id' // Để reply comment
        ]);

        $post = Post::findOrFail($postId);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $postId,
            'content' => $request->content,
            'parent_id' => $request->parent_id
        ]);

        return response()->json([
            'message' => 'Bình luận thành công',
            'data' => $comment->load('user:id,name,avatar')
        ], 201);
    }

    /**
     * Cập nhật comment (chỉ owner)
     * PUT /api/comments/{id}
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        // Kiểm tra quyền sở hữu
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền sửa bình luận này'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment->update(['content' => $request->content]);

        return response()->json([
            'message' => 'Cập nhật bình luận thành công',
            'data' => $comment->load('user:id,name,avatar')
        ]);
    }

    /**
     * Xóa comment (chỉ owner hoặc admin)
     * DELETE /api/comments/{id}
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Kiểm tra quyền
        if ($comment->user_id !== Auth::id() && Auth::user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Không có quyền xóa bình luận này'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Đã xóa bình luận']);
    }
}

