<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị - EcoStudent')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 h-full">
    <!-- Admin Header -->
    <header class="bg-white shadow-md border-b">
        <div class="max-w-full mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-blue-600">EcoStudent Admin</a>
                    <nav class="flex space-x-4 ml-8">
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.users') }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.users') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                            Người dùng
                        </a>
                        <a href="{{ route('admin.categories') }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.categories') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                            Danh mục
                        </a>
                        <a href="{{ route('admin.universities') }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.universities') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                            Trường ĐH
                        </a>
                        <a href="{{ route('admin.reports') }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('admin.reports') ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                            Báo cáo
                        </a>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-lg hover:bg-gray-100">
                        Về trang chủ
                    </a>
                    <button id="admin-logout-btn" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        Đăng xuất
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area with 80vw width -->
    <main class="max-w-[80vw] mx-auto px-6 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        // Admin logout handler
        document.addEventListener('DOMContentLoaded', function() {
            const logoutBtn = document.getElementById('admin-logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const token = localStorage.getItem('jwt_token');
                    
                    try {
                        const response = await fetch('/api/logout', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        // Clear localStorage regardless of response
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user');
                        
                        // Redirect to home
                        window.location.href = '/';
                    } catch (error) {
                        console.error('Logout error:', error);
                        // Still clear and redirect even if API call fails
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user');
                        window.location.href = '/';
                    }
                });
            }

            // Check if user is admin
            const user = JSON.parse(localStorage.getItem('user') || 'null');
            if (!user || user.role !== 'ADMIN') {
                alert('Bạn không có quyền truy cập trang này');
                window.location.href = '/';
            }
        });
    </script>
    @yield('scripts')
</body>
</html>

