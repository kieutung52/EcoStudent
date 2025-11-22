@extends('layouts.app')

@section('title', 'Giỏ hàng - EcoStudent')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Giỏ hàng của tôi</h1>

    <div id="cart-container" class="bg-white rounded-lg shadow-lg p-6">
        <div class="text-center py-12">
            <p class="text-gray-500">Đang tải giỏ hàng...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let cartItems = [];

async function loadCart() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        document.getElementById('cart-container').innerHTML = `
            <div class="text-center py-12">
                <p class="text-gray-500 mb-4">Vui lòng đăng nhập để xem giỏ hàng</p>
                <a href="/login" class="text-blue-600 hover:underline">Đăng nhập</a>
            </div>
        `;
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
            renderCart();
        } else {
            document.getElementById('cart-container').innerHTML = `
                <div class="text-center py-12">
                    <p class="text-red-500">Không thể tải giỏ hàng</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Load cart error:', error);
    }
}

function renderCart() {
    const container = document.getElementById('cart-container');
    
    if (cartItems.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <p class="text-gray-500 mb-4">Giỏ hàng của bạn đang trống</p>
                <a href="/" class="text-blue-600 hover:underline">Tiếp tục mua sắm</a>
            </div>
        `;
        return;
    }

    let html = '<div class="space-y-4">';
    let total = 0;

    cartItems.forEach(item => {
        const itemTotal = item.product.price * item.quantity;
        total += itemTotal;
        
        const isSoldOut = item.product.quantity === 0;
        const isNotEnough = item.product.quantity < item.quantity;
        
        html += `
            <div class="flex items-center space-x-4 p-4 border rounded-lg cart-item relative ${isSoldOut ? 'bg-gray-100 opacity-75' : ''}" data-cart-id="${item.id}">
                ${isSoldOut ? '<div class="absolute inset-0 flex items-center justify-center bg-gray-200 bg-opacity-50 z-10"><span class="bg-red-600 text-white px-3 py-1 rounded font-bold">HẾT HÀNG</span></div>' : ''}
                <img src="${item.product.image ? (item.product.image.startsWith('http') ? item.product.image : '/storage/' + item.product.image) : 'https://via.placeholder.com/100'}" 
                     alt="${item.product.name}" 
                     class="w-24 h-24 object-cover rounded">
                <div class="flex-1">
                    <h3 class="font-semibold">${escapeHtml(item.product.name)}</h3>
                    <p class="text-gray-600 text-sm">${new Intl.NumberFormat('vi-VN').format(item.product.price)} đ</p>
                    ${isNotEnough && !isSoldOut ? `<p class="text-red-500 text-xs mt-1">Số lượng tồn kho chỉ còn ${item.product.quantity}</p>` : ''}
                </div>
                <div class="flex items-center space-x-2 z-20 relative">
                    <button class="decrease-qty px-3 py-1 border rounded hover:bg-gray-100" data-cart-id="${item.id}" ${isSoldOut ? 'disabled' : ''}>-</button>
                    <span class="quantity w-12 text-center">${item.quantity}</span>
                    <button class="increase-qty px-3 py-1 border rounded hover:bg-gray-100" data-cart-id="${item.id}" ${isSoldOut ? 'disabled' : ''}>+</button>
                </div>
                <div class="text-right z-20 relative">
                    <p class="font-semibold">${new Intl.NumberFormat('vi-VN').format(itemTotal)} đ</p>
                    <button class="remove-item text-red-600 text-sm mt-2 hover:underline" data-cart-id="${item.id}">Xóa</button>
                </div>
            </div>
        `;
    });

    const hasSoldOutItems = cartItems.some(item => item.product.quantity === 0);

    html += `
        <div class="border-t pt-4 mt-6">
            <div class="flex justify-between items-center">
                <span class="text-xl font-semibold">Tổng cộng:</span>
                <span class="text-2xl font-bold text-blue-600">${new Intl.NumberFormat('vi-VN').format(total)} đ</span>
            </div>
            ${hasSoldOutItems ? '<p class="text-red-500 mt-2 text-sm">Vui lòng xóa sản phẩm hết hàng để thanh toán.</p>' : ''}
            <button id="checkout-btn" class="w-full mt-4 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed" ${hasSoldOutItems ? 'disabled' : ''}>
                Thanh toán
            </button>
        </div>
    </div>`;

    container.innerHTML = html;

    // Attach event listeners
    attachCartEvents();
}

function attachCartEvents() {
    // Remove item
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', async function() {
            const cartId = this.dataset.cartId;
            await removeFromCart(cartId);
        });
    });

    // Increase quantity
    document.querySelectorAll('.increase-qty').forEach(btn => {
        btn.addEventListener('click', async function() {
            const cartId = this.dataset.cartId;
            const item = cartItems.find(i => i.id == cartId);
            if (item) {
                await updateCart(cartId, item.quantity + 1);
            }
        });
    });

    // Decrease quantity
    document.querySelectorAll('.decrease-qty').forEach(btn => {
        btn.addEventListener('click', async function() {
            const cartId = this.dataset.cartId;
            const item = cartItems.find(i => i.id == cartId);
            if (item && item.quantity > 1) {
                await updateCart(cartId, item.quantity - 1);
            }
        });
    });

    // Checkout
    document.getElementById('checkout-btn')?.addEventListener('click', () => {
        window.location.href = '/checkout';
    });
}

async function removeFromCart(cartId) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/cart/${cartId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            await loadCart();
        }
    } catch (error) {
        console.error('Remove cart error:', error);
    }
}

async function updateCart(cartId, quantity) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/cart/${cartId}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ quantity: quantity })
        });

        if (response.ok) {
            await loadCart();
        } else {
            const error = await response.json();
            alert(error.message || 'Không thể cập nhật số lượng');
        }
    } catch (error) {
        console.error('Update cart error:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadCart();
</script>
@endsection

