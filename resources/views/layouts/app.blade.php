<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EcoStudent - Marketplace cho Sinh viên')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 h-full flex">
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-white shadow-lg h-screen sticky top-0 flex flex-col">
        <!-- Logo -->
        <div class="p-4 border-b">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">EcoStudent</a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="{{ route('home') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span>Trang chủ</span>
            </a>

            <!-- User Menu Items (Hidden by default) -->
            <div id="user-nav-items" class="hidden space-y-2">
                <a href="#" id="nav-cart" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Giỏ hàng</span>
                </a>
                <a href="#" id="nav-my-orders" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span>Đơn hàng</span>
                </a>
                <a href="#" id="nav-sales-orders" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span>Đơn bán</span>
                </a>
                <a href="#" id="nav-create-post" class="flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Đăng bài</span>
                </a>
            </div>
        </nav>

        <!-- User Section at Bottom -->
        <div class="p-4 border-t">
            <!-- Auth Buttons (Not logged in) - Default visible -->
            <div id="auth-buttons" class="space-y-2">
                <a href="{{ route('auth.login') }}" class="block w-full text-center px-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg border border-blue-600">
                    Đăng nhập
                </a>
                <a href="{{ route('auth.register') }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Đăng ký
                </a>
            </div>

            <!-- User Menu (Logged in) -->
            <div id="user-menu" class="hidden">
                <button id="user-menu-button" class="w-full flex items-center space-x-3 px-4 py-2 rounded-lg hover:bg-gray-100 focus:outline-none">
                    <img id="user-avatar" src="" alt="Avatar" class="w-10 h-10 rounded-full border-2 border-gray-300">
                    <div class="flex-1 text-left">
                        <p id="user-name" class="font-medium text-gray-700 text-sm"></p>
                        <p class="text-xs text-gray-500">Click để xem menu</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <!-- Dropdown Content -->
                <div id="user-dropdown" class="hidden mt-2 bg-white rounded-lg shadow-lg py-2 border">
                    <a href="#" id="dropdown-profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Hồ sơ</a>
                    <a href="#" id="dropdown-admin" class="hidden block px-4 py-2 text-gray-700 hover:bg-gray-100">Quản trị</a>
                    <hr class="my-2">
                    <button id="logout-btn" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">Đăng xuất</button>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto  ">
            <div class="max-w-8xl mx-auto px-4 py-6 min-h-[calc(100vh-100px)]">
                @yield('content')
            </div>
            
            <!-- Footer -->
            {{-- <footer class="bg-white border-t mt-auto">
                <div class="max-w-7xl mx-auto px-4 py-6 text-center text-gray-600">
                    <p>&copy; 2025 EcoStudent. Marketplace cho Sinh viên.</p>
                </div>
            </footer> --}}
        </main>
    </div>

    <!-- Product Detail Modal -->
    <div id="product-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="relative">
                <button id="close-modal" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                
                <button id="prev-product" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-75 hover:bg-opacity-100 rounded-full p-3 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <button id="next-product" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-75 hover:bg-opacity-100 rounded-full p-3 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <div id="modal-content" class="p-6">
                    <!-- Nội dung sẽ được load bằng JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global navigation update function
        function updateNavigation() {
            const token = localStorage.getItem('jwt_token');
            let user = null;
            try {
                const userStr = localStorage.getItem('user');
                if (userStr && userStr !== 'null') {
                    user = JSON.parse(userStr);
                }
            } catch (e) {
                console.error('Error parsing user from localStorage:', e);
                // Clear invalid data
                localStorage.removeItem('user');
            }

            const authButtons = document.getElementById('auth-buttons');
            const userMenu = document.getElementById('user-menu');
            const userNavItems = document.getElementById('user-nav-items');

            // Check if user is actually logged in (has valid token and user data)
            const isLoggedIn = token && token !== 'null' && user && user.id;

            if (isLoggedIn) {
                // User is logged in
                if (authButtons) authButtons.classList.add('hidden');
                if (userMenu) userMenu.classList.remove('hidden');
                if (userNavItems) userNavItems.classList.remove('hidden');
                
                // Set navigation links
                const navItems = ['nav-cart', 'nav-my-orders', 'nav-sales-orders', 'nav-create-post'];
                navItems.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        if (id === 'nav-cart') el.href = '/cart';
                        if (id === 'nav-my-orders') el.href = '/my-orders';
                        if (id === 'nav-sales-orders') el.href = '/sales-orders';
                        if (id === 'nav-create-post') el.href = '/posts/create';
                    }
                });
                
                // Show admin dropdown if admin
                if (user.role === 'ADMIN') {
                    const adminDropdown = document.getElementById('dropdown-admin');
                    if (adminDropdown) {
                        adminDropdown.classList.remove('hidden');
                        adminDropdown.href = '/admin';
                    }
                } else {
                    const adminDropdown = document.getElementById('dropdown-admin');
                    if (adminDropdown) {
                        adminDropdown.classList.add('hidden');
                    }
                }
                
                // Set dropdown links
                const dropdownProfile = document.getElementById('dropdown-profile');
                if (dropdownProfile) dropdownProfile.href = '/profile';
                
                const avatarEl = document.getElementById('user-avatar');
                const nameEl = document.getElementById('user-name');
                if (avatarEl) avatarEl.src = user.avatar ? `/storage/${user.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}`;
                if (nameEl) nameEl.textContent = user.name;
            } else {
                // User is not logged in - show auth buttons
                if (authButtons) {
                    authButtons.classList.remove('hidden');
                    authButtons.style.display = 'block';
                }
                if (userMenu) {
                    userMenu.classList.add('hidden');
                    userMenu.style.display = 'none';
                }
                if (userNavItems) {
                    userNavItems.classList.add('hidden');
                    userNavItems.style.display = 'none';
                }
            }
        }

        // Run on page load - ensure it runs after DOM is ready
        function initNavigation() {
            updateNavigation();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initNavigation);
        } else {
            // DOM is already ready
            initNavigation();
        }

        // Also run when page becomes visible (in case of navigation)
        document.addEventListener('DOMContentLoaded', function() {
            // Re-check navigation state after a short delay to ensure all elements are ready
            setTimeout(updateNavigation, 100);
            
            // User dropdown toggle
            const userMenuButton = document.getElementById('user-menu-button');
            const userDropdown = document.getElementById('user-dropdown');
            
            if (userMenuButton && userDropdown) {
                userMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('hidden');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.classList.add('hidden');
                    }
                });
            }

            // Logout handler
            const logoutBtn = document.getElementById('logout-btn');
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
                        
                        // Update navigation immediately
                        updateNavigation();
                        
                        // Redirect to home
                        window.location.href = '/';
                    } catch (error) {
                        console.error('Logout error:', error);
                        // Still clear and redirect even if API call fails
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('user');
                        
                        // Update navigation immediately
                        updateNavigation();
                        
                        window.location.href = '/';
                    }
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>

