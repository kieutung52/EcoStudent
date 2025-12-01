@extends('layouts.app')

@section('title', 'Xác thực OTP - EcoStudent')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Xác thực OTP</h2>

        <div class="mb-4 text-center text-gray-600">
            <p>Mã OTP đã được gửi đến email: <strong>{{ $email }}</strong></p>
            <p>Vui lòng kiểm tra hộp thư đến (và spam) của bạn.</p>
        </div>

        <form id="verify-form">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            
            <div class="mb-6">
                <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">Mã OTP <span class="text-red-500">*</span></label>
                <input type="text" 
                       id="otp" 
                       name="otp" 
                       required
                       placeholder="Nhập mã 6 số"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-2xl tracking-widest">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors mb-4">
                Xác thực
            </button>
        </form>

        <div class="text-center">
            <button id="resend-btn" class="text-blue-600 hover:underline text-sm">Gửi lại mã OTP</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('verify-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/verify-otp', {
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
            
            alert('Xác thực thành công!');
            window.location.href = '/';
        } else {
            alert(result.message || 'Xác thực thất bại');
        }
    } catch (error) {
        console.error('Verify error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    }
});

document.getElementById('resend-btn').addEventListener('click', async function(e) {
    e.preventDefault();
    const email = document.querySelector('input[name="email"]').value;
    
    try {
        const response = await fetch('/api/resend-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: email })
        });

        const result = await response.json();
        alert(result.message);
    } catch (error) {
        console.error('Resend error:', error);
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    }
});
</script>
@endsection
