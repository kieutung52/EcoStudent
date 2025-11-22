@extends('layouts.app')

@section('title', 'Đăng nhập - EcoStudent')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Đăng nhập</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('auth.login') }}" id="login-form">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                       value="{{ old('email') }}">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Đăng nhập
            </button>
        </form>

        <div class="mt-4 text-center">
            <p class="text-gray-600">Chưa có tài khoản? 
                <a href="{{ route('auth.register') }}" class="text-blue-600 hover:underline">Đăng ký ngay</a>
            </p>
        </div>

        <div class="mt-6 p-4 bg-gray-100 rounded-lg">
            <p class="text-sm text-gray-600 mb-2"><strong>Test Accounts:</strong></p>
            <p class="text-xs text-gray-500">Admin: admin@ecostudent.com / password123</p>
            <p class="text-xs text-gray-500">User: an@example.com / password123</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            // Lưu token và user vào localStorage
            localStorage.setItem('jwt_token', result.access_token);
            localStorage.setItem('user', JSON.stringify(result.user));
            
            // Redirect về trang chủ
            window.location.href = '/';
        } else {
            alert(result.message || 'Đăng nhập thất bại');
        }
    } catch (error) {
        console.error('Login error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    }
});
</script>
@endsection

