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
    // Auth handling
    const token = localStorage.getItem('jwt_token');
    const user = JSON.parse(localStorage.getItem('user') || 'null');

    // Update navigation based on auth status
    function updateNavigation() {
        const token = localStorage.getItem('jwt_token');
        const user = JSON.parse(localStorage.getItem('user') || 'null');

        if (token && user) {
            document.getElementById('auth-buttons')?.classList.add('hidden');
            document.getElementById('user-menu')?.classList.remove('hidden');
            
            // Show navigation items
            document.getElementById('nav-cart')?.classList.remove('hidden');
            document.getElementById('nav-my-orders')?.classList.remove('hidden');
            document.getElementById('nav-sales-orders')?.classList.remove('hidden');
            document.getElementById('nav-create-post')?.classList.remove('hidden');
            document.getElementById('nav-profile')?.classList.remove('hidden');
            
            // Set navigation links
            document.getElementById('nav-cart').href = '/cart';
            document.getElementById('nav-my-orders').href = '/my-orders';
            document.getElementById('nav-sales-orders').href = '/sales-orders';
            document.getElementById('nav-create-post').href = '/posts/create';
            document.getElementById('nav-profile').href = '/profile';
            
            const avatarEl = document.getElementById('user-avatar');
            const nameEl = document.getElementById('user-name');
            if (avatarEl) avatarEl.src = user.avatar ? `/storage/${user.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}`;
            if (nameEl) nameEl.textContent = user.name;
        } else {
            document.getElementById('auth-buttons')?.classList.remove('hidden');
            document.getElementById('user-menu')?.classList.add('hidden');
            
            // Hide navigation items
            document.getElementById('nav-cart')?.classList.add('hidden');
            document.getElementById('nav-my-orders')?.classList.add('hidden');
            document.getElementById('nav-sales-orders')?.classList.add('hidden');
            document.getElementById('nav-create-post')?.classList.add('hidden');
            document.getElementById('nav-profile')?.classList.add('hidden');
        }
    }

    updateNavigation();

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

    // Product click handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Product items click
        document.querySelectorAll('.product-item, .product-item-more').forEach(item => {
            item.addEventListener('click', function() {
                const postId = this.dataset.postId;
                const productId = this.dataset.productId;
                const productIndex = parseInt(this.dataset.productIndex || this.dataset.startIndex || 0);
                
                if (window.loadProductModal) {
                    window.loadProductModal(postId, productId || null, productIndex);
                }
            });
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

