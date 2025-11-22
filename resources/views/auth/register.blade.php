@extends('layouts.app')

@section('title', 'Đăng ký - EcoStudent')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Đăng ký</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('auth.register') }}" id="register-form">
            @csrf
            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Họ và tên</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                       value="{{ old('name') }}">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

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

            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                <input type="text" 
                       id="phone" 
                       name="phone" 
                       required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror"
                       value="{{ old('phone') }}">
                @error('phone')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
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

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu</label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label for="university_id" class="block text-sm font-medium text-gray-700 mb-2">Trường đại học (tùy chọn)</label>
                <select id="university_id" 
                        name="university_id"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Chọn trường đại học</option>
                    @foreach($universities as $university)
                        <option value="{{ $university->id }}" {{ old('university_id') == $university->id ? 'selected' : '' }}>
                            {{ $university->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Đăng ký
            </button>
        </form>

        <div class="mt-4 text-center">
            <p class="text-gray-600">Đã có tài khoản? 
                <a href="{{ route('auth.login') }}" class="text-blue-600 hover:underline">Đăng nhập</a>
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/register', {
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
            localStorage.setItem('user', JSON.stringify(result.data));
            
            // Redirect về trang chủ
            window.location.href = '/';
        } else {
            // Hiển thị lỗi
            if (result.errors) {
                let errorMsg = '';
                for (const key in result.errors) {
                    errorMsg += result.errors[key][0] + '\n';
                }
                alert(errorMsg);
            } else {
                alert(result.message || 'Đăng ký thất bại');
            }
        }
    } catch (error) {
        console.error('Register error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    }
});
</script>
@endsection

