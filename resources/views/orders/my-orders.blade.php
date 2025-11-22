@extends('layouts.app')

@section('title', 'Đơn hàng của tôi - EcoStudent')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Đơn hàng của tôi</h1>

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
        const response = await fetch('/api/my-orders', {
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
                        <p class="text-sm text-gray-500">Người bán: ${escapeHtml(order.seller?.name || 'N/A')}</p>
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
                    ${order.status === 'shipping' ? `
                        <button class="confirm-received-btn bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors" data-order-id="${order.id}">
                            Đã nhận hàng
                        </button>
                    ` : ''}
                    ${order.status === 'completed' ? `
                        <button class="review-btn bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors" data-order-id="${order.id}">
                            Đánh giá
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Review Modal Logic
    const reviewModal = document.getElementById('review-modal');
    const reviewForm = document.getElementById('review-form');
    const closeReviewBtn = document.getElementById('close-review-modal');
    let currentReviewOrderId = null;

    // Initialize Star Rating
    const starContainer = document.getElementById('star-rating-container');
    const ratingInput = document.getElementById('review-rating');
    
    function renderStars(rating) {
        starContainer.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const star = document.createElement('button');
            star.type = 'button';
            star.className = `w-8 h-8 focus:outline-none transition-colors ${i <= rating ? 'text-yellow-400' : 'text-gray-300'}`;
            star.innerHTML = `
                <svg class="w-full h-full fill-current" viewBox="0 0 24 24">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
            `;
            star.addEventListener('click', () => {
                ratingInput.value = i;
                renderStars(i);
            });
            starContainer.appendChild(star);
        }
    }
    renderStars(5); // Default 5 stars

    function openReviewModal(orderId) {
        currentReviewOrderId = orderId;
        reviewModal.classList.remove('hidden');
        renderStars(5); // Reset to 5
        ratingInput.value = 5;
    }

    closeReviewBtn.addEventListener('click', () => {
        reviewModal.classList.add('hidden');
        reviewForm.reset();
        currentReviewOrderId = null;
    });

    reviewForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentReviewOrderId) return;

        const token = localStorage.getItem('jwt_token');
        const rating = document.getElementById('review-rating').value;
        const comment = document.getElementById('review-comment').value;

        try {
            const response = await fetch(`/api/orders/${currentReviewOrderId}/reviews`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ rating, comment })
            });

            if (response.ok) {
                alert('Đánh giá thành công!');
                reviewModal.classList.add('hidden');
                reviewForm.reset();
                loadOrders(); // Reload to update UI
            } else {
                const error = await response.json();
                alert(error.message || 'Lỗi khi gửi đánh giá');
            }
        } catch (error) {
            console.error('Review error:', error);
            alert('Có lỗi xảy ra');
        }
    });

    // Attach event handlers
    document.querySelectorAll('.confirm-received-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            confirmReceived(orderId);
        });
    });

    document.querySelectorAll('.review-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.dataset.orderId;
            openReviewModal(orderId);
        });
    });
}

async function confirmReceived(orderId) {
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
            alert('Đã xác nhận nhận hàng thành công!');
            await loadOrders();
        } else {
            const error = await response.json();
            alert(error.message || 'Xác nhận thất bại');
        }
    } catch (error) {
        console.error('Confirm received error:', error);
        alert('Có lỗi xảy ra');
    }
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadOrders();
</script>
@endsection

<!-- Review Modal -->
<div id="review-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Đánh giá đơn hàng</h3>
            <button id="close-review-modal" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="review-form">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Đánh giá sao</label>
                <div class="flex space-x-2" id="star-rating-container">
                    <!-- Stars will be rendered here -->
                </div>
                <input type="hidden" id="review-rating" value="5">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nhận xét</label>
                <textarea id="review-comment" rows="4" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Gửi đánh giá
            </button>
        </form>
    </div>
</div>

