@extends('layouts.app')

@section('title', 'Quản lý người dùng - EcoStudent')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Quản lý người dùng</h1>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-4 flex space-x-4">
            <select id="filter-role" class="px-4 py-2 border rounded-lg">
                <option value="">Tất cả</option>
                <option value="USER">User</option>
                <option value="ADMIN">Admin</option>
            </select>
            <select id="filter-status" class="px-4 py-2 border rounded-lg">
                <option value="">Tất cả trạng thái</option>
                <option value="ACTIVE">Active</option>
                <option value="BANNED">Banned</option>
                <option value="WARNING">Warning</option>
                <option value="SHUT_DOWN">Shut Down</option>
            </select>
        </div>
        <div id="users-container" class="space-y-4">
            <p class="text-center text-gray-500 py-8">Đang tải...</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let users = [];

async function loadUsers() {
    const token = localStorage.getItem('jwt_token');
    const role = document.getElementById('filter-role').value;
    const status = document.getElementById('filter-status').value;
    
    let url = '/api/admin/users?';
    if (role) url += `role=${role}&`;
    if (status) url += `status=${status}&`;

    try {
        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            users = data.data || data;
            renderUsers();
        }
    } catch (error) {
        console.error('Load users error:', error);
    }
}

function renderUsers() {
    const container = document.getElementById('users-container');
    
    if (users.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">Không có người dùng nào</p>';
        return;
    }

    let html = '';
    users.forEach(user => {
        const statusColors = {
            'ACTIVE': 'bg-green-100 text-green-800',
            'BANNED': 'bg-red-100 text-red-800',
            'WARNING': 'bg-yellow-100 text-yellow-800',
            'SHUT_DOWN': 'bg-gray-100 text-gray-800'
        };

        html += `
            <div class="flex items-center justify-between p-4 border rounded-lg">
                <div class="flex items-center space-x-4">
                    <img src="${user.avatar ? '/storage/' + user.avatar : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name)}" 
                         alt="${escapeHtml(user.name)}" 
                         class="w-12 h-12 rounded-full">
                    <div>
                        <p class="font-medium">${escapeHtml(user.name)}</p>
                        <p class="text-sm text-gray-500">${escapeHtml(user.email)}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 rounded-full text-sm ${statusColors[user.status] || 'bg-gray-100'}">
                        ${user.status}
                    </span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                        ${user.role}
                    </span>
                    <select class="change-status px-3 py-1 border rounded" data-user-id="${user.id}">
                        <option value="ACTIVE" ${user.status === 'ACTIVE' ? 'selected' : ''}>Active</option>
                        <option value="BANNED" ${user.status === 'BANNED' ? 'selected' : ''}>Banned</option>
                        <option value="WARNING" ${user.status === 'WARNING' ? 'selected' : ''}>Warning</option>
                        <option value="SHUT_DOWN" ${user.status === 'SHUT_DOWN' ? 'selected' : ''}>Shut Down</option>
                    </select>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Attach event listeners
    document.querySelectorAll('.change-status').forEach(select => {
        select.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const newStatus = this.value;
            updateUserStatus(userId, newStatus);
        });
    });
}

async function updateUserStatus(userId, status) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/admin/users/${userId}/status`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status })
        });

        if (response.ok) {
            await loadUsers();
        } else {
            alert('Cập nhật thất bại');
        }
    } catch (error) {
        console.error('Update status error:', error);
    }
}

document.getElementById('filter-role').addEventListener('change', loadUsers);
document.getElementById('filter-status').addEventListener('change', loadUsers);

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadUsers();
</script>
@endsection

