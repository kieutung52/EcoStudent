@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng - EcoStudent')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="javascript:history.back()" class="text-blue-600 hover:underline flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>Quay lại</span>
        </a>
        <h1 class="text-3xl font-bold mt-4">Chi tiết đơn hàng</h1>
    </div>

    <div id="order-details-container">
        <div class="text-center py-12">
            <p class="text-gray-500">Đang tải thông tin đơn hàng...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const orderId = {{ $id }};
let order = null;
let isBuyer = false;
let isSeller = false;

async function loadOrderDetails() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        document.getElementById('order-details-container').innerHTML = `
            <div class="text-center py-12">
                <p class="text-gray-500 mb-4">Vui lòng đăng nhập để xem đơn hàng</p>
                <a href="/login" class="text-blue-600 hover:underline">Đăng nhập</a>
            </div>
        `;
        return;
    }

    try {
        const response = await fetch(`/api/orders/${orderId}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            order = await response.json();
            const user = JSON.parse(localStorage.getItem('user') || 'null');
            
            isBuyer = order.user_id === user?.id;
            isSeller = order.seller_id === user?.id;
            
            renderOrderDetails();
        } else {
            const error = await response.json();
            document.getElementById('order-details-container').innerHTML = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    ${error.message || 'Không thể tải thông tin đơn hàng'}
                </div>
            `;
        }
    } catch (error) {
        console.error('Load order error:', error);
        document.getElementById('order-details-container').innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                Có lỗi xảy ra khi tải thông tin đơn hàng
            </div>
        `;
    }
}

function renderOrderDetails() {
    const container = document.getElementById('order-details-container');
    
    const statusColors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'shipping': 'bg-purple-100 text-purple-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
    };

    const statusText = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'shipping': 'Đang giao hàng',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy'
    };

    const html = `
        <div class="bg-white rounded-lg shadow-lg p-6 space-y-6">
            <!-- Order Header -->
            <div class="border-b pb-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Đơn hàng #${order.id}</h2>
                        <p class="text-gray-600">Ngày đặt: ${new Date(order.created_at).toLocaleString('vi-VN')}</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-medium ${statusColors[order.status] || 'bg-gray-100'}">
                        ${statusText[order.status] || order.status}
                    </span>
                </div>
            </div>

            <!-- User Info -->
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">${isBuyer ? 'Người bán' : 'Người mua'}</h3>
                    <div class="flex items-center space-x-3">
                        <img src="${isBuyer ? (order.seller?.avatar ? `/storage/${order.seller.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(order.seller?.name || '')}`) : (order.user?.avatar ? `/storage/${order.user.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(order.user?.name || '')}`)}" 
                             alt="Avatar" 
                             class="w-12 h-12 rounded-full">
                        <div>
                            <p class="font-medium">${escapeHtml(isBuyer ? (order.seller?.name || 'N/A') : (order.user?.name || 'N/A'))}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Thông tin giao hàng</h3>
                    <p class="text-gray-600">${escapeHtml(order.shipping_address || 'Chưa có')}</p>
                    <p class="text-gray-600">SĐT: ${escapeHtml(order.phone_number || 'Chưa có')}</p>
                </div>
            </div>

            <!-- Order Items -->
            <div>
                <h3 class="font-semibold text-gray-700 mb-4">Sản phẩm</h3>
                <div class="space-y-3">
                    ${order.items.map(item => `
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex-1">
                                <p class="font-medium">${escapeHtml(item.product_name)}</p>
                                <p class="text-sm text-gray-500">Số lượng: ${item.quantity}</p>
                                <p class="text-sm text-gray-500">Giá: ${new Intl.NumberFormat('vi-VN').format(item.product_price)} đ</p>
                            </div>
                            <p class="font-semibold text-lg">${new Intl.NumberFormat('vi-VN').format(item.product_price * item.quantity)} đ</p>
                        </div>
                    `).join('')}
                </div>
            </div>

            <!-- Order Summary -->
            <div class="border-t pt-4">
                <div class="flex justify-between items-center">
                    <span class="text-xl font-semibold">Tổng cộng:</span>
                    <span class="text-2xl font-bold text-blue-600">${new Intl.NumberFormat('vi-VN').format(order.total_amount)} đ</span>
                </div>
                ${order.note ? `<p class="text-sm text-gray-600 mt-2">Ghi chú: ${escapeHtml(order.note)}</p>` : ''}
            </div>

            <!-- Actions -->
            <div class="border-t pt-4">
                ${isSeller ? renderSellerActions() : ''}
                ${isBuyer ? renderBuyerActions() : ''}
            </div>
        </div>
    `;

    container.innerHTML = html;

    // Attach event listeners
    if (isSeller) {
        attachSellerListeners();
    }
    if (isBuyer) {
        attachBuyerListeners();
    }
}

function renderSellerActions() {
    if (order.status === 'pending') {
        return `
            <div class="flex space-x-3">
                <button id="confirm-order-btn" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Xác nhận đơn hàng
                </button>
                <button id="cancel-order-btn" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Hủy đơn hàng
                </button>
            </div>
        `;
    } else if (order.status === 'confirmed') {
        return `
            <button id="ship-order-btn" class="w-full bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors font-medium">
                Xác nhận đang giao hàng
            </button>
        `;
    } else if (order.status === 'shipping') {
        return `
            <p class="text-gray-600 text-center">Đơn hàng đang được giao. Chờ người mua xác nhận đã nhận hàng.</p>
        `;
    } else if (order.status === 'completed') {
        return `
            <p class="text-green-600 text-center font-medium">Đơn hàng đã hoàn thành!</p>
        `;
    } else if (order.status === 'cancelled') {
        return `
            <p class="text-red-600 text-center font-medium">Đơn hàng đã bị hủy</p>
        `;
    }
    return '';
}

function renderBuyerActions() {
    if (order.status === 'shipping') {
        return `
            <button id="confirm-received-btn" class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors font-medium">
                Đã nhận hàng
            </button>
        `;
    } else if (order.status === 'completed') {
        return `
            <p class="text-green-600 text-center font-medium">Bạn đã xác nhận nhận hàng!</p>
        `;
    } else if (order.status === 'cancelled') {
        return `
            <p class="text-red-600 text-center font-medium">Đơn hàng đã bị hủy</p>
        `;
    }
    return '';
}

function attachSellerListeners() {
    const confirmBtn = document.getElementById('confirm-order-btn');
    const cancelBtn = document.getElementById('cancel-order-btn');
    const shipBtn = document.getElementById('ship-order-btn');

    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => updateOrderStatus('confirmed'));
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                updateOrderStatus('cancelled');
            }
        });
    }
    if (shipBtn) {
        shipBtn.addEventListener('click', () => updateOrderStatus('shipping'));
    }
}

function attachBuyerListeners() {
    const confirmReceivedBtn = document.getElementById('confirm-received-btn');
    if (confirmReceivedBtn) {
        confirmReceivedBtn.addEventListener('click', confirmReceived);
    }
}

async function updateOrderStatus(status) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/orders/${orderId}/status`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status })
        });

        if (response.ok) {
            const result = await response.json();
            order = result.data;
            alert('Cập nhật trạng thái thành công!');
            renderOrderDetails();
        } else {
            const error = await response.json();
            alert(error.message || 'Cập nhật thất bại');
        }
    } catch (error) {
        console.error('Update status error:', error);
        alert('Có lỗi xảy ra');
    }
}

async function confirmReceived() {
    if (!confirm('Bạn có chắc đã nhận được hàng?')) {
        return;
    }

    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/orders/${orderId}/confirm-received`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            const result = await response.json();
            order = result.data;
            alert('Đã xác nhận nhận hàng thành công!');
            renderOrderDetails();
        } else {
            const error = await response.json();
            alert(error.message || 'Xác nhận thất bại');
        }
    } catch (error) {
        console.error('Confirm received error:', error);
        alert('Có lỗi xảy ra');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadOrderDetails();
</script>
@endsection

