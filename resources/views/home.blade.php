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
        document.body.addEventListener('click', function(e) {
            const productItem = e.target.closest('.product-item, .product-item-more');
            if (productItem) {
                e.preventDefault();
                e.stopPropagation();
                
                const postId = productItem.dataset.postId;
                const productId = productItem.dataset.productId;
                const productIndex = parseInt(productItem.dataset.productIndex || productItem.dataset.startIndex || 0);
                
                console.log('Product clicked:', { postId, productId, productIndex });
                
                if (window.loadProductModal) {
                    window.loadProductModal(postId, productId || null, productIndex);
                } else {
                    console.error('loadProductModal function not found. Make sure product-modal.js is loaded.');
                    alert('Chức năng xem chi tiết sản phẩm đang được tải. Vui lòng thử lại sau.');
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
</script>
@endsection

