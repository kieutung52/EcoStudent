@extends('layouts.app')

@section('title', $seller->name . ' - EcoStudent')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Seller Info Card -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center space-x-6">
            <img src="{{ $seller->avatar ? asset('storage/' . $seller->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($seller->name) }}" 
                 alt="{{ $seller->name }}" 
                 class="w-24 h-24 rounded-full object-cover border-4 border-blue-100">
            
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $seller->name }}</h1>
                
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    @if($seller->university)
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            {{ $seller->university->name }}
                        </div>
                    @endif
                    
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Tham gia {{ $seller->created_at->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts List -->
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Bài viết của {{ $seller->name }}</h2>
        
        <div id="posts-container">
            @forelse($posts as $post)
                @if ($post->status === 'pending' || $post->status === 'rejected' || $post->status === 'hidden')
                    @continue
                @endif
                @include('partials.post-card', ['post' => $post])
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <p>Người dùng này chưa có bài viết nào.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    </div>
</div>

<!-- Re-use the same modals and scripts from home if needed, or include a partial -->
<!-- For simplicity, we assume app.js handles global things, but specific post interactions might need the script from home.blade.php -->
<!-- Ideally, the script in home.blade.php should be moved to a separate js file or partial. -->
<!-- For now, I will copy the necessary script parts or include a common script if available. -->
<!-- Since I cannot easily refactor home.blade.php right now without risk, I will duplicate the necessary JS for post interactions (like, comment, report). -->

@endsection

@section('scripts')
<script>
    // Copying essential parts from home.blade.php script for post interactions
    
    function attachPostEventListeners() {
        // Like buttons
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const postId = this.dataset.postId;
                const isLiked = this.dataset.liked === 'true';
                const token = localStorage.getItem('jwt_token');

                if (!token) {
                    alert('Vui lòng đăng nhập để like bài viết');
                    return;
                }

                try {
                    const response = await fetch(`/api/posts/${postId}/like`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        const likeCount = this.querySelector('.like-count');
                        const svg = this.querySelector('svg');
                        
                        if (isLiked) {
                            this.dataset.liked = 'false';
                            svg.classList.remove('text-blue-600', 'fill-current');
                            likeCount.textContent = Math.max(0, parseInt(likeCount.textContent) - 1);
                        } else {
                            this.dataset.liked = 'true';
                            svg.classList.add('text-blue-600', 'fill-current');
                            likeCount.textContent = parseInt(likeCount.textContent) + 1;
                        }
                    }
                } catch (error) {
                    console.error('Like error:', error);
                }
            });
        });

        // Comment/Review buttons
        document.querySelectorAll('.comment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                const userId = this.dataset.userId;
                const reviewsSection = document.querySelector(`.reviews-section[data-post-id="${postId}"]`);
                
                if (reviewsSection) {
                    reviewsSection.classList.toggle('hidden');
                    if (!reviewsSection.classList.contains('hidden')) {
                        loadSellerReviews(userId, postId);
                    }
                }
            });
        });
    }

    async function loadSellerReviews(userId, postId) {
        const container = document.querySelector(`.reviews-section[data-post-id="${postId}"] .reviews-list`);
        if (!container) return;

        try {
            const response = await fetch(`/api/users/${userId}/reviews`, {
                headers: { 'Accept': 'application/json' }
            });

            if (response.ok) {
                const data = await response.json();
                const reviews = data.data; 
                
                if (reviews.length === 0) {
                    container.innerHTML = '<p class="text-center text-gray-500 text-sm py-2">Người bán chưa có đánh giá nào</p>';
                } else {
                    container.innerHTML = reviews.map(review => {
                        const stars = Array(5).fill(0).map((_, i) => 
                            `<svg class="w-4 h-4 ${i < review.rating ? 'text-yellow-400' : 'text-gray-300'} fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>`
                        ).join('');

                        return `
                            <div class="flex space-x-2 border-b pb-2 last:border-0">
                                <img src="${review.reviewer?.avatar ? `/storage/${review.reviewer.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(review.reviewer?.name || 'User')}`}" 
                                    class="w-8 h-8 rounded-full object-cover">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="font-semibold text-sm text-gray-900">${escapeHtml(review.reviewer?.name || 'User')}</p>
                                        <div class="flex">${stars}</div>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-1">Đơn hàng #${review.order_id}</p>
                                    ${review.comment ? `<p class="text-sm text-gray-700">${escapeHtml(review.comment)}</p>` : ''}
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }
        } catch (error) {
            container.innerHTML = '<p class="text-center text-red-500 text-sm py-2">Lỗi tải đánh giá</p>';
        }
    }

    // Helper
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    document.addEventListener('DOMContentLoaded', function() {
        attachPostEventListeners();
        
        // Product click delegation
        document.body.addEventListener('click', async function(e) {
            const productItem = e.target.closest('.product-item');
            if (productItem) {
                e.preventDefault();
                e.stopPropagation();
                
                const postId = productItem.dataset.postId;
                const productId = productItem.dataset.productId;
                const productIndex = parseInt(productItem.dataset.productIndex || 0);
                const inlineView = productItem.dataset.inlineView === 'true';
                
                // Reuse the global function if available or reimplement
                // Assuming showProductDetailInline is available globally or I need to copy it too.
                // For now, I'll assume the user can click into the post detail if inline doesn't work, 
                // OR I should copy showProductDetailInline here too.
                // Let's copy it to be safe.
                if (inlineView && productIndex < 4) {
                    await showProductDetailInline(postId, productId, productIndex);
                }
            }
        });
    });

    // Copy showProductDetailInline from home.blade.php
    async function showProductDetailInline(postId, productId, productIndex) {
        const token = localStorage.getItem('jwt_token');
        const inlineContainer = document.getElementById(`product-detail-inline-${postId}`);
        
        if (!inlineContainer) return;

        inlineContainer.innerHTML = '<div class="text-center py-4"><p class="text-gray-500">Đang tải...</p></div>';
        inlineContainer.classList.remove('hidden');

        try {
            const response = await fetch(`/api/posts/${postId}`, {
                headers: {
                    'Authorization': token ? `Bearer ${token}` : '',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to load post');

            const post = await response.json();
            const products = post.products || [];
            
            if (products.length === 0) {
                inlineContainer.innerHTML = '<div class="text-center py-4"><p class="text-gray-500">Không có sản phẩm</p></div>';
                return;
            }

            let currentIndex = productIndex;
            if (productId) {
                currentIndex = products.findIndex(p => p.id == productId);
                if (currentIndex === -1) currentIndex = 0;
            }

            const product = products[currentIndex];
            const imageUrl = product.image 
                ? (product.image.startsWith('http') ? product.image : `/${product.image}`)
                : null;

            const imageHtml = imageUrl 
                ? `<img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full h-64 object-cover rounded-lg">`
                : `<div class="w-full h-64 flex items-center justify-center bg-gray-100 rounded-lg">
                    <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>`;

            let navigationHtml = '';
            if (products.length > 1) {
                navigationHtml = `
                    <div class="flex justify-between items-center mt-4 pt-4 border-t">
                        <button class="prev-inline-product px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors ${currentIndex === 0 ? 'opacity-50 cursor-not-allowed' : ''}" 
                                data-post-id="${postId}" 
                                data-product-index="${currentIndex - 1}"
                                ${currentIndex === 0 ? 'disabled' : ''}>
                            ← Trước
                        </button>
                        <span class="text-sm text-gray-600">${currentIndex + 1} / ${products.length}</span>
                        <button class="next-inline-product px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors ${currentIndex >= products.length - 1 ? 'opacity-50 cursor-not-allowed' : ''}" 
                                data-post-id="${postId}" 
                                data-product-index="${currentIndex + 1}"
                                ${currentIndex >= products.length - 1 ? 'disabled' : ''}>
                            Sau →
                        </button>
                    </div>
                `;
            }

            inlineContainer.innerHTML = `
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-lg font-semibold text-gray-900">Chi tiết sản phẩm</h3>
                    <button class="close-inline-product text-gray-500 hover:text-gray-700" data-post-id="${postId}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>${imageHtml}</div>
                    <div>
                        <h2 class="text-2xl font-bold mb-2 text-gray-900">${escapeHtml(product.name)}</h2>
                        <p class="text-3xl font-bold text-blue-600 mb-4">${new Intl.NumberFormat('vi-VN').format(product.price)} đ</p>
                        <p class="text-gray-700 mb-4">${escapeHtml(product.description || 'Không có mô tả')}</p>
                        <div class="space-y-2 mb-4">
                            <p><span class="font-semibold">Số lượng:</span> ${product.quantity}</p>
                            <p><span class="font-semibold">Tình trạng:</span> ${product.is_sold ? 'Đã bán' : 'Còn hàng'}</p>
                        </div>
                        <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors add-to-cart-inline-btn" 
                                data-product-id="${product.id}"
                                ${product.is_sold || product.quantity === 0 ? 'disabled class="bg-gray-400 cursor-not-allowed"' : ''}>
                            ${product.is_sold || product.quantity === 0 ? 'Đã hết hàng' : 'Thêm vào giỏ hàng'}
                        </button>
                    </div>
                </div>
                ${navigationHtml}
            `;

            // Attach inline listeners
            inlineContainer.querySelector('.close-inline-product').addEventListener('click', () => inlineContainer.classList.add('hidden'));
            
            const prevBtn = inlineContainer.querySelector('.prev-inline-product');
            if (prevBtn) prevBtn.addEventListener('click', () => showProductDetailInline(postId, null, parseInt(prevBtn.dataset.productIndex)));
            
            const nextBtn = inlineContainer.querySelector('.next-inline-product');
            if (nextBtn) nextBtn.addEventListener('click', () => showProductDetailInline(postId, null, parseInt(nextBtn.dataset.productIndex)));
            
            const addToCartBtn = inlineContainer.querySelector('.add-to-cart-inline-btn');
            if (addToCartBtn && !addToCartBtn.disabled) {
                addToCartBtn.addEventListener('click', async function() {
                    const productId = this.dataset.productId;
                    const token = localStorage.getItem('jwt_token');
                    if (!token) return alert('Vui lòng đăng nhập');
                    
                    try {
                        const res = await fetch('/api/cart', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ product_id: productId, quantity: 1 })
                        });
                        if (res.ok) alert('Đã thêm vào giỏ hàng');
                        else alert((await res.json()).message || 'Lỗi');
                    } catch (e) { alert('Lỗi'); }
                });
            }

            inlineContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        } catch (error) {
            inlineContainer.innerHTML = '<div class="text-center py-4"><p class="text-red-500">Lỗi tải sản phẩm</p></div>';
        }
    }
</script>
@endsection
