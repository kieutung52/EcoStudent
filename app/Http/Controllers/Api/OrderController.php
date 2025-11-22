<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // Đặt hàng (Checkout)
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array', // [{product_id: 1, quantity: 2}, ...]
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        // Group items by Seller (User ID của người đăng bài chứa sản phẩm)
        $itemsBySeller = [];
        
        foreach ($request->items as $item) {
            $product = Product::with('post')->find($item['product_id']);
            
            // Check tồn kho
            if ($product->quantity < $item['quantity']) {
                return response()->json(['message' => "Sản phẩm {$product->name} không đủ số lượng"], 400);
            }

            $sellerId = $product->post->user_id;
            if ($sellerId == Auth::id()) {
                return response()->json(['message' => "Không thể tự mua hàng của mình"], 400);
            }

            $itemsBySeller[$sellerId][] = [
                'product' => $product,
                'quantity' => $item['quantity']
            ];
        }

        DB::beginTransaction();
        try {
            $createdOrders = [];

            foreach ($itemsBySeller as $sellerId => $items) {
                $totalAmount = 0;
                
                // Tính tổng tiền
                foreach ($items as $data) {
                    $totalAmount += $data['product']->price * $data['quantity'];
                }

                // Tạo Order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'seller_id' => $sellerId,
                    'total_amount' => $totalAmount,
                    'payment_method' => 'COD', // Mặc định
                    'shipping_address' => $request->shipping_address,
                    'phone_number' => $request->phone_number,
                    'status' => 'pending'
                ]);

                // Tạo Order Items và trừ tồn kho
                foreach ($items as $data) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $data['product']->id,
                        'product_name' => $data['product']->name,
                        'product_price' => $data['product']->price,
                        'quantity' => $data['quantity']
                    ]);

                    // --- ĐOẠN SỬA LỖI ---
                    // Thay vì gọi decrement() trực tiếp (gây lỗi protected method),
                    // ta trừ thủ công và save(). Cách này an toàn hơn và chuẩn logic.
                    $productToUpdate = $data['product'];
                    $productToUpdate->quantity -= $data['quantity'];
                    $productToUpdate->save();
                    // --------------------
                }
                
                $createdOrders[] = $order->id;
            }

            DB::commit();
            return response()->json(['message' => 'Đặt hàng thành công', 'orders' => $createdOrders], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi xử lý đơn hàng: ' . $e->getMessage()], 500);
        }
    }

    // Lấy danh sách đơn hàng CỦA TÔI (Tôi mua)
    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items', 'seller:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($orders);
    }

    // Lấy danh sách đơn hàng KHÁCH ĐẶT (Tôi bán)
    public function salesOrders()
    {
        $orders = Order::where('seller_id', Auth::id())
            ->with(['items', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($orders);
    }

    /**
     * Cập nhật trạng thái đơn hàng (Người bán)
     * PUT /api/orders/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Chỉ người bán mới được cập nhật trạng thái
        if ($order->seller_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền cập nhật đơn hàng này'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,shipping,completed,cancelled'
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
            'data' => $order->load(['items', 'user:id,name'])
        ]);
    }

    /**
     * Chi tiết đơn hàng
     * GET /api/orders/{id}
     */
    public function show($id)
    {
        $order = Order::with(['items', 'user:id,name,avatar', 'seller:id,name,avatar'])
            ->findOrFail($id);

        // Chỉ người mua hoặc người bán mới xem được hoặc Admin
        if ($order->user_id !== Auth::id() && $order->seller_id !== Auth::id() && Auth::user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Không có quyền xem đơn hàng này'], 403);
        }

        return response()->json($order);
    }

    /**
     * Người mua xác nhận đã nhận hàng
     * POST /api/orders/{id}/confirm-received
     */
    public function confirmReceived($id)
    {
        $order = Order::findOrFail($id);

        // Chỉ người mua mới được xác nhận đã nhận hàng
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền xác nhận đơn hàng này'], 403);
        }

        // Chỉ có thể xác nhận khi đơn hàng đang ở trạng thái shipping
        if ($order->status !== 'shipping') {
            return response()->json([
                'message' => 'Chỉ có thể xác nhận đã nhận hàng khi đơn hàng đang được giao'
            ], 400);
        }

        $order->update(['status' => 'completed']);

        return response()->json([
            'message' => 'Đã xác nhận nhận hàng thành công',
            'data' => $order->load(['items', 'user:id,name,avatar', 'seller:id,name,avatar'])
        ]);
    }
}