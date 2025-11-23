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
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        $itemsBySeller = [];
        
        foreach ($request->items as $item) {
            $product = Product::with('post')->find($item['product_id']);
            
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
                
                foreach ($items as $data) {
                    $totalAmount += $data['product']->price * $data['quantity'];
                }

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'seller_id' => $sellerId,
                    'total_amount' => $totalAmount,
                    'payment_method' => 'COD', // Mặc định
                    'shipping_address' => $request->shipping_address,
                    'phone_number' => $request->phone_number,
                    'status' => 'pending'
                ]);

                foreach ($items as $data) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $data['product']->id,
                        'product_name' => $data['product']->name,
                        'product_price' => $data['product']->price,
                        'quantity' => $data['quantity']
                    ]);

                    $productToUpdate = $data['product'];
                    $productToUpdate->quantity -= $data['quantity'];
                    if ($productToUpdate->quantity === 0) {
                        $productToUpdate->is_sold = true;
                    }
                    $productToUpdate->save();
                    
                    $post = $productToUpdate->post;
                    $allSoldOut = true;
                    foreach ($post->products as $p) {
                        if ($p->quantity > 0) {
                            $allSoldOut = false;
                            break;
                        }
                    }
                    
                    if ($allSoldOut) {
                        $post->update(['status' => 'hidden']);
                    }
                }
                
                $createdOrders[] = $order->id;
            }

            $productIds = collect($request->items)->pluck('product_id');
            \App\Models\Cart::where('user_id', Auth::id())
                ->whereIn('product_id', $productIds)
                ->delete();

            DB::commit();
            return response()->json(['message' => 'Đặt hàng thành công', 'orders' => $createdOrders], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi xử lý đơn hàng: ' . $e->getMessage()], 500);
        }
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items', 'seller:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($orders);
    }

    public function salesOrders()
    {
        $orders = Order::where('seller_id', Auth::id())
            ->with(['items', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

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

    public function show($id)
    {
        $order = Order::with(['items', 'user:id,name,avatar', 'seller:id,name,avatar'])
            ->findOrFail($id);

        if ($order->user_id !== Auth::id() && $order->seller_id !== Auth::id() && Auth::user()->role !== 'ADMIN') {
            return response()->json(['message' => 'Không có quyền xem đơn hàng này'], 403);
        }

        return response()->json($order);
    }

    public function confirmReceived($id)
    {
        $order = Order::findOrFail($id);

        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền xác nhận đơn hàng này'], 403);
        }

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