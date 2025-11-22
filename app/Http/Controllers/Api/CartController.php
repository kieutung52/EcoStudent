<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Lấy danh sách giỏ hàng
    public function index()
    {
        $cartItems = Cart::with('product')->where('user_id', Auth::id())->get();
        return response()->json($cartItems);
    }

    // Thêm vào giỏ
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($request->product_id);
        if ($product->quantity < $request->quantity) {
            return response()->json(['message' => 'Số lượng sản phẩm không đủ'], 400);
        }

        $cart = Cart::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $request->product_id],
            ['quantity' => DB::raw("quantity + $request->quantity")]
        );

        return response()->json($cart, 201);
    }

    // Xóa khỏi giỏ
    public function destroy($id)
    {
        Cart::where('id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Đã xóa sản phẩm khỏi giỏ']);
    }
}