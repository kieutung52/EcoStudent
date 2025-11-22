@extends('layouts.app')

@section('title', 'Quản trị - EcoStudent')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Bảng điều khiển quản trị</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-gray-600 text-sm mb-2">Tổng người dùng</h3>
            <p id="total-users" class="text-3xl font-bold text-blue-600">-</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-gray-600 text-sm mb-2">Tổng bài viết</h3>
            <p id="total-posts" class="text-3xl font-bold text-green-600">-</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-gray-600 text-sm mb-2">Báo cáo chờ xử lý</h3>
            <p id="pending-reports" class="text-3xl font-bold text-yellow-600">-</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-gray-600 text-sm mb-2">Tổng đơn hàng</h3>
            <p id="total-orders" class="text-3xl font-bold text-purple-600">-</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Quản lý nhanh</h2>
            <div class="space-y-3">
                <a href="/admin/categories" class="block p-4 border rounded-lg hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Quản lý danh mục</span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <a href="/admin/universities" class="block p-4 border rounded-lg hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Quản lý trường đại học</span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <a href="/admin/users" class="block p-4 border rounded-lg hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Quản lý người dùng</span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
                <a href="/admin/reports" class="block p-4 border rounded-lg hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">Quản lý báo cáo</span>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Bài viết gần đây</h2>
            <div id="recent-posts" class="space-y-3">
                <p class="text-gray-500 text-center py-4">Đang tải...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
async function loadDashboard() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    // Check if admin
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    if (user && user.role !== 'ADMIN') {
        alert('Bạn không có quyền truy cập trang này');
        window.location.href = '/';
        return;
    }

    try {
        // Load stats (cần tạo API endpoint hoặc tính từ các API hiện có)
        // Tạm thời để placeholder
        document.getElementById('total-users').textContent = '-';
        document.getElementById('total-posts').textContent = '-';
        document.getElementById('pending-reports').textContent = '-';
        document.getElementById('total-orders').textContent = '-';
    } catch (error) {
        console.error('Load dashboard error:', error);
    }
}

loadDashboard();
</script>
@endsection

