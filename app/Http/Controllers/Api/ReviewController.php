<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index($userId)
    {
        $reviews = Review::where('reviewed_user_id', $userId)
            ->with(['reviewer:id,name,avatar', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($reviews);
    }

    public function store(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Chỉ người mua mới được đánh giá'], 403);
        }

        if ($order->status !== 'completed') {
            return response()->json(['message' => 'Chỉ có thể đánh giá đơn hàng đã hoàn thành'], 400);
        }

        $existingReview = Review::where('order_id', $orderId)->first();
        if ($existingReview) {
            return response()->json(['message' => 'Bạn đã đánh giá đơn hàng này rồi'], 400);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $review = Review::create([
            'order_id' => $orderId,
            'reviewer_id' => Auth::id(),
            'reviewed_user_id' => $order->seller_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json([
            'message' => 'Đánh giá thành công',
            'data' => $review->load('reviewer:id,name,avatar')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        if ($review->reviewer_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền sửa đánh giá này'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json([
            'message' => 'Cập nhật đánh giá thành công',
            'data' => $review->load('reviewer:id,name,avatar')
        ]);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        if ($review->reviewer_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền xóa đánh giá này'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Đã xóa đánh giá']);
    }
}

