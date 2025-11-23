@extends('layouts.app')

@section('title', 'Chi tiết bài viết - EcoStudent')

@section('content')
<div class="max-w-4xl mx-auto">
    <div id="post-detail-container" class="bg-white rounded-lg shadow-lg p-6">
        <p class="text-center text-gray-500 py-8">Đang tải...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
let postId = {{ request()->route('id') }};
let currentUser = null;

// Load user from localStorage
try {
    const userStr = localStorage.getItem('user');
    if (userStr) {
        currentUser = JSON.parse(userStr);
    }
} catch (e) {
    console.error('Error parsing user:', e);
}

async function loadPostDetail() {
    const token = localStorage.getItem('jwt_token');
    const container = document.getElementById('post-detail-container');
    
    try {
        const response = await fetch(`/api/posts/${postId}`, {
            headers: {
                'Authorization': token ? `Bearer ${token}` : '',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load post');
        }

        const post = await response.json();
        renderPostDetail(post);
    } catch (error) {
        console.error('Load post error:', error);
        container.innerHTML = '<p class="text-center text-red-500 py-8">Không thể tải bài viết</p>';
    }
}

function renderPostDetail(post) {
    const container = document.getElementById('post-detail-container');
    const isOwner = currentUser && currentUser.id == post.user_id;
    const isAdmin = currentUser && currentUser.role === 'ADMIN';
    
    const statusColors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'hidden': 'bg-gray-100 text-gray-800',
        'sold_out': 'bg-blue-100 text-blue-800'
    };

    const statusText = {
        'pending': 'Chờ duyệt',
        'approved': 'Đã duyệt',
        'rejected': 'Đã từ chối',
        'hidden': 'Đã ẩn',
        'sold_out': 'Đã bán hết'
    };

    let actionButtons = '';
    if (isOwner || isAdmin) {
        actionButtons = `
            <div class="flex space-x-2 mb-4">
                ${isOwner ? `
                    <a href="/posts/${post.id}/edit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Sửa bài
                    </a>
                ` : ''}
                <button class="delete-post-btn px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors" data-post-id="${post.id}">
                    Xóa bài
                </button>
            </div>
        `;
    }

    let productsHtml = '';
    if (post.products && post.products.length > 0) {
        productsHtml = `
            <div class="mt-6">
                <h3 class="text-xl font-semibold mb-4">Sản phẩm (${post.products.length})</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    ${post.products.map(product => {
                        const imageUrl = product.image 
                            ? (product.image.startsWith('http') ? product.image : `/${product.image}`)
                            : null;
                        
                        const imageHtml = imageUrl 
                            ? `<img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full h-48 object-cover rounded-lg">`
                            : `<div class="w-full h-48 flex items-center justify-center bg-gray-100 rounded-lg">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>`;

                        const isSoldOut = product.quantity === 0 || product.is_sold;
                        
                        return `
                            <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow relative ${isSoldOut ? 'bg-gray-50' : ''}">
                                ${isSoldOut ? '<div class="absolute inset-0 flex items-center justify-center bg-gray-200 bg-opacity-50 z-10 rounded-lg"><span class="bg-red-600 text-white px-3 py-1 rounded font-bold transform -rotate-12">HẾT HÀNG</span></div>' : ''}
                                ${imageHtml}
                                <h4 class="font-semibold mt-2 text-gray-900">${escapeHtml(product.name)}</h4>
                                <p class="text-xl font-bold text-blue-600 mt-1">
                                    ${new Intl.NumberFormat('vi-VN').format(product.price)} đ
                                </p>
                                <p class="text-sm text-gray-600 mt-1">Số lượng: ${product.quantity}</p>
                                ${product.description ? `<p class="text-sm text-gray-700 mt-2">${escapeHtml(product.description)}</p>` : ''}
                                ${isSoldOut ? '' : `
                                    <button class="mt-3 w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors add-to-cart-btn z-20 relative" data-product-id="${product.id}">
                                        Thêm vào giỏ
                                    </button>
                                `}
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    container.innerHTML = `
        <div class="mb-4">
            <a href="/" class="text-blue-600 hover:underline">← Quay lại trang chủ</a>
        </div>
        
        ${actionButtons}
        
        <div class="flex items-center space-x-3 mb-4">
            <img src="${post.user?.avatar ? `/storage/${post.user.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user?.name || 'User')}`}" 
                 alt="${escapeHtml(post.user?.name || 'User')}" 
                 class="w-12 h-12 rounded-full object-cover">
            <div>
                <h3 class="font-semibold text-gray-900">${escapeHtml(post.user?.name || 'User')}</h3>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <span>${new Date(post.created_at).toLocaleDateString('vi-VN')}</span>
                    ${post.university ? `<span>•</span><span>${escapeHtml(post.university.name)}</span>` : ''}
                </div>
            </div>
            ${post.status ? `<span class="ml-auto px-3 py-1 rounded-full text-sm font-medium ${statusColors[post.status] || 'bg-gray-100'}">${statusText[post.status] || post.status}</span>` : ''}
        </div>

        <h1 class="text-3xl font-bold mb-4 text-gray-900">${escapeHtml(post.title)}</h1>
        
        ${post.content ? `<div class="text-gray-700 mb-6 whitespace-pre-wrap">${escapeHtml(post.content)}</div>` : ''}

        <div class="flex items-center space-x-6 text-sm text-gray-600 mb-6 pb-4 border-b">
            <span>${post.products?.length || 0} sản phẩm</span>
            <span>•</span>
            <span>${post.likes?.length || 0} lượt thích</span>
            <span>•</span>
            <span>${post.view_count || 0} lượt xem</span>
            <span class="mx-2">•</span>
            <button class="text-red-600 hover:text-red-800 flex items-center space-x-1 report-btn" onclick="openReportModal(${post.id})">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-8a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5h6a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1 1 0 00-1 1v3"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>Báo cáo</span>
            </button>
        </div>

        ${productsHtml}


    `;

    // Attach delete button handler
    const deleteBtn = container.querySelector('.delete-post-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa bài viết này?')) {
                deletePost(postId);
            }
        });
    }

    // Attach add to cart handlers
    container.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const productId = this.dataset.productId;
            await addToCart(productId);
        });
    });
}

async function addToCart(productId) {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        alert('Vui lòng đăng nhập để mua hàng');
        return;
    }

    try {
        const response = await fetch('/api/cart', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        });

        if (response.ok) {
            alert('Đã thêm vào giỏ hàng');
        } else {
            const error = await response.json();
            alert(error.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        console.error('Add to cart error:', error);
        alert('Có lỗi xảy ra');
    }
}

async function deletePost(id) {
    const token = localStorage.getItem('jwt_token');
    
    if (!token) {
        alert('Vui lòng đăng nhập để xóa bài viết');
        return;
    }

    try {
        const response = await fetch(`/api/posts/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            alert('Đã xóa bài viết');
            window.location.href = '/';
        } else {
            const error = await response.json();
            alert(error.message || 'Xóa thất bại');
        }
    } catch (error) {
        console.error('Delete post error:', error);
        alert('Có lỗi xảy ra');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load post on page load
loadPostDetail();
</script>
<!-- Report Modal -->
<div id="report-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-bold mb-4">Báo cáo bài viết</h3>
        <form id="report-form">
            <input type="hidden" id="report-post-id">
            <div class="mb-4">
                <p class="mb-2 font-medium text-gray-700">Lý do báo cáo:</p>
                <div id="report-rules-list" class="space-y-2 max-h-48 overflow-y-auto border p-2 rounded mb-2">
                    <p class="text-gray-500 text-sm">Đang tải lý do...</p>
                </div>
                <div class="mt-2">
                    <label class="flex items-center space-x-2">
                        <input type="radio" name="report_reason" value="other" class="form-radio text-blue-600">
                        <span>Lý do khác</span>
                    </label>
                    <textarea id="report-other-reason" class="w-full mt-2 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 hidden" rows="3" placeholder="Nhập lý do của bạn..."></textarea>
                </div>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeReportModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                    Hủy
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                    Gửi báo cáo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let postId = {{ request()->route('id') }};
let currentUser = null;
let reportRulesLoaded = false;

// ... (Existing user loading code) ...

// Report Modal Logic
async function openReportModal(id) {
    document.getElementById('report-post-id').value = id;
    document.getElementById('report-modal').classList.remove('hidden');
    
    if (!reportRulesLoaded) {
        await loadReportRules();
    }
}

function closeReportModal() {
    document.getElementById('report-modal').classList.add('hidden');
    document.getElementById('report-form').reset();
    document.getElementById('report-other-reason').classList.add('hidden');
}

async function loadReportRules() {
    const container = document.getElementById('report-rules-list');
    try {
        const response = await fetch('/api/rules');
        if (response.ok) {
            const rules = await response.json();
            if (rules.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm">Không có lý do cụ thể</p>';
            } else {
                container.innerHTML = rules.map(rule => `
                    <label class="flex items-start space-x-2 cursor-pointer">
                        <input type="radio" name="report_reason" value="${escapeHtml(rule.content)}" class="form-radio text-blue-600 mt-1">
                        <span class="text-sm text-gray-700">${escapeHtml(rule.title)}: ${escapeHtml(rule.content)}</span>
                    </label>
                `).join('');
            }
            reportRulesLoaded = true;
        }
    } catch (error) {
        console.error('Load rules error:', error);
        container.innerHTML = '<p class="text-red-500 text-sm">Lỗi tải lý do</p>';
    }
}

// Toggle other reason textarea
document.getElementById('report-form').addEventListener('change', function(e) {
    if (e.target.name === 'report_reason') {
        const otherTextarea = document.getElementById('report-other-reason');
        if (e.target.value === 'other') {
            otherTextarea.classList.remove('hidden');
            otherTextarea.required = true;
        } else {
            otherTextarea.classList.add('hidden');
            otherTextarea.required = false;
        }
    }
});

// Submit Report
document.getElementById('report-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('report-post-id').value;
    const selectedReason = document.querySelector('input[name="report_reason"]:checked');
    const otherReason = document.getElementById('report-other-reason').value;
    
    if (!selectedReason) {
        alert('Vui lòng chọn lý do báo cáo');
        return;
    }

    let reason = selectedReason.value;
    if (reason === 'other') {
        reason = otherReason;
        if (!reason.trim()) {
            alert('Vui lòng nhập lý do cụ thể');
            return;
        }
    }

    const token = localStorage.getItem('jwt_token');
    if (!token) {
        alert('Vui lòng đăng nhập để báo cáo');
        return;
    }

    try {
        const response = await fetch(`/api/posts/${id}/reports`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ reason })
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message || 'Báo cáo thành công');
            closeReportModal();
        } else {
            alert(result.message || 'Báo cáo thất bại');
        }
    } catch (error) {
        console.error('Report error:', error);
        alert('Có lỗi xảy ra');
    }
});

// ... (Existing loadPostDetail and other functions) ...

