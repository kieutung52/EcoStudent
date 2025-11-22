@extends('layouts.app')

@section('title', 'Quản lý danh mục - EcoStudent')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Quản lý danh mục</h1>
        <button id="add-category-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            + Thêm danh mục
        </button>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div id="categories-container" class="space-y-4">
            <p class="text-center text-gray-500 py-8">Đang tải...</p>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="category-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h2 id="modal-title" class="text-xl font-semibold mb-4">Thêm danh mục</h2>
        <form id="category-form">
            <input type="hidden" id="category-id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tên danh mục</label>
                <input type="text" 
                       id="category-name" 
                       required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
let categories = [];

async function loadCategories() {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch('/api/categories', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            categories = await response.json();
            renderCategories();
        }
    } catch (error) {
        console.error('Load categories error:', error);
    }
}

function renderCategories() {
    const container = document.getElementById('categories-container');
    
    if (categories.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">Chưa có danh mục nào</p>';
        return;
    }

    let html = '';
    categories.forEach(cat => {
        html += `
            <div class="flex items-center justify-between p-4 border rounded-lg">
                <span class="font-medium">${escapeHtml(cat.name)}</span>
                <div class="flex space-x-2">
                    <button class="edit-category text-blue-600 hover:underline" data-id="${cat.id}">Sửa</button>
                    <button class="delete-category text-red-600 hover:underline" data-id="${cat.id}">Xóa</button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Attach event listeners
    document.querySelectorAll('.edit-category').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const category = categories.find(c => c.id == id);
            if (category) {
                openModal(category);
            }
        });
    });

    document.querySelectorAll('.delete-category').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            if (confirm('Bạn có chắc muốn xóa danh mục này?')) {
                deleteCategory(id);
            }
        });
    });
}

function openModal(category = null) {
    const modal = document.getElementById('category-modal');
    const title = document.getElementById('modal-title');
    const form = document.getElementById('category-form');
    const nameInput = document.getElementById('category-name');
    const idInput = document.getElementById('category-id');

    if (category) {
        title.textContent = 'Sửa danh mục';
        nameInput.value = category.name;
        idInput.value = category.id;
    } else {
        title.textContent = 'Thêm danh mục';
        nameInput.value = '';
        idInput.value = '';
    }

    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('category-modal').classList.add('hidden');
}

document.getElementById('add-category-btn').addEventListener('click', () => openModal());
document.getElementById('cancel-modal').addEventListener('click', closeModal);

document.getElementById('category-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const token = localStorage.getItem('jwt_token');
    const name = document.getElementById('category-name').value;
    const id = document.getElementById('category-id').value;

    try {
        const url = id ? `/api/admin/categories/${id}` : '/api/admin/categories';
        const method = id ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ name })
        });

        if (response.ok) {
            closeModal();
            await loadCategories();
        } else {
            const error = await response.json();
            alert(error.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        console.error('Save category error:', error);
        alert('Có lỗi xảy ra');
    }
});

async function deleteCategory(id) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/admin/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            await loadCategories();
        } else {
            alert('Xóa thất bại');
        }
    } catch (error) {
        console.error('Delete category error:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadCategories();
</script>
@endsection

