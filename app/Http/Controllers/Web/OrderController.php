<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Hiển thị trang đơn hàng của tôi
     */
    public function myOrders()
    {
        return view('orders.my-orders');
    }

    /**
     * Hiển thị trang đơn hàng tôi bán
     */
    public function salesOrders()
    {
        return view('orders.sales-orders');
    }

    /**
     * Hiển thị trang checkout
     */
    public function checkout()
    {
        return view('orders.checkout');
    }

    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show($id)
    {
        return view('orders.details', compact('id'));
    }
}

