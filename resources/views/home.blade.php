@extends('layouts.app')

@section('title', 'Trang chủ - EcoStudent')

@section('content')
<div class="flex gap-6">
    <!-- Sidebar Left (Filters) -->
    <aside class="w-64 bg-white rounded-lg shadow p-4 h-fit sticky top-20">
        <h2 class="text-lg font-semibold mb-4">Bộ lọc</h2>
        
        <!-- Search -->
        <div class="mb-4">
            <input type="text" id="search-input" placeholder="Tìm kiếm..." 
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- University Filter -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Trường Đại học</label>
            <select id="university-filter" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả</option>
                @foreach($universities as $university)
                    <option value="{{ $university->id }}">{{ $university->name }}</option>
                @endforeach
            </select>
        </div>
    </aside>

    <!-- Main Feed -->
    <div class="flex-1 max-w-2xl">
        <div id="posts-container">
            @foreach($posts as $post)
                @include('partials.post-card', ['post' => $post])
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    </div>

    <!-- Sidebar Right (Optional) -->
    <aside class="w-64">
        <!-- Có thể thêm thông tin khác ở đây -->
    </aside>
</div>
@endsection

@section('scripts')
<script>
    // Note: updateNavigation() is already defined in app.blade.php layout
    // We don't need to redefine it here, just ensure it's called after page load

    // Filter handlers
    document.getElementById('university-filter')?.addEventListener('change', (e) => {
        const universityId = e.target.value;
        const url = new URL(window.location);
        if (universityId) {
            url.searchParams.set('university_id', universityId);
        } else {
            url.searchParams.delete('university_id');
        }
        window.location.href = url.toString();
    });

    document.getElementById('search-input')?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const keyword = e.target.value;
            const url = new URL(window.location);
            if (keyword) {
                url.searchParams.set('keyword', keyword);
            } else {
                url.searchParams.delete('keyword');
            }
            window.location.href = url.toString();
        }
    });

    // Product click handlers - use event delegation for dynamically loaded content
    document.addEventListener('DOMContentLoaded', function() {
        // Product items click - use event delegation
        document.body.addEventListener('click', async function(e) {
            const productItem = e.target.closest('.product-item, .product-item-more');
            if (productItem) {
                e.preventDefault();
                e.stopPropagation();
                
                const postId = productItem.dataset.postId;
                const productId = productItem.dataset.productId;
                const productIndex = parseInt(productItem.dataset.productIndex || productItem.dataset.startIndex || 0);
                const inlineView = productItem.dataset.inlineView === 'true';
                
                console.log('Product clicked:', { postId, productId, productIndex, inlineView });
                
                // Nếu là inline view (4 sản phẩm đầu) thì hiển thị inline
                if (inlineView && productIndex < 4) {
                    await showProductDetailInline(postId, productId, productIndex);
                } else {
                    // Các sản phẩm sau (>= 4) hoặc không có inline view thì dùng modal
                    if (window.loadProductModal) {
                        window.loadProductModal(postId, productId || null, productIndex);
                    } else {
                        console.error('loadProductModal function not found. Make sure product-modal.js is loaded.');
                        alert('Chức năng xem chi tiết sản phẩm đang được tải. Vui lòng thử lại sau.');
                    }
                }
            }
        });

        // Like button handlers
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

        // Comment button handlers
        document.querySelectorAll('.comment-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                const commentsSection = document.querySelector(`.comments-section[data-post-id="${postId}"]`);
                if (commentsSection) {
                    commentsSection.classList.toggle('hidden');
                }
            });
        });
    });

    // Function to show product detail inline
    async function showProductDetailInline(postId, productId, productIndex) {
        const token = localStorage.getItem('jwt_token');
        const inlineContainer = document.getElementById(`product-detail-inline-${postId}`);
        
        if (!inlineContainer) {
            console.error('Inline container not found');
            return;
        }

        // Show loading
        inlineContainer.innerHTML = '<div class="text-center py-4"><p class="text-gray-500">Đang tải...</p></div>';
        inlineContainer.classList.remove('hidden');

        try {
            const response = await fetch(`/api/posts/${postId}`, {
                headers: {
                    'Authorization': token ? `Bearer ${token}` : '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load post');
            }

            const post = await response.json();
            const products = post.products || [];
            
            if (products.length === 0) {
                inlineContainer.innerHTML = '<div class="text-center py-4"><p class="text-gray-500">Không có sản phẩm</p></div>';
                return;
            }

            // Find product by ID or use index
            let currentIndex = productIndex;
            if (productId) {
                currentIndex = products.findIndex(p => p.id == productId);
                if (currentIndex === -1) currentIndex = 0;
            }

            const product = products[currentIndex];
            if (!product) {
                inlineContainer.innerHTML = '<div class="text-center py-4"><p class="text-gray-500">Không tìm thấy sản phẩm</p></div>';
                return;
            }

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
                    <div>
                        ${imageHtml}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold mb-2 text-gray-900">${escapeHtml(product.name)}</h2>
                        <p class="text-3xl font-bold text-blue-600 mb-4">
                            ${new Intl.NumberFormat('vi-VN').format(product.price)} đ
                        </p>
                        <p class="text-gray-700 mb-4">${escapeHtml(product.description || 'Không có mô tả')}</p>
                        <div class="space-y-2 mb-4">
                            <p><span class="font-semibold">Số lượng:</span> ${product.quantity}</p>
                            <p><span class="font-semibold">Danh mục:</span> ${product.category?.name || 'Chưa phân loại'}</p>
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

            // Attach event listeners
            const closeBtn = inlineContainer.querySelector('.close-inline-product');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    inlineContainer.classList.add('hidden');
                });
            }

            const prevBtn = inlineContainer.querySelector('.prev-inline-product');
            if (prevBtn && !prevBtn.disabled) {
                prevBtn.addEventListener('click', () => {
                    const newIndex = parseInt(prevBtn.dataset.productIndex);
                    showProductDetailInline(postId, null, newIndex);
                });
            }

            const nextBtn = inlineContainer.querySelector('.next-inline-product');
            if (nextBtn && !nextBtn.disabled) {
                nextBtn.addEventListener('click', () => {
                    const newIndex = parseInt(nextBtn.dataset.productIndex);
                    showProductDetailInline(postId, null, newIndex);
                });
            }

            const addToCartBtn = inlineContainer.querySelector('.add-to-cart-inline-btn');
            if (addToCartBtn && !addToCartBtn.disabled) {
                addToCartBtn.addEventListener('click', async function() {
                    const productId = this.dataset.productId;
                    const token = localStorage.getItem('jwt_token');
                    
                    if (!token) {
                        alert('Vui lòng đăng nhập để thêm vào giỏ hàng');
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
                                product_id: parseInt(productId),
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
                        alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
                    }
                });
            }

            // Scroll to inline view
            inlineContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        } catch (error) {
            console.error('Load product error:', error);
            inlineContainer.innerHTML = '<div class="text-center py-4"><p class="text-red-500">Không thể tải thông tin sản phẩm</p></div>';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endsection

