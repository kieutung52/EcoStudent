// Product Modal Handler
let currentPostData = null;
let currentProductIndex = 0;

async function loadProductModal(postId, productId, productIndex) {
    const token = localStorage.getItem('jwt_token');
    
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
        
        if (products.length === 0) return;

        // Find product by ID or use index
        let currentIndex = productIndex;
        if (productId) {
            currentIndex = products.findIndex(p => p.id == productId);
            if (currentIndex === -1) currentIndex = 0;
        }

        currentPostData = { postId, products };
        currentProductIndex = currentIndex;
        
        showProductModal(products[currentIndex], currentIndex, products.length);
    } catch (error) {
        console.error('Load product error:', error);
        alert('Không thể tải thông tin sản phẩm');
    }
}

function showProductModal(product, index, total) {
    const modal = document.getElementById('product-modal');
    const modalContent = document.getElementById('modal-content');
    
    const imageUrl = product.image 
        ? `/storage/${product.image}` 
        : null;
    
    const imageHtml = imageUrl 
        ? `<img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full rounded-lg object-cover" style="max-height: 500px;">`
        : `<div class="w-full h-full flex items-center justify-center bg-gray-100 rounded-lg" style="min-height: 500px;">
            <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>`;
    
    modalContent.innerHTML = `
        <div class="grid grid-cols-2 gap-6">
            <div>
                ${imageHtml}
            </div>
            <div>
                <h2 class="text-2xl font-bold mb-4">${escapeHtml(product.name)}</h2>
                <p class="text-3xl font-bold text-blue-600 mb-4">
                    ${new Intl.NumberFormat('vi-VN').format(product.price)} đ
                </p>
                <p class="text-gray-700 mb-4">${escapeHtml(product.description || 'Không có mô tả')}</p>
                <div class="space-y-2 mb-4">
                    <p><span class="font-semibold">Số lượng:</span> ${product.quantity}</p>
                    <p><span class="font-semibold">Danh mục:</span> ${product.category?.name || 'Chưa phân loại'}</p>
                    <p><span class="font-semibold">Tình trạng:</span> ${product.is_sold ? 'Đã bán' : 'Còn hàng'}</p>
                </div>
                <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 add-to-cart-btn" 
                        data-product-id="${product.id}"
                        ${product.is_sold || product.quantity === 0 ? 'disabled class="bg-gray-400 cursor-not-allowed"' : ''}>
                    ${product.is_sold || product.quantity === 0 ? 'Đã hết hàng' : 'Thêm vào giỏ hàng'}
                </button>
            </div>
        </div>
    `;

    // Update navigation buttons
    const prevBtn = document.getElementById('prev-product');
    const nextBtn = document.getElementById('next-product');
    
    prevBtn.style.display = index > 0 ? 'block' : 'none';
    nextBtn.style.display = index < total - 1 ? 'block' : 'none';

    // Update button handlers
    prevBtn.onclick = () => {
        if (index > 0 && currentPostData) {
            loadProductModal(currentPostData.postId, null, index - 1);
        }
    };

    nextBtn.onclick = () => {
        if (index < total - 1 && currentPostData) {
            loadProductModal(currentPostData.postId, null, index + 1);
        }
    };

    // Add to cart handler
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
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

    modal.classList.remove('hidden');
}

// Close modal
document.getElementById('close-modal')?.addEventListener('click', () => {
    document.getElementById('product-modal').classList.add('hidden');
    currentPostData = null;
    currentProductIndex = 0;
});

// Close on background click
document.getElementById('product-modal')?.addEventListener('click', (e) => {
    if (e.target.id === 'product-modal') {
        document.getElementById('product-modal').classList.add('hidden');
        currentPostData = null;
        currentProductIndex = 0;
    }
});

// Escape key to close
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('product-modal');
        if (!modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            currentPostData = null;
            currentProductIndex = 0;
        }
    }
});

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Export for use in other scripts
window.loadProductModal = loadProductModal;

