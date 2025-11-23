<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with(['user:id,name', 'post:id,title']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($reports);
    }

    public function store(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);

        if ($post->user_id === Auth::id()) {
            return response()->json(['message' => 'Không thể báo cáo bài viết của chính mình'], 400);
        }

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

    public function destroy($id)
    {
        Report::destroy($id);

        return response()->json(['message' => 'Đã xóa báo cáo']);
    }

    public function ban(Request $request, $id)
    {
        $report = Report::with('post.user')->findOrFail($id);
        $post = $report->post;

        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        $request->validate([
            'rule_ids' => 'required|array',
            'rule_ids.*' => 'exists:rules,id',
            'note' => 'nullable|string|max:1000'
        ]);

        // 1. Reject Post
        $post->update(['status' => 'rejected']);

        // 2. Create Violations
        foreach ($request->rule_ids as $ruleId) {
            \App\Models\PostViolation::create([
                'post_id' => $post->id,
                'rule_id' => $ruleId,
                'admin_id' => Auth::id(),
                'note' => $request->note
            ]);
        }

        // 3. Punish User
        $user = $post->user;
        $oneWeekAgo = now()->subWeek();
        
        $violationsCount = Post::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->whereHas('violations')
            ->where('created_at', '>=', $oneWeekAgo)
            ->count();

        if ($user->status === 'WARNING' && $violationsCount >= 2) {
            $user->update([
                'status' => 'BANNED',
                'is_active' => false
            ]);
        } elseif ($user->status === 'ACTIVE') {
            $user->update([
                'status' => 'WARNING'
            ]);
        }

        // 4. Resolve Report
        $report->update(['status' => 'resolved']);

        return response()->json([
            'message' => 'Đã cấm bài viết và xử lý vi phạm người dùng',
            'data' => $report->load(['user', 'post'])
        ]);
    }
}

