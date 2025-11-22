@extends('layouts.app')

@section('title', 'Quản lý trường đại học - EcoStudent')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Quản lý trường đại học</h1>
        <button id="add-university-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            + Thêm trường
        </button>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div id="universities-container" class="space-y-4">
            <p class="text-center text-gray-500 py-8">Đang tải...</p>
        </div>
    </div>
</div>

<!-- Add/Edit University Modal -->
<div id="university-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h2 id="modal-title" class="text-xl font-semibold mb-4">Thêm trường đại học</h2>
        <form id="university-form">
            <input type="hidden" id="university-id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tên trường</label>
                <input type="text" 
                       id="university-name" 
                       required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Mã trường</label>
                <input type="text" 
                       id="university-code" 
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ</label>
                <textarea id="university-address" 
                          rows="2"
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                    Lưu
                </button>
                <button type="button" id="cancel-modal" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let universities = [];

async function loadUniversities() {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch('/api/universities', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            universities = await response.json();
            renderUniversities();
        }
    } catch (error) {
        console.error('Load universities error:', error);
    }
}

function renderUniversities() {
    const container = document.getElementById('universities-container');
    
    if (universities.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">Chưa có trường nào</p>';
        return;
    }

    let html = '';
    universities.forEach(uni => {
        html += `
            <div class="flex items-center justify-between p-4 border rounded-lg">
                <div>
                    <span class="font-medium">${escapeHtml(uni.name)}</span>
                    ${uni.code ? `<span class="text-sm text-gray-500 ml-2">(${escapeHtml(uni.code)})</span>` : ''}
                    ${uni.address ? `<p class="text-sm text-gray-500 mt-1">${escapeHtml(uni.address)}</p>` : ''}
                </div>
                <div class="flex space-x-2">
                    <button class="edit-university text-blue-600 hover:underline" data-id="${uni.id}">Sửa</button>
                    <button class="delete-university text-red-600 hover:underline" data-id="${uni.id}">Xóa</button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Attach event listeners
    document.querySelectorAll('.edit-university').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const university = universities.find(u => u.id == id);
            if (university) {
                openModal(university);
            }
        });
    });

    document.querySelectorAll('.delete-university').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            if (confirm('Bạn có chắc muốn xóa trường này?')) {
                deleteUniversity(id);
            }
        });
    });
}

function openModal(university = null) {
    const modal = document.getElementById('university-modal');
    const title = document.getElementById('modal-title');
    const nameInput = document.getElementById('university-name');
    const codeInput = document.getElementById('university-code');
    const addressInput = document.getElementById('university-address');
    const idInput = document.getElementById('university-id');

    if (university) {
        title.textContent = 'Sửa trường đại học';
        nameInput.value = university.name || '';
        codeInput.value = university.code || '';
        addressInput.value = university.address || '';
        idInput.value = university.id;
    } else {
        title.textContent = 'Thêm trường đại học';
        nameInput.value = '';
        codeInput.value = '';
        addressInput.value = '';
        idInput.value = '';
    }

    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('university-modal').classList.add('hidden');
}

document.getElementById('add-university-btn').addEventListener('click', () => openModal());
document.getElementById('cancel-modal').addEventListener('click', closeModal);

document.getElementById('university-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const token = localStorage.getItem('jwt_token');
    const name = document.getElementById('university-name').value;
    const code = document.getElementById('university-code').value;
    const address = document.getElementById('university-address').value;
    const id = document.getElementById('university-id').value;

    try {
        const url = id ? `/api/admin/universities/${id}` : '/api/admin/universities';
        const method = id ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ name, code, address })
        });

        if (response.ok) {
            closeModal();
            await loadUniversities();
        } else {
            const error = await response.json();
            alert(error.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        console.error('Save university error:', error);
        alert('Có lỗi xảy ra');
    }
});

async function deleteUniversity(id) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/admin/universities/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            await loadUniversities();
        } else {
            alert('Xóa thất bại');
        }
    } catch (error) {
        console.error('Delete university error:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadUniversities();
</script>
@endsection

