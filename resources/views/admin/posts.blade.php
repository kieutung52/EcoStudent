@extends('layouts.admin')

@section('title', 'Duyệt bài viết - EcoStudent')

@section('content')
<div>
    <h1 class="text-3xl font-bold mb-6">Duyệt bài viết</h1>

    <div class="mb-4">
        <select id="filter-status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="pending">Chờ duyệt</option>
            <option value="approved">Đã duyệt</option>
            <option value="rejected">Đã từ chối</option>
            <option value="">Tất cả</option>
        </select>
    </div>

    <div id="posts-container" class="space-y-4">
        <p class="text-center text-gray-500 py-8">Đang tải...</p>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-semibold mb-4">Từ chối bài viết</h2>
        <form id="reject-form">
            <input type="hidden" id="reject-post-id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Chọn luật vi phạm *</label>
                <div id="rules-checkboxes" class="space-y-2 max-h-60 overflow-y-auto border rounded-lg p-4">
                    <p class="text-gray-500">Đang tải luật lệ...</p>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú thêm (tùy chọn)</label>
                <textarea id="reject-note" 
                          rows="3"
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors">
                    Từ chối
                </button>
                <button type="button" id="cancel-reject-modal" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let posts = [];
let rules = [];

async function loadRules() {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch('/api/admin/rules', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            rules = await response.json();
        }
    } catch (error) {
        console.error('Load rules error:', error);
    }
}

async function loadPosts() {
    const token = localStorage.getItem('jwt_token');
    const status = document.getElementById('filter-status').value;
    
    let url = '/api/admin/posts?';
    if (status) {
        url += `status=${status}&`;
    }
    
    try {
        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            posts = data.data || data;
            renderPosts();
        }
    } catch (error) {
        console.error('Load posts error:', error);
    }
}

function renderPosts() {
    const container = document.getElementById('posts-container');
    
    if (posts.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">Không có bài viết nào</p>';
        return;
    }

    let html = '';
    posts.forEach(post => {
        const statusColors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'approved': 'bg-green-100 text-green-800',
            'rejected': 'bg-red-100 text-red-800',
            'hidden': 'bg-gray-100 text-gray-800'
        };

        const statusText = {
            'pending': 'Chờ duyệt',
            'approved': 'Đã duyệt',
            'rejected': 'Đã từ chối',
            'hidden': 'Đã ẩn'
        };

        html += `
            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold mb-2">${escapeHtml(post.title)}</h3>
                        <p class="text-sm text-gray-500 mb-1">Người đăng: ${escapeHtml(post.user?.name || 'N/A')}</p>
                        <p class="text-sm text-gray-500">Ngày đăng: ${new Date(post.created_at).toLocaleDateString('vi-VN')}</p>
                        ${post.content ? `<p class="text-gray-700 mt-2">${escapeHtml(post.content)}</p>` : ''}
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium ${statusColors[post.status] || 'bg-gray-100'}">
                        ${statusText[post.status] || post.status}
                    </span>
                </div>
                
                ${post.violations && post.violations.length > 0 ? `
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm font-semibold text-red-800 mb-2">Vi phạm:</p>
                        <ul class="list-disc list-inside space-y-1">
                            ${post.violations.map(v => `<li class="text-sm text-red-700">${escapeHtml(v.rule?.title || 'N/A')}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
                
                <div class="flex items-center space-x-2 mb-4">
                    <span class="text-sm text-gray-600">${post.products?.length || 0} sản phẩm</span>
                    <span>•</span>
                    <span class="text-sm text-gray-600">${post.view_count || 0} lượt xem</span>
                </div>
                
                ${post.status === 'pending' ? `
                    <div class="flex space-x-2">
                        <button class="approve-post bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors" data-post-id="${post.id}">
                            Duyệt
                        </button>
                        <button class="reject-post bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors" data-post-id="${post.id}">
                            Từ chối
                        </button>
                        <a href="/posts/${post.id}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            Xem chi tiết
                        </a>
                    </div>
                ` : `
                    <a href="/posts/${post.id}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Xem chi tiết
                    </a>
                `}
            </div>
        `;
    });

    container.innerHTML = html;

    // Attach event listeners
    document.querySelectorAll('.approve-post').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            approvePost(postId);
        });
    });

    document.querySelectorAll('.reject-post').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            openRejectModal(postId);
        });
    });
}

function openRejectModal(postId) {
    document.getElementById('reject-post-id').value = postId;
    
    // Render rules checkboxes
    const container = document.getElementById('rules-checkboxes');
    if (rules.length === 0) {
        container.innerHTML = '<p class="text-gray-500">Chưa có luật lệ nào</p>';
    } else {
        container.innerHTML = rules.map(rule => `
            <label class="flex items-start space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                <input type="checkbox" name="rule_ids[]" value="${rule.id}" class="mt-1">
                <div>
                    <p class="font-medium text-sm">${escapeHtml(rule.title)}</p>
                    <p class="text-xs text-gray-600">${escapeHtml(rule.content)}</p>
                </div>
            </label>
        `).join('');
    }
    
    document.getElementById('reject-modal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    document.getElementById('reject-form').reset();
}

async function approvePost(postId) {
    if (!confirm('Bạn có chắc muốn duyệt bài viết này?')) {
        return;
    }

    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/admin/posts/${postId}/approve`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            alert('Đã duyệt bài viết thành công!');
            await loadPosts();
        } else {
            const error = await response.json();
            alert(error.message || 'Duyệt thất bại');
        }
    } catch (error) {
        console.error('Approve post error:', error);
        alert('Có lỗi xảy ra');
    }
}

document.getElementById('reject-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const postId = document.getElementById('reject-post-id').value;
    const note = document.getElementById('reject-note').value;
    const selectedRules = Array.from(document.querySelectorAll('input[name="rule_ids[]"]:checked'))
        .map(cb => parseInt(cb.value));
    
    if (selectedRules.length === 0) {
        alert('Vui lòng chọn ít nhất một luật vi phạm');
        return;
    }

    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/admin/posts/${postId}/reject`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                rule_ids: selectedRules,
                note: note
            })
        });

        if (response.ok) {
            alert('Đã từ chối bài viết thành công!');
            closeRejectModal();
            await loadPosts();
        } else {
            const error = await response.json();
            alert(error.message || 'Từ chối thất bại');
        }
    } catch (error) {
        console.error('Reject post error:', error);
        alert('Có lỗi xảy ra');
    }
});

document.getElementById('cancel-reject-modal').addEventListener('click', closeRejectModal);
document.getElementById('filter-status').addEventListener('change', loadPosts);

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load on page load
loadRules();
loadPosts();
</script>
@endsection

