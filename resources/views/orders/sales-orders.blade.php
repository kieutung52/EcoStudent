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
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-semibold">Đơn hàng #${order.id}</h3>
                        <p class="text-sm text-gray-500">Người mua: ${escapeHtml(order.user?.name || 'N/A')}</p>
                        <p class="text-sm text-gray-500">Ngày đặt: ${new Date(order.created_at).toLocaleDateString('vi-VN')}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <select class="status-select px-3 py-1 border rounded" data-order-id="${order.id}">
                            <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Chờ xác nhận</option>
                            <option value="confirmed" ${order.status === 'confirmed' ? 'selected' : ''}>Đã xác nhận</option>
                            <option value="shipping" ${order.status === 'shipping' ? 'selected' : ''}>Đang giao</option>
                            <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Hoàn thành</option>
                            <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Đã hủy</option>
                        </select>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <div class="space-y-2">
                        ${order.items.map(item => `
                            <div class="flex justify-between">
                                <span>${escapeHtml(item.product_name)} x ${item.quantity}</span>
                                <span>${new Intl.NumberFormat('vi-VN').format(item.product_price * item.quantity)} đ</span>
                            </div>
                        `).join('')}
                    </div>
                    <div class="flex justify-between items-center mt-4 pt-4 border-t">
                        <span class="font-semibold">Tổng cộng:</span>
                        <span class="text-xl font-bold text-blue-600">${new Intl.NumberFormat('vi-VN').format(order.total_amount)} đ</span>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Attach status change handlers
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', async function() {
            const orderId = this.dataset.orderId;
            const newStatus = this.value;
            await updateOrderStatus(orderId, newStatus);
        });
    });
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

