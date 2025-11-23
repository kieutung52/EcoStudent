@extends('layouts.app')

@section('title', 'Trang chủ - EcoStudent')

@section('content')
<div class="flex gap-6">
    <!-- Sidebar Left (Filters) -->
    <aside class="w-64 bg-white rounded-lg shadow-lg p-4 h-fit sticky top-20">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Bộ lọc</h2>
            <button id="clear-filters" class="text-sm text-blue-600 hover:underline">Xóa bộ lọc</button>
        </div>
        
        <!-- Search -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm</label>
            <input type="text" id="search-input" placeholder="Từ khóa..." 
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
        </div>

        <!-- Sort By -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Sắp xếp</label>
            <select id="sort-by" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="newest">Mới nhất</option>
                <option value="oldest">Cũ nhất</option>
                <option value="most_viewed">Xem nhiều nhất</option>
                <option value="most_liked">Nhiều like nhất</option>
            </select>
        </div>

        <!-- University Filter -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Trường Đại học</label>
            <select id="university-filter" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="">Tất cả</option>
                @foreach($universities as $university)
                    <option value="{{ $university->id }}">{{ $university->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Category Filter -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Danh mục</label>
            <select id="category-filter" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="">Tất cả</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Price Range -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Khoảng giá</label>
            <div class="flex items-center space-x-2">
                <input type="number" id="price-min" placeholder="Min" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <span class="text-gray-500">-</span>
                <input type="number" id="price-max" placeholder="Max" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
        </div>

        <button id="apply-filters" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Áp dụng
        </button>
    </aside>

    <!-- Main Feed -->
    <div class="flex-1 max-w-2xl">
        <div id="posts-container">
            @foreach($posts as $post)
                @if ($post->status === 'pending' || $post->status === 'rejected' || $post->status === 'hidden')
                    @continue
                @endif
                @include('partials.post-card', ['post' => $post])
            @endforeach
        </div>

        <!-- Pagination -->
        <div id="pagination-container" class="mt-6">
            {{ $posts->links() }}
        </div>
        
        <!-- Loading Indicator -->
        <div id="loading-indicator" class="hidden text-center py-4">
            <p class="text-gray-500">Đang tải...</p>
        </div>
    </div>

    <!-- Sidebar Right (Optional) -->
    <aside class="w-64">
        <!-- Có thể thêm thông tin khác ở đây -->
    </aside>
</div>
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
    // ... (Existing variables) ...
    let reportRulesLoaded = false;

    // ... (Existing code) ...

    // Report Modal Logic
    async function openReportModal(postId) {
        document.getElementById('report-post-id').value = postId;
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
        
        const postId = document.getElementById('report-post-id').value;
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
            const response = await fetch(`/api/posts/${postId}/reports`, {
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

    // ... (Existing attachPostEventListeners) ...
    function attachPostEventListeners() {
        // ... (Existing listeners) ...

        // Report buttons
        document.querySelectorAll('.report-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                openReportModal(postId);
            });
        });
    }

    // ... (Rest of existing code) ...

    const searchInput = document.getElementById('search-input');
    const sortBySelect = document.getElementById('sort-by');
    const universitySelect = document.getElementById('university-filter');
    const categorySelect = document.getElementById('category-filter');
    const priceMinInput = document.getElementById('price-min');
    const priceMaxInput = document.getElementById('price-max');
    const applyBtn = document.getElementById('apply-filters');
    const clearBtn = document.getElementById('clear-filters');
    const postsContainer = document.getElementById('posts-container');
    const paginationContainer = document.getElementById('pagination-container');
    const loadingIndicator = document.getElementById('loading-indicator');

    // Apply filters
    applyBtn.addEventListener('click', () => {
        currentPage = 1;
        isFilterActive = true;
        loadPosts();
    });

    // Clear filters
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        sortBySelect.value = 'newest';
        universitySelect.value = '';
        categorySelect.value = '';
        priceMinInput.value = '';
        priceMaxInput.value = '';
        
        currentPage = 1;
        isFilterActive = false;
        
        // Reload page to reset SSR state or just load posts
        // loadPosts();
        window.location.href = '/';
    });

    // Search on Enter
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            currentPage = 1;
            isFilterActive = true;
            loadPosts();
        }
    });

    async function loadPosts(page = 1) {
        // Show loading
        loadingIndicator.classList.remove('hidden');
        postsContainer.style.opacity = '0.5';

        const params = new URLSearchParams();
        params.append('page', page);
        
        if (searchInput.value) params.append('keyword', searchInput.value);
        if (sortBySelect.value) params.append('sort_by', sortBySelect.value);
        if (universitySelect.value) params.append('university_id', universitySelect.value);
        if (categorySelect.value) params.append('category_id', categorySelect.value);
        if (priceMinInput.value) params.append('price_min', priceMinInput.value);
        if (priceMaxInput.value) params.append('price_max', priceMaxInput.value);

        try {
            const response = await fetch(`/api/posts?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                renderPosts(data.data);
                renderPagination(data);
                currentPage = data.current_page;
                
                // Update URL without reload
                const newUrl = `${window.location.pathname}?${params.toString()}`;
                window.history.pushState({path: newUrl}, '', newUrl);
            }
        } catch (error) {
            console.error('Load posts error:', error);
        } finally {
            loadingIndicator.classList.add('hidden');
            postsContainer.style.opacity = '1';
        }
    }

    function renderPosts(posts) {
        if (posts.length === 0) {
            postsContainer.innerHTML = '<div class="text-center py-8 text-gray-500">Không tìm thấy bài viết nào</div>';
            return;
        }

        postsContainer.innerHTML = posts.map(post => renderPostCard(post)).join('');
        
        // Re-attach event listeners for new elements
        attachPostEventListeners();
    }

    function renderPostCard(post) {
        const userAvatar = post.user?.avatar 
            ? `/storage/${post.user.avatar}` 
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user?.name || 'User')}`;
        
        const timeAgo = new Date(post.created_at).toLocaleDateString('vi-VN'); // Simplification
        
        let productsHtml = '';
        if (post.products && post.products.length > 0) {
            const displayProducts = post.products.slice(0, 4);
            const remainingCount = Math.max(0, post.products.length - 4);
            
            const productGrid = displayProducts.map((product, index) => {
                const imageUrl = product.image 
                    ? (product.image.startsWith('http') ? product.image : `/${product.image}`)
                    : null;
                
                const imageHtml = imageUrl 
                    ? `<img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">`
                    : `<div class="w-full h-full flex items-center justify-center bg-gray-100">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>`;

                const isSoldOut = product.quantity === 0 || product.is_sold;

                return `
                    <div class="relative group cursor-pointer product-item" 
                         data-product-id="${product.id}"
                         data-post-id="${post.id}"
                         data-product-index="${index}"
                         data-inline-view="true">
                        <div class="aspect-square bg-gray-200 relative overflow-hidden rounded-lg">
                            ${imageHtml}
                            
                            <div class="absolute top-2 left-2 right-2 z-10">
                                <div class="bg-black/80 backdrop-blur-sm px-2 py-1.5 rounded-md shadow-lg border border-white/20">
                                    <p class="text-white text-xs font-semibold truncate drop-shadow-md">${escapeHtml(product.name)}</p>
                                </div>
                            </div>

                            <div class="absolute bottom-0 left-0 right-0 z-10">
                                <div class="bg-gradient-to-t from-black/90 via-black/80 to-transparent p-2.5">
                                    <div class="bg-black/60 backdrop-blur-sm px-2 py-1 rounded-md shadow-lg border border-white/20 inline-block">
                                        <p class="text-white font-bold text-sm drop-shadow-md">${new Intl.NumberFormat('vi-VN').format(product.price)} đ</p>
                                    </div>
                                </div>
                            </div>

                            ${isSoldOut ? `
                                <div class="absolute inset-0 flex items-center justify-center bg-gray-200 bg-opacity-50 z-10">
                                    <span class="bg-red-600 text-white px-2 py-1 rounded text-xs font-bold transform -rotate-12 shadow-md">HẾT HÀNG</span>
                                </div>
                            ` : ''}

                            ${index === 3 && remainingCount > 0 ? `
                                <div class="absolute inset-0 bg-black/60 flex items-center justify-center z-20 rounded-lg">
                                    <div class="text-center text-white">
                                        <p class="text-3xl font-bold drop-shadow-lg">+${remainingCount}</p>
                                        <p class="text-sm font-medium drop-shadow-md">sản phẩm</p>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            productsHtml = `
                <div class="px-4 pb-4">
                    <div class="grid grid-cols-2 gap-2 rounded-lg overflow-hidden" style="max-height: 650px;">
                        ${productGrid}
                    </div>
                    <div id="product-detail-inline-${post.id}" class="hidden mt-4 bg-gray-50 rounded-lg p-4 border border-gray-200"></div>
                </div>
            `;
        }

        const isLiked = post.likes && post.likes.some(like => like.user_id == (currentUser?.id || 0));

        return `
            <div class="bg-white rounded-lg shadow-md mb-4 overflow-hidden">
                <div class="p-4 border-b">
                    <div class="flex items-center space-x-3">
                        <img src="${userAvatar}" alt="${escapeHtml(post.user?.name || 'User')}" class="w-10 h-10 rounded-full object-cover">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">${escapeHtml(post.user?.name || 'User')}</h3>
                            <div class="flex items-center space-x-2 text-sm text-gray-500">
                                <span>${timeAgo}</span>
                                ${post.university ? `<span>•</span><span>${escapeHtml(post.university.name)}</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 pb-2">
                    <h2 class="text-lg font-semibold text-gray-900">${escapeHtml(post.title)}</h2>
                    ${post.content ? `<p class="text-gray-700 mt-2">${escapeHtml(post.content)}</p>` : ''}
                </div>

                ${productsHtml}

                <div class="px-4 py-3 border-t">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-6">
                            <button class="flex items-center space-x-2 text-gray-600 hover:text-blue-600 like-btn" 
                                    data-post-id="${post.id}"
                                    data-liked="${isLiked ? 'true' : 'false'}">
                                <svg class="w-5 h-5 ${isLiked ? 'text-blue-600 fill-current' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span class="like-count">${post.likes?.length || 0}</span>
                            </button>

                            <button class="flex items-center space-x-2 text-gray-600 hover:text-blue-600 comment-btn" 
                                    data-post-id="${post.id}"
                                    data-user-id="${post.user_id}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span>Đánh giá</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-4 pb-4 reviews-section hidden" data-post-id="${post.id}">
                    <h4 class="font-semibold text-sm mb-2 text-gray-700">Đánh giá về người bán</h4>
                    <div class="reviews-list space-y-3 max-h-64 overflow-y-auto">
                        <p class="text-center text-gray-500 text-sm py-2">Đang tải đánh giá...</p>
                    </div>
                </div>
            </div>
        `;
    }

    function renderPagination(data) {
        if (data.last_page <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '<div class="flex justify-center space-x-2">';
        
        // Prev
        if (data.current_page > 1) {
            html += `<button onclick="loadPosts(${data.current_page - 1})" class="px-3 py-1 border rounded hover:bg-gray-100">Previous</button>`;
        }

        // Pages (Simplified)
        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                html += `<span class="px-3 py-1 border rounded bg-blue-600 text-white">${i}</span>`;
            } else {
                html += `<button onclick="loadPosts(${i})" class="px-3 py-1 border rounded hover:bg-gray-100">${i}</button>`;
            }
        }

        // Next
        if (data.current_page < data.last_page) {
            html += `<button onclick="loadPosts(${data.current_page + 1})" class="px-3 py-1 border rounded hover:bg-gray-100">Next</button>`;
        }

        html += '</div>';
        paginationContainer.innerHTML = html;
    }

    // Attach event listeners for dynamically created elements
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

        // Comment buttons (Now triggers Reviews)
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


        // Report buttons
        document.querySelectorAll('.report-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.dataset.postId;
                openReportModal(postId);
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
                const reviews = data.data; // Pagination result
                
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

    // Initial attachment for SSR content
    document.addEventListener('DOMContentLoaded', function() {
        attachPostEventListeners();
        
        // Product click delegation (keep existing logic)
        document.body.addEventListener('click', async function(e) {
            const productItem = e.target.closest('.product-item, .product-item-more');
            if (productItem) {
                e.preventDefault();
                e.stopPropagation();
                
                const postId = productItem.dataset.postId;
                const productId = productItem.dataset.productId;
                const productIndex = parseInt(productItem.dataset.productIndex || productItem.dataset.startIndex || 0);
                const inlineView = productItem.dataset.inlineView === 'true';
                
                if (inlineView && productIndex < 4) {
                    await showProductDetailInline(postId, productId, productIndex);
                } else {
                    if (window.loadProductModal) {
                        window.loadProductModal(postId, productId || null, productIndex);
                    }
                }
            }
        });
    });

    // Reuse showProductDetailInline from previous code
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

            // Attach inline listeners (close, prev, next, add-to-cart) - Simplified for brevity
            inlineContainer.querySelector('.close-inline-product').addEventListener('click', () => inlineContainer.classList.add('hidden'));
            
            const prevBtn = inlineContainer.querySelector('.prev-inline-product');
            if (prevBtn) prevBtn.addEventListener('click', () => showProductDetailInline(postId, null, parseInt(prevBtn.dataset.productIndex)));
            
            const nextBtn = inlineContainer.querySelector('.next-inline-product');
            if (nextBtn) nextBtn.addEventListener('click', () => showProductDetailInline(postId, null, parseInt(nextBtn.dataset.productIndex)));
            
            const addToCartBtn = inlineContainer.querySelector('.add-to-cart-inline-btn');
            if (addToCartBtn && !addToCartBtn.disabled) {
                addToCartBtn.addEventListener('click', async function() {
                    // Add to cart logic (same as before)
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

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    let currentUser = null;
    try {
        const userStr = localStorage.getItem('user');
        if (userStr) currentUser = JSON.parse(userStr);
    } catch (e) {}
</script>
@endsection

