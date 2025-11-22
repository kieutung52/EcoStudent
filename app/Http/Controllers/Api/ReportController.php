<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Lấy danh sách báo cáo (Admin only)
     * GET /api/admin/reports
     */
    public function index(Request $request)
    {
        $query = Report::with(['user:id,name', 'post:id,title']);

        // Filter theo status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($reports);
    }

    /**
     * Tạo báo cáo mới
     * POST /api/posts/{postId}/reports
     */
    public function store(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);

        // Không được báo cáo bài của chính mình
        if ($post->user_id === Auth::id()) {
            return response()->json(['message' => 'Không thể báo cáo bài viết của chính mình'], 400);
        }

        // Kiểm tra đã báo cáo chưa
        $existingReport = Report::where('user_id', Auth::id())
            ->where('post_id', $postId)
            ->first();

        if ($existingReport) {
            return response()->json(['message' => 'Bạn đã báo cáo bài viết này rồi'], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $report = Report::create([
            'user_id' => Auth::id(),
            'post_id' => $postId,
            'reason' => $request->reason,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Báo cáo thành công. Chúng tôi sẽ xem xét và xử lý sớm nhất.',
            'data' => $report
        ], 201);
    }

    /**
     * Cập nhật trạng thái báo cáo (Admin only)
     * PUT /api/admin/reports/{id}
     */
    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,resolved'
        ]);

        $report->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Cập nhật trạng thái báo cáo thành công',
            'data' => $report->load(['user:id,name', 'post:id,title'])
        ]);
    }

    /**
     * Xóa báo cáo (Admin only)
     * DELETE /api/admin/reports/{id}
     */
    public function destroy($id)
    {
        Report::destroy($id);

        return response()->json(['message' => 'Đã xóa báo cáo']);
    }
}

