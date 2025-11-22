@extends('layouts.app')

@section('title', 'Hồ sơ của tôi - EcoStudent')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center space-x-6 mb-6">
            <div class="relative">
                <img id="user-avatar-img" 
                     src="" 
                     alt="Avatar" 
                     class="w-24 h-24 rounded-full border-4 border-blue-500 object-cover">
            </div>
            <div class="flex-1">
                <h1 id="user-name-display" class="text-2xl font-bold text-gray-900 mb-2"></h1>
                <p id="user-email-display" class="text-gray-600 mb-1"></p>
                <p id="user-university-display" class="text-sm text-gray-500"></p>
            </div>
            <button id="edit-profile-btn" 
                    class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Cập nhật hồ sơ
            </button>
        </div>
    </div>

    <!-- Profile Edit Modal -->
    <div id="profile-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Cập nhật hồ sơ</h2>
                    <button id="close-profile-modal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="profile-form">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Họ và tên *</label>
                            <input type="text" 
                                   id="name" 
                                   name="name"
                                   required
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" 
                                   id="email" 
                                   name="email"
                                   disabled
                                   class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                            <input type="text" 
                                   id="phone" 
                                   name="phone"
                                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Trường đại học</label>
                            <select id="university_id" 
                                    name="university_id"
                                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Chọn trường</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex space-x-4">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Lưu thay đổi
                        </button>
                        <button type="button" 
                                id="cancel-profile-modal"
                                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            Hủy
                        </button>
                    </div>
                </form>

                <div class="mt-8 border-t pt-6">
                    <h3 class="text-xl font-semibold mb-4">Đổi mật khẩu</h3>
                    <form id="change-password-form">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu hiện tại *</label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password"
                                       required
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới *</label>
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password"
                                       required
                                       minlength="6"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu mới *</label>
                                <input type="password" 
                                       id="new_password_confirmation" 
                                       name="new_password_confirmation"
                                       required
                                       minlength="6"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" 
                                    class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- My Posts Section -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Bài đăng của tôi</h2>
            <a href="{{ route('posts.create') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                + Đăng bài mới
            </a>
        </div>

        <div id="my-posts-container" class="space-y-4">
            <p class="text-center text-gray-500 py-8">Đang tải...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let user = null;
let universities = [];

// Load user data
async function loadProfile() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {
        // Load user profile
        const userResponse = await fetch('/api/profile', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (userResponse.ok) {
            user = await userResponse.json();
            
            // Update display
            document.getElementById('user-name-display').textContent = user.name || 'Chưa có tên';
            document.getElementById('user-email-display').textContent = user.email || '';
            document.getElementById('user-university-display').textContent = user.university?.name || 'Chưa chọn trường';
            
            const avatarImg = document.getElementById('user-avatar-img');
            avatarImg.src = user.avatar ? `/storage/${user.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&size=96`;
            
            // Fill form
            document.getElementById('name').value = user.name || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('university_id').value = user.university_id || '';
        }

        // Load universities
        const uniResponse = await fetch('/api/universities');
        if (uniResponse.ok) {
            universities = await uniResponse.json();
            const select = document.getElementById('university_id');
            select.innerHTML = '<option value="">Chọn trường</option>';
            universities.forEach(uni => {
                const option = document.createElement('option');
                option.value = uni.id;
                option.textContent = uni.name;
                if (user && user.university_id == uni.id) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Load profile error:', error);
    }
}

// Load my posts
async function loadMyPosts() {
    const token = localStorage.getItem('jwt_token');
    const container = document.getElementById('my-posts-container');
    
    try {
        const response = await fetch('/api/my-posts', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            const posts = data.data || data;
            
            if (posts.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 mb-4">Bạn chưa có bài đăng nào</p>
                        <a href="{{ route('posts.create') }}" class="text-blue-600 hover:underline">Đăng bài ngay</a>
                    </div>
                `;
                return;
            }

            let html = '';
            posts.forEach(post => {
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

                html += `
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">${escapeHtml(post.title)}</h3>
                                <p class="text-sm text-gray-500">${new Date(post.created_at).toLocaleDateString('vi-VN')}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${statusColors[post.status] || 'bg-gray-100'}">
                                ${statusText[post.status] || post.status}
                            </span>
                        </div>
                        ${post.content ? `<p class="text-gray-700 mb-3">${escapeHtml(post.content)}</p>` : ''}
                        <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                            <span>${post.products?.length || 0} sản phẩm</span>
                            <span>•</span>
                            <span>${post.likes?.length || 0} lượt thích</span>
                            <span>•</span>
                            <span>${post.view_count || 0} lượt xem</span>
                        </div>
                        <div class="flex space-x-2">
                            <a href="/posts/${post.id}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm">
                                Xem chi tiết
                            </a>
                            <button class="edit-post px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm" data-post-id="${post.id}">
                                Sửa
                            </button>
                            <button class="delete-post px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm" data-post-id="${post.id}">
                                Xóa
                            </button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;

            // Attach event listeners
            document.querySelectorAll('.delete-post').forEach(btn => {
                btn.addEventListener('click', function() {
                    const postId = this.dataset.postId;
                    if (confirm('Bạn có chắc muốn xóa bài viết này?')) {
                        deletePost(postId);
                    }
                });
            });
        } else {
            container.innerHTML = '<p class="text-center text-red-500 py-8">Không thể tải bài đăng</p>';
        }
    } catch (error) {
        console.error('Load posts error:', error);
        container.innerHTML = '<p class="text-center text-red-500 py-8">Có lỗi xảy ra khi tải bài đăng</p>';
    }
}

async function deletePost(postId) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/posts/${postId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            alert('Đã xóa bài viết');
            loadMyPosts();
        } else {
            const error = await response.json();
            alert(error.message || 'Xóa thất bại');
        }
    } catch (error) {
        console.error('Delete post error:', error);
        alert('Có lỗi xảy ra');
    }
}

// Modal handlers
document.getElementById('edit-profile-btn')?.addEventListener('click', function() {
    document.getElementById('profile-modal').classList.remove('hidden');
});

document.getElementById('close-profile-modal')?.addEventListener('click', function() {
    document.getElementById('profile-modal').classList.add('hidden');
});

document.getElementById('cancel-profile-modal')?.addEventListener('click', function() {
    document.getElementById('profile-modal').classList.add('hidden');
});

// Update profile
document.getElementById('profile-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const token = localStorage.getItem('jwt_token');
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/profile', {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            const result = await response.json();
            localStorage.setItem('user', JSON.stringify(result.data));
            alert('Cập nhật profile thành công!');
            document.getElementById('profile-modal').classList.add('hidden');
            loadProfile();
        } else {
            const error = await response.json();
            alert(error.message || 'Cập nhật thất bại');
        }
    } catch (error) {
        console.error('Update profile error:', error);
        alert('Có lỗi xảy ra');
    }
});

// Change password
document.getElementById('change-password-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const token = localStorage.getItem('jwt_token');
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    if (data.new_password !== data.new_password_confirmation) {
        alert('Mật khẩu mới và xác nhận không khớp');
        return;
    }
    
    try {
        const response = await fetch('/api/change-password', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                current_password: data.current_password,
                new_password: data.new_password,
                new_password_confirmation: data.new_password_confirmation
            })
        });

        if (response.ok) {
            alert('Đổi mật khẩu thành công!');
            this.reset();
        } else {
            const error = await response.json();
            alert(error.message || 'Đổi mật khẩu thất bại');
        }
    } catch (error) {
        console.error('Change password error:', error);
        alert('Có lỗi xảy ra');
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load on page load
loadProfile();
loadMyPosts();
</script>
@endsection
