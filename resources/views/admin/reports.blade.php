@extends('layouts.admin')

@section('title', 'Quản lý báo cáo - EcoStudent')

@section('content')
<div>
    <h1 class="text-3xl font-bold mb-6">Quản lý báo cáo</h1>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-4">
            <select id="filter-status" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">Tất cả</option>
                <option value="pending">Chờ xử lý</option>
                <option value="resolved">Đã xử lý</option>
            </select>
        </div>
        <div id="reports-container" class="space-y-4">
            <p class="text-center text-gray-500 py-8">Đang tải...</p>
        </div>
    </div>
</div>
<!-- Ban Modal -->
<div id="ban-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-bold mb-4 text-red-600">Cấm bài viết & Xử lý vi phạm</h3>
        <p class="text-sm text-gray-600 mb-4">Hành động này sẽ từ chối bài viết, ghi nhận vi phạm và có thể khóa tài khoản người dùng nếu tái phạm.</p>
        <form id="ban-form">
            <input type="hidden" id="ban-report-id">
            <div class="mb-4">
                <p class="mb-2 font-medium text-gray-700">Chọn lỗi vi phạm:</p>
                <div id="ban-rules-list" class="space-y-2 max-h-48 overflow-y-auto border p-2 rounded mb-2">
                    <p class="text-gray-500 text-sm">Đang tải luật...</p>
                </div>
                <textarea id="ban-note" class="w-full mt-2 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" rows="2" placeholder="Ghi chú thêm (tùy chọn)..."></textarea>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeBanModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                    Hủy
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                    Xác nhận Cấm
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
let reports = [];
let adminRulesLoaded = false;

async function loadReports() {
    const token = localStorage.getItem('jwt_token');
    const status = document.getElementById('filter-status').value;
    
    let url = '/api/admin/reports';
    if (status) url += `?status=${status}`;

    try {
        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            reports = data.data || data;
            renderReports();
        }
    } catch (error) {
        console.error('Load reports error:', error);
    }
}

function renderReports() {
    const container = document.getElementById('reports-container');
    
    if (reports.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">Không có báo cáo nào</p>';
        return;
    }

    let html = '';
    reports.forEach(report => {
        html += `
            <div class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-medium text-gray-800">Báo cáo từ: ${escapeHtml(report.user?.name || 'N/A')}</p>
                        <p class="text-sm text-gray-500">Bài viết: ${escapeHtml(report.post?.title || 'N/A')}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium ${report.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}">
                        ${report.status === 'pending' ? 'Chờ xử lý' : 'Đã xử lý'}
                    </span>
                </div>
                <p class="text-gray-700 mb-3">Lý do: ${escapeHtml(report.reason)}</p>
                <div class="flex space-x-2 items-center">
                    <select class="change-status px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm" data-report-id="${report.id}">
                        <option value="pending" ${report.status === 'pending' ? 'selected' : ''}>Chờ xử lý</option>
                        <option value="resolved" ${report.status === 'resolved' ? 'selected' : ''}>Đã xử lý</option>
                    </select>
                    
                    <button class="ban-post-btn bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors text-sm flex items-center space-x-1" data-report-id="${report.id}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                        <span>Ban bài viết</span>
                    </button>

                    <button class="delete-report bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors text-sm" data-report-id="${report.id}">Xóa</button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Attach event listeners
    document.querySelectorAll('.change-status').forEach(select => {
        select.addEventListener('change', function() {
            const reportId = this.dataset.reportId;
            const newStatus = this.value;
            updateReportStatus(reportId, newStatus);
        });
    });

    document.querySelectorAll('.delete-report').forEach(btn => {
        btn.addEventListener('click', function() {
            const reportId = this.dataset.reportId;
            if (confirm('Bạn có chắc muốn xóa báo cáo này?')) {
                deleteReport(reportId);
            }
        });
    });

    document.querySelectorAll('.ban-post-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const reportId = this.dataset.reportId;
            openBanModal(reportId);
        });
    });
}

// Ban Modal Logic
async function openBanModal(reportId) {
    document.getElementById('ban-report-id').value = reportId;
    document.getElementById('ban-modal').classList.remove('hidden');
    
    if (!adminRulesLoaded) {
        await loadAdminRules();
    }
}

function closeBanModal() {
    document.getElementById('ban-modal').classList.add('hidden');
    document.getElementById('ban-form').reset();
}

async function loadAdminRules() {
    const container = document.getElementById('ban-rules-list');
    const token = localStorage.getItem('jwt_token');
    try {
        const response = await fetch('/api/admin/rules', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        if (response.ok) {
            const rules = await response.json();
            if (rules.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm">Chưa có luật nào</p>';
            } else {
                container.innerHTML = rules.map(rule => `
                    <label class="flex items-start space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                        <input type="checkbox" name="ban_rules[]" value="${rule.id}" class="form-checkbox text-red-600 mt-1">
                        <span class="text-sm text-gray-700 font-medium">${escapeHtml(rule.title)}</span>
                    </label>
                `).join('');
            }
            adminRulesLoaded = true;
        }
    } catch (error) {
        console.error('Load rules error:', error);
        container.innerHTML = '<p class="text-red-500 text-sm">Lỗi tải luật</p>';
    }
}

document.getElementById('ban-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const reportId = document.getElementById('ban-report-id').value;
    const selectedRules = Array.from(document.querySelectorAll('input[name="ban_rules[]"]:checked')).map(cb => cb.value);
    const note = document.getElementById('ban-note').value;
    
    if (selectedRules.length === 0) {
        alert('Vui lòng chọn ít nhất một lỗi vi phạm');
        return;
    }

    const token = localStorage.getItem('jwt_token');
    try {
        const response = await fetch(`/api/admin/reports/${reportId}/ban`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                rule_ids: selectedRules,
                note: note
            })
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message || 'Đã xử lý thành công');
            closeBanModal();
            loadReports(); // Reload list
        } else {
            alert(result.message || 'Xử lý thất bại');
        }
    } catch (error) {
        console.error('Ban error:', error);
        alert('Có lỗi xảy ra');
    }
});

async function updateReportStatus(reportId, status) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/admin/reports/${reportId}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status })
        });

        if (response.ok) {
            await loadReports();
        } else {
            alert('Cập nhật thất bại');
        }
    } catch (error) {
        console.error('Update report error:', error);
    }
}

async function deleteReport(reportId) {
    const token = localStorage.getItem('jwt_token');
    
    try {
        const response = await fetch(`/api/admin/reports/${reportId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                // No body needed for delete, but keeping structure
            })
        });

        if (response.ok) {
            await loadReports();
        } else {
            alert('Xóa thất bại');
        }
    } catch (error) {
        console.error('Delete report error:', error);
    }
}

document.getElementById('filter-status').addEventListener('change', loadReports);

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadReports();
</script>
@endsection

