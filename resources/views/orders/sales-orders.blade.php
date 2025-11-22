@extends('layouts.app')

@section('title', 'Đơn hàng tôi bán - EcoStudent')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Đơn hàng tôi bán</h1>

    <div id="orders-container" class="space-y-4">
        <div class="text-center py-12">
            <p class="text-gray-500">Đang tải đơn hàng...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
async function loadOrders() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        document.getElementById('orders-container').innerHTML = `
            <div class="text-center py-12">
                <p class="text-gray-500 mb-4">Vui lòng đăng nhập để xem đơn hàng</p>
                <a href="/login" class="text-blue-600 hover:underline">Đăng nhập</a>
            </div>
        `;
        return;
    }

    try {
        const response = await fetch('/api/sales-orders', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const orders = await response.json();
            renderOrders(orders);
        }
    } catch (error) {
        console.error('Load orders error:', error);
    }
}

function renderOrders(orders) {
    const container = document.getElementById('orders-container');
    
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <p class="text-gray-500">Bạn chưa có đơn hàng nào</p>
            </div>
        `;
        return;
    }

    let html = '';
    orders.forEach(order => {
        const statusColors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'confirmed': 'bg-blue-100 text-blue-800',
            'shipping': 'bg-purple-100 text-purple-800',
            'completed': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800'
        };

        html += `
            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg mb-1">Đơn hàng #${order.id}</h3>
                        <p class="text-sm text-gray-500">Người mua: ${escapeHtml(order.user?.name || 'N/A')}</p>
                        <p class="text-sm text-gray-500">Ngày đặt: ${new Date(order.created_at).toLocaleDateString('vi-VN')}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium ${statusColors[order.status] || 'bg-gray-100 text-gray-800'}">
                        ${getStatusText(order.status)}
                    </span>
                </div>
                
                <div class="border-t pt-4 mb-4">
                    <div class="space-y-2 mb-4">
                        ${order.items.slice(0, 3).map(item => `
                            <div class="flex justify-between text-sm">
                                <span>${escapeHtml(item.product_name)} x ${item.quantity}</span>
                                <span>${new Intl.NumberFormat('vi-VN').format(item.product_price * item.quantity)} đ</span>
                            </div>
                        `).join('')}
                        ${order.items.length > 3 ? `<p class="text-sm text-gray-500">... và ${order.items.length - 3} sản phẩm khác</p>` : ''}
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t">
                        <span class="font-semibold">Tổng cộng:</span>
                        <span class="text-xl font-bold text-blue-600">${new Intl.NumberFormat('vi-VN').format(order.total_amount)} đ</span>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <a href="/orders/${order.id}" class="flex-1 text-center bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                        Xem chi tiết
                    </a>
                    ${order.status === 'pending' ? `
                        <button class="confirm-order-btn bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors" data-order-id="${order.id}">
                            Xác nhận
                        </button>
                        <button class="cancel-order-btn bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors" data-order-id="${order.id}">
                            Hủy
                        </button>
                    ` : ''}
                    ${order.status === 'confirmed' ? `
                        <button class="ship-order-btn bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition-colors" data-order-id="${order.id}">
                            Đang giao
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Attach event handlers
    document.querySelectorAll('.confirm-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            updateOrderStatus(orderId, 'confirmed');
        });
    });

    document.querySelectorAll('.cancel-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                updateOrderStatus(orderId, 'cancelled');
            }
        });
    });

    document.querySelectorAll('.ship-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            updateOrderStatus(orderId, 'shipping');
        });
    });
}

function getStatusText(status) {
    const statusMap = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'shipping': 'Đang giao',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy'
    };
    return statusMap[status] || status;
}

async function updateOrderStatus(orderId, status) {
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
            alert('Cập nhật trạng thái thành công!');
            await loadOrders();
        } else {
            alert('Cập nhật thất bại');
        }
    } catch (error) {
        console.error('Update status error:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadOrders();
</script>
@endsection

