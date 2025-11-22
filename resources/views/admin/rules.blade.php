@extends('layouts.admin')

@section('title', 'Quản lý luật lệ - EcoStudent')

@section('content')
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Quản lý luật lệ đăng bài</h1>
        <button id="add-rule-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            + Thêm luật lệ
        </button>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div id="rules-container" class="space-y-4">
            <p class="text-center text-gray-500 py-8">Đang tải...</p>
        </div>
    </div>
</div>

<!-- Add/Edit Rule Modal -->
<div id="rule-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h2 id="modal-title" class="text-xl font-semibold mb-4">Thêm luật lệ</h2>
        <form id="rule-form">
            <input type="hidden" id="rule-id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề *</label>
                <input type="text" 
                       id="rule-title" 
                       required
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nội dung *</label>
                <textarea id="rule-content" 
                          rows="6"
                          required
                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Thứ tự hiển thị</label>
                    <input type="number" 
                           id="rule-order" 
                           min="0"
                           value="0"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                    <select id="rule-is-active" 
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Hoạt động</option>
                        <option value="0">Tạm ẩn</option>
                    </select>
                </div>
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Lưu
                </button>
                <button type="button" id="cancel-modal" class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let rules = [];

async function loadRules() {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch('/api/admin/rules', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            rules = await response.json();
            renderRules();
        }
    } catch (error) {
        console.error('Load rules error:', error);
    }
}

function renderRules() {
    const container = document.getElementById('rules-container');
    
    if (rules.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">Chưa có luật lệ nào</p>';
        return;
    }

    let html = '';
    rules.forEach(rule => {
        html += `
            <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <h3 class="font-semibold text-gray-800">${escapeHtml(rule.title)}</h3>
                            <span class="px-2 py-1 rounded text-xs ${rule.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${rule.is_active ? 'Hoạt động' : 'Tạm ẩn'}
                            </span>
                            <span class="text-xs text-gray-500">Thứ tự: ${rule.order}</span>
                        </div>
                        <p class="text-gray-700 whitespace-pre-wrap">${escapeHtml(rule.content)}</p>
                    </div>
                    <div class="flex space-x-2 ml-4">
                        <button class="edit-rule bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors text-sm" data-id="${rule.id}">Sửa</button>
                        <button class="delete-rule bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors text-sm" data-id="${rule.id}">Xóa</button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Attach event listeners
    document.querySelectorAll('.edit-rule').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const rule = rules.find(r => r.id == id);
            if (rule) {
                openModal(rule);
            }
        });
    });

    document.querySelectorAll('.delete-rule').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            if (confirm('Bạn có chắc muốn xóa luật lệ này?')) {
                deleteRule(id);
            }
        });
    });
}

function openModal(rule = null) {
    const modal = document.getElementById('rule-modal');
    const title = document.getElementById('modal-title');
    const form = document.getElementById('rule-form');
    const titleInput = document.getElementById('rule-title');
    const contentInput = document.getElementById('rule-content');
    const orderInput = document.getElementById('rule-order');
    const isActiveInput = document.getElementById('rule-is-active');
    const idInput = document.getElementById('rule-id');

    if (rule) {
        title.textContent = 'Sửa luật lệ';
        titleInput.value = rule.title;
        contentInput.value = rule.content;
        orderInput.value = rule.order;
        isActiveInput.value = rule.is_active ? '1' : '0';
        idInput.value = rule.id;
    } else {
        title.textContent = 'Thêm luật lệ';
        titleInput.value = '';
        contentInput.value = '';
        orderInput.value = '0';
        isActiveInput.value = '1';
        idInput.value = '';
    }

    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('rule-modal').classList.add('hidden');
}

document.getElementById('add-rule-btn').addEventListener('click', () => openModal());
document.getElementById('cancel-modal').addEventListener('click', closeModal);

document.getElementById('rule-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const token = localStorage.getItem('jwt_token');
    const title = document.getElementById('rule-title').value;
    const content = document.getElementById('rule-content').value;
    const order = parseInt(document.getElementById('rule-order').value) || 0;
    const isActive = document.getElementById('rule-is-active').value === '1';
    const id = document.getElementById('rule-id').value;

    try {
        const url = id ? `/api/admin/rules/${id}` : '/api/admin/rules';
        const method = id ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ title, content, order, is_active: isActive })
        });

        if (response.ok) {
            closeModal();
            await loadRules();
        } else {
            const error = await response.json();
            alert(error.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        console.error('Save rule error:', error);
        alert('Có lỗi xảy ra');
    }
});

async function deleteRule(id) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/admin/rules/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (response.ok) {
            await loadRules();
        } else {
            alert('Xóa thất bại');
        }
    } catch (error) {
        console.error('Delete rule error:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadRules();
</script>
@endsection

