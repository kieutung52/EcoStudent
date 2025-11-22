@extends('layouts.app')

@section('title', 'Hồ sơ của tôi - EcoStudent')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Hồ sơ của tôi</h1>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <form id="profile-form">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Họ và tên</label>
                    <input type="text" 
                           id="name" 
                           name="name"
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

            <div class="mt-6">
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Cập nhật
                </button>
            </div>
        </form>

        <div class="mt-8 border-t pt-6">
            <h2 class="text-xl font-semibold mb-4">Đổi mật khẩu</h2>
            <form id="change-password-form">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu hiện tại</label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu mới</label>
                        <input type="password" 
                               id="new_password_confirmation" 
                               name="new_password_confirmation"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" 
                            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                        Đổi mật khẩu
                    </button>
                </div>
            </form>
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

// Update profile
document.getElementById('profile-form').addEventListener('submit', async function(e) {
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
document.getElementById('change-password-form').addEventListener('submit', async function(e) {
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

// Load on page load
loadProfile();
</script>
@endsection

