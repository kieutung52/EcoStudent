@extends('layouts.app')

@section('title', 'Thanh toán - EcoStudent')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Thanh toán</h1>

    <div class="grid grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Thông tin giao hàng</h2>
            <form id="checkout-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ giao hàng</label>
                    <textarea id="shipping_address" 
                              name="shipping_address" 
                              required
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              rows="3"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                    <input type="text" 
                           id="phone_number" 
                           name="phone_number" 
                           required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú (tùy chọn)</label>
                    <textarea id="note" 
                              name="note" 
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              rows="2"></textarea>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
                    Đặt hàng
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Đơn hàng</h2>
            <div id="checkout-items" class="space-y-2 mb-4">
                <!-- Items will be loaded here -->
            </div>
            <div class="border-t pt-4">
                <div class="flex justify-between">
                    <span class="font-semibold">Tổng cộng:</span>
                    <span id="total-amount" class="text-xl font-bold text-blue-600">0 đ</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let cartItems = [];

async function loadCheckoutData() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {
        const response = await fetch('/api/cart', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            cartItems = await response.json();
            renderCheckoutItems();
        } else {
            window.location.href = '/cart';
        }
    } catch (error) {
        console.error('Load checkout error:', error);
    }
}

function renderCheckoutItems() {
    const container = document.getElementById('checkout-items');
    let total = 0;

    if (cartItems.length === 0) {
        container.innerHTML = '<p class="text-gray-500">Giỏ hàng trống</p>';
        return;
    }

    let html = '';
    cartItems.forEach(item => {
        const itemTotal = item.product.price * item.quantity;
        total += itemTotal;
        
        html += `
            <div class="flex justify-between py-2 border-b">
                <div>
                    <p class="font-medium">${escapeHtml(item.product.name)}</p>
                    <p class="text-sm text-gray-500">x ${item.quantity}</p>
                </div>
                <span>${new Intl.NumberFormat('vi-VN').format(itemTotal)} đ</span>
            </div>
        `;
    });

    container.innerHTML = html;
    document.getElementById('total-amount').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
}

document.getElementById('checkout-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const token = localStorage.getItem('jwt_token');
    const formData = new FormData(this);
    
    // Prepare items for checkout
    const items = cartItems.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity
    }));

    const data = {
        items: items,
        shipping_address: formData.get('shipping_address'),
        phone_number: formData.get('phone_number'),
        note: formData.get('note') || ''
    };

    try {
        const response = await fetch('/api/checkout', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            const result = await response.json();
            alert('Đặt hàng thành công!');
            window.location.href = '/my-orders';
        } else {
            const error = await response.json();
            alert(error.message || 'Đặt hàng thất bại');
        }
    } catch (error) {
        console.error('Checkout error:', error);
        alert('Có lỗi xảy ra');
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadCheckoutData();
</script>
@endsection

