@extends('layouts.app')

@section('title', 'Thanh toán - EcoStudent')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Thanh toán</h1>

    <div class="grid grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Thông tin giao hàng</h2>
            <form id="checkout-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ giao hàng <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 gap-4 mb-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tỉnh/Thành phố <span class="text-red-500">*</span></label>
                            <select id="province" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Chọn Tỉnh/Thành</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Quận/Huyện <span class="text-red-500">*</span></label>
                            <select id="district" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                                <option value="">Chọn Quận/Huyện</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Phường/Xã <span class="text-red-500">*</span></label>
                            <select id="ward" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                                <option value="">Chọn Phường/Xã</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Số nhà, tên đường <span class="text-red-500">*</span></label>
                        <input type="text" id="street" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ví dụ: 123 Đường Nguyễn Văn Cừ">
                    </div>
                    <input type="hidden" name="shipping_address" id="full_shipping_address">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại <span class="text-red-500">*</span></label>
                    <input type="text" 
                           id="phone_number" 
                           name="phone_number" 
                           required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú (tùy chọn)</label>
                    <textarea id="note" 
                              name="note" 
                              class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              rows="2"></textarea>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
                    Đặt hàng
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Đơn hàng</h2>
            <div id="checkout-items" class="space-y-2 mb-4">
                <!-- Items will be loaded here -->
            </div>
            <div class="border-t pt-4">
                <div class="flex justify-between">
                    <span class="font-semibold">Tổng cộng:</span>
                    <span id="total-amount" class="text-xl font-bold text-blue-600">0 đ</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let cartItems = [];

async function loadCheckoutData() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    try {
        const response = await fetch('/api/cart', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            cartItems = await response.json();
            renderCheckoutItems();
        } else {
            window.location.href = '/cart';
        }
    } catch (error) {
        console.error('Load checkout error:', error);
    }
}

function renderCheckoutItems() {
    const container = document.getElementById('checkout-items');
    let total = 0;

    // Filter out items with missing products
    const validItems = cartItems.filter(item => item && item.product);

    console.log('Cart Items:', cartItems);
    console.log('Valid Items:', validItems);

    if (validItems.length === 0) {
        container.innerHTML = `<div class="text-center py-4">
            <p class="text-gray-500">Giỏ hàng trống</p>
            <p class="text-xs text-gray-400 mt-2">Debug: Total ${cartItems.length}, Valid ${validItems.length}</p>
            <a href="/" class="text-blue-600 hover:underline text-sm">Quay lại mua sắm</a>
        </div>`;
        document.getElementById('total-amount').textContent = '0 đ';
        return;
    }

    let html = '';
    validItems.forEach(item => {
        const price = parseFloat(item.product.price) || 0;
        const quantity = parseInt(item.quantity) || 0;
        const itemTotal = price * quantity;
        total += itemTotal;
        
        html += `
            <div class="flex justify-between py-2 border-b">
                <div>
                    <p class="font-medium">${escapeHtml(item.product.name)}</p>
                    <p class="text-sm text-gray-500">x ${quantity}</p>
                </div>
                <span>${new Intl.NumberFormat('vi-VN').format(itemTotal)} đ</span>
            </div>
        `;
    });

    container.innerHTML = html;
    document.getElementById('total-amount').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
}

loadCheckoutData();
loadProvinces();

// Address Handling
const provinceSelect = document.getElementById('province');
const districtSelect = document.getElementById('district');
const wardSelect = document.getElementById('ward');
const streetInput = document.getElementById('street');

async function loadProvinces() {
    try {
        const response = await fetch('https://provinces.open-api.vn/api/?depth=1');
        const data = await response.json();
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.code;
            option.textContent = item.name;
            provinceSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading provinces:', error);
    }
}

provinceSelect.addEventListener('change', async function() {
    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    districtSelect.disabled = true;
    wardSelect.disabled = true;

    if (this.value) {
        try {
            const response = await fetch(`https://provinces.open-api.vn/api/p/${this.value}?depth=2`);
            const data = await response.json();
            data.districts.forEach(item => {
                const option = document.createElement('option');
                option.value = item.code;
                option.textContent = item.name;
                districtSelect.appendChild(option);
            });
            districtSelect.disabled = false;
        } catch (error) {
            console.error('Error loading districts:', error);
        }
    }
});

districtSelect.addEventListener('change', async function() {
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    wardSelect.disabled = true;

    if (this.value) {
        try {
            const response = await fetch(`https://provinces.open-api.vn/api/d/${this.value}?depth=2`);
            const data = await response.json();
            data.wards.forEach(item => {
                const option = document.createElement('option');
                option.value = item.code;
                option.textContent = item.name;
                wardSelect.appendChild(option);
            });
            wardSelect.disabled = false;
        } catch (error) {
            console.error('Error loading wards:', error);
        }
    }
});

document.getElementById('checkout-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validate Address
    if (!provinceSelect.value || !districtSelect.value || !wardSelect.value || !streetInput.value) {
        alert('Vui lòng nhập đầy đủ địa chỉ giao hàng');
        return;
    }

    const provinceName = provinceSelect.options[provinceSelect.selectedIndex].text;
    const districtName = districtSelect.options[districtSelect.selectedIndex].text;
    const wardName = wardSelect.options[wardSelect.selectedIndex].text;
    const fullAddress = `${streetInput.value}, ${wardName}, ${districtName}, ${provinceName}`;
    
    const token = localStorage.getItem('jwt_token');
    const formData = new FormData(this);
    
    // Prepare items for checkout
    const items = cartItems.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity
    }));

    const data = {
        items: items,
        shipping_address: fullAddress,
        phone_number: formData.get('phone_number'),
        note: formData.get('note') || ''
    };

    try {
        const response = await fetch('/api/checkout', {
            method: 'POST',
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
            alert('Đặt hàng thành công!');
            window.location.href = '/my-orders';
        } else {
            const error = await response.json();
            alert(error.message || 'Đặt hàng thất bại');
        }
    } catch (error) {
        console.error('Checkout error:', error);
        alert('Có lỗi xảy ra');
    }
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection

