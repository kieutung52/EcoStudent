@extends('layouts.app')

@section('title', 'Đăng bài - EcoStudent')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">Đăng bài mới</h1>
        <p class="text-gray-600">Chia sẻ đồ dùng học tập, sách vở của bạn với cộng đồng sinh viên</p>
    </div>

    <!-- Rules Warning Section -->
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-lg">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-yellow-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Lưu ý quan trọng về luật lệ đăng bài</h3>
                <p class="text-yellow-700 mb-3">Vui lòng đọc kỹ các quy định dưới đây. Vi phạm luật lệ có thể dẫn đến cảnh báo hoặc khóa tài khoản.</p>
                <div id="rules-list" class="space-y-2 max-h-60 overflow-y-auto bg-white p-4 rounded border border-yellow-200">
                    <p class="text-gray-500 text-center">Đang tải luật lệ...</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <form id="create-post-form" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề *</label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nội dung</label>
                <textarea id="content" 
                          name="content" 
                          rows="4"
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Trường đại học *</label>
                <select id="university_id" 
                        name="university_id" 
                        required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Chọn trường đại học</option>
                    @foreach($universities as $university)
                        <option value="{{ $university->id }}">{{ $university->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sản phẩm *</label>
                <div id="products-container" class="space-y-4">
                    <div class="product-item border rounded-lg p-4 bg-gray-50">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-gray-800">Sản phẩm 1</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tên sản phẩm *</label>
                                <input type="text" 
                                       name="products[0][name]" 
                                       required
                                       class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Giá (đ) *</label>
                                <input type="number" 
                                       name="products[0][price]" 
                                       required
                                       min="0"
                                       class="w-full px-4 py-2 border rounded-lg">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng *</label>
                                <input type="number" 
                                       name="products[0][quantity]" 
                                       required
                                       min="1"
                                       class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Danh mục</label>
                                <select name="products[0][category_id]" 
                                        class="w-full px-4 py-2 border rounded-lg">
                                    <option value="">Chọn danh mục</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                            <textarea name="products[0][description]" 
                                      rows="2"
                                      class="w-full px-4 py-2 border rounded-lg"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ảnh sản phẩm</label>
                            <input type="file" 
                                   name="products[0][image]" 
                                   accept="image/*"
                                   class="w-full px-4 py-2 border rounded-lg">
                        </div>
                    </div>
                </div>
                <button type="button" 
                        id="add-product-btn"
                        class="mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Thêm sản phẩm</span>
                </button>
            </div>

            <div class="flex space-x-4 mt-6">
                <button type="submit" 
                        class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Đăng bài
                </button>
                <a href="{{ route('home') }}" 
                   class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let productCount = 1;

// Load rules
async function loadRules() {
    try {
        const response = await fetch('/api/rules');
        if (response.ok) {
            const rules = await response.json();
            renderRules(rules);
        }
    } catch (error) {
        console.error('Load rules error:', error);
    }
}

function renderRules(rules) {
    const container = document.getElementById('rules-list');
    if (rules.length === 0) {
        container.innerHTML = '<p class="text-gray-500">Chưa có luật lệ nào</p>';
        return;
    }

    let html = '<ol class="list-decimal list-inside space-y-2">';
    rules.forEach((rule, index) => {
        html += `
            <li class="text-sm text-gray-700">
                <span class="font-medium">${escapeHtml(rule.title)}:</span>
                <span>${escapeHtml(rule.content)}</span>
            </li>
        `;
    });
    html += '</ol>';
    container.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load rules on page load
loadRules();

document.getElementById('add-product-btn').addEventListener('click', function() {
    const container = document.getElementById('products-container');
    const categories = @json($categories);
    
    const html = `
        <div class="product-item border rounded-lg p-4 bg-gray-50">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Sản phẩm ${productCount + 1}</h3>
                <button type="button" class="remove-product bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600 transition-colors text-sm">Xóa</button>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tên sản phẩm *</label>
                    <input type="text" 
                           name="products[${productCount}][name]" 
                           required
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Giá (đ) *</label>
                    <input type="number" 
                           name="products[${productCount}][price]" 
                           required
                           min="0"
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng *</label>
                    <input type="number" 
                           name="products[${productCount}][quantity]" 
                           required
                           min="1"
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Danh mục</label>
                    <select name="products[${productCount}][category_id]" 
                            class="w-full px-4 py-2 border rounded-lg">
                        <option value="">Chọn danh mục</option>
                        ${categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('')}
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                <textarea name="products[${productCount}][description]" 
                          rows="2"
                          class="w-full px-4 py-2 border rounded-lg"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ảnh sản phẩm</label>
                <input type="file" 
                       name="products[${productCount}][image]" 
                       accept="image/*"
                       class="w-full px-4 py-2 border rounded-lg">
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    productCount++;

    // Attach remove button handler
    container.querySelectorAll('.remove-product').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.product-item').remove();
        });
    });
});

document.getElementById('create-post-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        alert('Vui lòng đăng nhập để đăng bài');
        window.location.href = '/login';
        return;
    }

    const formData = new FormData(this);
    
    try {
        const response = await fetch('/api/posts', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        if (response.ok) {
            const result = await response.json();
            alert('Đăng bài thành công! Bài viết của bạn đang chờ admin duyệt. Bạn sẽ được thông báo khi bài viết được duyệt.');
            window.location.href = '/profile';
        } else {
            const error = await response.json();
            alert(error.message || 'Đăng bài thất bại');
        }
    } catch (error) {
        console.error('Create post error:', error);
        alert('Có lỗi xảy ra');
    }
});
</script>
@endsection

