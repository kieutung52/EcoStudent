@php
    // Check if user is logged in via JWT token (stored in localStorage)
    // We'll check this in JavaScript instead
    $products = $post->products;
    $productCount = $products->count();
    $displayProducts = $products->take(4); // Chỉ hiển thị 4 sản phẩm đầu
    $remainingCount = max(0, $productCount - 4);
    $isLiked = false; // Will be set via JavaScript
@endphp

<div class="bg-white rounded-lg shadow-md mb-4 overflow-hidden">
    <!-- Post Header - Account Info -->
    <div class="p-4 border-b">
        <div class="flex items-center space-x-3">
            <img src="{{ $post->user->avatar ? asset('storage/' . $post->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($post->user->name) }}" 
                 alt="{{ $post->user->name }}" 
                 class="w-10 h-10 rounded-full object-cover">
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900">{{ $post->user->name }}</h3>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <span>{{ $post->created_at->diffForHumans() }}</span>
                    @if($post->university)
                        <span>•</span>
                        <span>{{ $post->university->name }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Post Title -->
    <div class="p-4 pb-2">
        <h2 class="text-lg font-semibold text-gray-900">{{ $post->title }}</h2>
        @if($post->content)
            <p class="text-gray-700 mt-2">{{ $post->content }}</p>
        @endif
    </div>

    <!-- Products Grid (Facebook style) -->
    @if($productCount > 0)
        <div class="px-4 pb-4">
            <div class="grid grid-cols-2 gap-2 rounded-lg overflow-hidden" style="max-height: 650px;">
                @foreach($displayProducts as $index => $product)
                    <div class="relative group cursor-pointer product-item" 
                         data-product-id="{{ $product->id }}"
                         data-post-id="{{ $post->id }}"
                         data-product-index="{{ $index }}"
                         data-inline-view="true">
                        <div class="aspect-square bg-gray-200 relative overflow-hidden rounded-lg">
                            @if($product->image)
                                <img src="{{ asset($product->image) }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Product Name - Cải thiện để dễ nhìn hơn -->
                            <div class="absolute top-2 left-2 right-2 z-10">
                                <div class="bg-black/80 backdrop-blur-sm px-2 py-1.5 rounded-md shadow-lg border border-white/20">
                                    <p class="text-white text-xs font-semibold truncate drop-shadow-md">{{ $product->name }}</p>
                                </div>
                            </div>

                            <!-- Price Overlay - Cải thiện để dễ nhìn hơn -->
                            <div class="absolute bottom-0 left-0 right-0 z-10">
                                <div class="bg-gradient-to-t from-black/90 via-black/80 to-transparent p-2.5">
                                    <div class="bg-black/60 backdrop-blur-sm px-2 py-1 rounded-md shadow-lg border border-white/20 inline-block">
                                        <p class="text-white font-bold text-sm drop-shadow-md">{{ number_format($product->price, 0, ',', '.') }} đ</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Sold Out Overlay -->
                            @if($product->quantity === 0 || $product->is_sold)
                                <div class="absolute inset-0 flex items-center justify-center bg-gray-200 bg-opacity-50 z-10">
                                    <span class="bg-red-600 text-white px-2 py-1 rounded text-xs font-bold transform -rotate-12 shadow-md">HẾT HÀNG</span>
                                </div>
                            @endif

                            <!-- Overlay cho sản phẩm thứ 4 nếu có > 4 sản phẩm -->
                            @if($index === 3 && $remainingCount > 0)
                                <div class="absolute inset-0 bg-black/60 flex items-center justify-center z-20 rounded-lg">
                                    <div class="text-center text-white">
                                        <p class="text-3xl font-bold drop-shadow-lg">+{{ $remainingCount }}</p>
                                        <p class="text-sm font-medium drop-shadow-md">sản phẩm</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Product Detail Inline View (sẽ được hiển thị khi click vào sản phẩm) -->
            <div id="product-detail-inline-{{ $post->id }}" class="hidden mt-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                <!-- Nội dung sẽ được load bằng JavaScript -->
            </div>
        </div>
    @endif

    <!-- Post Actions -->
    <div class="px-4 py-3 border-t">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <!-- Like Button -->
                <button class="flex items-center space-x-2 text-gray-600 hover:text-blue-600 like-btn" 
                        data-post-id="{{ $post->id }}"
                        data-liked="{{ $isLiked ? 'true' : 'false' }}">
                    <svg class="w-5 h-5 {{ $isLiked ? 'text-blue-600 fill-current' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span class="like-count">{{ $post->likes->count() }}</span>
                </button>

                <!-- Comment Button -->
                <button class="flex items-center space-x-2 text-gray-600 hover:text-blue-600 comment-btn" 
                        data-post-id="{{ $post->id }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span>{{ $post->comments->count() }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Comments Section (Collapsible) -->
    <div class="px-4 pb-4 comments-section hidden" data-post-id="{{ $post->id }}">
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @foreach($post->comments->take(5) as $comment)
                <div class="flex space-x-2">
                    <img src="{{ $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name) }}" 
                         alt="{{ $comment->user->name }}" 
                         class="w-8 h-8 rounded-full">
                    <div class="flex-1 bg-gray-100 rounded-lg px-3 py-2">
                        <p class="font-semibold text-sm">{{ $comment->user->name }}</p>
                        <p class="text-sm text-gray-700">{{ $comment->content }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>


