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

                        return `
                            <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
                                ${imageHtml}
                                <h4 class="font-semibold mt-2 text-gray-900">${escapeHtml(product.name)}</h4>
                                <p class="text-xl font-bold text-blue-600 mt-1">
                                    ${new Intl.NumberFormat('vi-VN').format(product.price)} đ
                                </p>
                                <p class="text-sm text-gray-600 mt-1">Số lượng: ${product.quantity}</p>
                                ${product.description ? `<p class="text-sm text-gray-700 mt-2">${escapeHtml(product.description)}</p>` : ''}
                                ${product.is_sold ? '<span class="inline-block mt-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Đã bán</span>' : ''}
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
        </div>

        ${productsHtml}

        <!-- Comments Section -->
        <div class="mt-6 pt-6 border-t">
            <h3 class="text-xl font-semibold mb-4">Bình luận</h3>
            <div id="comments-container" class="space-y-4">
                ${post.comments && post.comments.length > 0 
                    ? post.comments.map(comment => `
                        <div class="flex space-x-3">
                            <img src="${comment.user?.avatar ? `/storage/${comment.user.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user?.name || 'User')}`}" 
                                 alt="${escapeHtml(comment.user?.name || 'User')}" 
                                 class="w-10 h-10 rounded-full">
                            <div class="flex-1">
                                <p class="font-semibold text-sm">${escapeHtml(comment.user?.name || 'User')}</p>
                                <p class="text-gray-700">${escapeHtml(comment.content)}</p>
                                <p class="text-xs text-gray-500 mt-1">${new Date(comment.created_at).toLocaleDateString('vi-VN')}</p>
                            </div>
                        </div>
                    `).join('')
                    : '<p class="text-gray-500">Chưa có bình luận nào</p>'
                }
            </div>
        </div>
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
@endsection

