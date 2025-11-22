@extends('layouts.app')

@section('title', 'Quản lý báo cáo - EcoStudent')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Quản lý báo cáo</h1>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-4">
            <select id="filter-status" class="px-4 py-2 border rounded-lg">
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
@endsection

@section('scripts')
<script>
let reports = [];

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
            <div class="p-4 border rounded-lg">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-medium">Báo cáo từ: ${escapeHtml(report.user?.name || 'N/A')}</p>
                        <p class="text-sm text-gray-500">Bài viết: ${escapeHtml(report.post?.title || 'N/A')}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm ${report.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}">
                        ${report.status === 'pending' ? 'Chờ xử lý' : 'Đã xử lý'}
                    </span>
                </div>
                <p class="text-gray-700 mb-3">${escapeHtml(report.reason)}</p>
                <div class="flex space-x-2">
                    <select class="change-status px-3 py-1 border rounded text-sm" data-report-id="${report.id}">
                        <option value="pending" ${report.status === 'pending' ? 'selected' : ''}>Chờ xử lý</option>
                        <option value="resolved" ${report.status === 'resolved' ? 'selected' : ''}>Đã xử lý</option>
                    </select>
                    <button class="delete-report text-red-600 text-sm hover:underline" data-report-id="${report.id}">Xóa</button>
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
}

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
            }
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

