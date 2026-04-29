<?php
/**
 * Admin Reports List View
 * Displays all reports with filters and status management
 */
?>

<div class="admin-reports-container" style="padding: 30px; background: linear-gradient(135deg, #183127 0%, #234236 100%); min-height: 100vh;">
    <div style="max-width: 1400px; margin: 0 auto;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
            <h1 style="color: #d2b48c; font-size: 2rem; font-weight: 700; margin: 0;">Reports Management</h1>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <select id="filterType" style="padding: 10px 15px; border-radius: 8px; border: 1px solid rgba(202, 164, 110, 0.3); background: rgba(255, 255, 255, 0.05); color: #d2b48c; cursor: pointer;">
                    <option value="">All Types</option>
                    <option value="listing">Listing Reports</option>
                    <option value="message">Message Reports</option>
                </select>
                <select id="filterStatus" style="padding: 10px 15px; border-radius: 8px; border: 1px solid rgba(202, 164, 110, 0.3); background: rgba(255, 255, 255, 0.05); color: #d2b48c; cursor: pointer;">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="dismissed">Dismissed</option>
                    <option value="action_taken">Action Taken</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>

        <!-- Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(202, 164, 110, 0.2); border-radius: 12px; padding: 20px;">
                <div style="color: rgba(210, 180, 140, 0.7); font-size: 0.85rem; margin-bottom: 8px;">Pending</div>
                <div style="color: #d2b48c; font-size: 2rem; font-weight: 700;" id="statPending">0</div>
            </div>
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(202, 164, 110, 0.2); border-radius: 12px; padding: 20px;">
                <div style="color: rgba(210, 180, 140, 0.7); font-size: 0.85rem; margin-bottom: 8px;">Total Reports</div>
                <div style="color: #d2b48c; font-size: 2rem; font-weight: 700;" id="statTotal">0</div>
            </div>
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(202, 164, 110, 0.2); border-radius: 12px; padding: 20px;">
                <div style="color: rgba(210, 180, 140, 0.7); font-size: 0.85rem; margin-bottom: 8px;">Listing Reports</div>
                <div style="color: #d2b48c; font-size: 2rem; font-weight: 700;" id="statListings">0</div>
            </div>
            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(202, 164, 110, 0.2); border-radius: 12px; padding: 20px;">
                <div style="color: rgba(210, 180, 140, 0.7); font-size: 0.85rem; margin-bottom: 8px;">Message Reports</div>
                <div style="color: #d2b48c; font-size: 2rem; font-weight: 700;" id="statMessages">0</div>
            </div>
        </div>

        <!-- Reports Table -->
        <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(202, 164, 110, 0.2); border-radius: 12px; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(202, 164, 110, 0.2);">
                            <th style="padding: 18px; text-align: left; color: #d2b48c; font-weight: 600; font-size: 0.85rem;">ID</th>
                            <th style="padding: 18px; text-align: left; color: #d2b48c; font-weight: 600; font-size: 0.85rem;">Type</th>
                            <th style="padding: 18px; text-align: left; color: #d2b48c; font-weight: 600; font-size: 0.85rem;">Reason</th>
                            <th style="padding: 18px; text-align: left; color: #d2b48c; font-weight: 600; font-size: 0.85rem;">Reporter</th>
                            <th style="padding: 18px; text-align: left; color: #d2b48c; font-weight: 600; font-size: 0.85rem;">Status</th>
                            <th style="padding: 18px; text-align: left; color: #d2b48c; font-weight: 600; font-size: 0.85rem;">Created</th>
                            <th style="padding: 18px; text-align: left; color: #d2b48c; font-weight: 600; font-size: 0.85rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTableBody">
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: rgba(210, 180, 140, 0.5);">Loading reports...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-reports-container {
        color: #d2b48c;
    }

    .admin-reports-container select {
        transition: all 0.3s ease;
    }

    .admin-reports-container select:hover,
    .admin-reports-container select:focus {
        border-color: var(--accent);
        background: rgba(255, 255, 255, 0.08);
        outline: none;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.pending {
        background: rgba(231, 76, 60, 0.2);
        color: #e74c3c;
    }

    .status-badge.reviewed {
        background: rgba(52, 152, 219, 0.2);
        color: #3498db;
    }

    .status-badge.dismissed {
        background: rgba(149, 165, 166, 0.2);
        color: #95a5a6;
    }

    .status-badge.action_taken {
        background: rgba(46, 204, 113, 0.2);
        color: #2ecc71;
    }

    .status-badge.suspended {
        background: rgba(230, 126, 34, 0.2);
        color: #e67e22;
    }

    .report-action-btn {
        background: none;
        border: 1px solid rgba(202, 164, 110, 0.3);
        color: #d2b48c;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.3s ease;
        margin-right: 5px;
    }

    .report-action-btn:hover {
        background: rgba(202, 164, 110, 0.1);
        border-color: #d2b48c;
    }

    table tbody tr {
        border-bottom: 1px solid rgba(202, 164, 110, 0.1);
        transition: background 0.3s ease;
    }

    table tbody tr:hover {
        background: rgba(202, 164, 110, 0.08);
    }

    table tbody td {
        padding: 16px 18px;
        color: rgba(210, 180, 140, 0.85);
        font-size: 0.9rem;
    }
</style>

<script>
    // Initialize reports list
    async function initReportsList() {
        await loadReportStatistics();
        await loadReports();

        // Filter change handlers
        document.getElementById('filterType')?.addEventListener('change', loadReports);
        document.getElementById('filterStatus')?.addEventListener('change', loadReports);
    }

    async function loadReportStatistics() {
        try {
            const response = await fetch('/admin/reports/statistics/overview');
            if (!response.ok) throw new Error('Failed to load statistics');
            
            const data = await response.json();
            if (data.status === 'success' && data.data) {
                const stats = data.data;
                document.getElementById('statPending').textContent = stats.total_pending || 0;
                document.getElementById('statTotal').textContent = 
                    (stats.listing_reports || 0) + (stats.message_reports || 0);
                document.getElementById('statListings').textContent = stats.listing_reports || 0;
                document.getElementById('statMessages').textContent = stats.message_reports || 0;
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    }

    async function loadReports() {
        const type = document.getElementById('filterType')?.value || '';
        const status = document.getElementById('filterStatus')?.value || '';

        try {
            let url = '/admin/reports?';
            if (type) url += 'type=' + encodeURIComponent(type) + '&';
            if (status) url += 'status=' + encodeURIComponent(status);

            const response = await fetch(url);
            if (!response.ok) throw new Error('Failed to load reports');

            const data = await response.json();
            renderReportsTable(data.data || []);
        } catch (error) {
            console.error('Error loading reports:', error);
            document.getElementById('reportsTableBody').innerHTML = 
                '<tr><td colspan="7" style="padding: 40px; text-align: center; color: #e74c3c;">Failed to load reports</td></tr>';
        }
    }

    function renderReportsTable(reports) {
        const tbody = document.getElementById('reportsTableBody');
        
        if (!reports.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="padding: 40px; text-align: center; color: rgba(210, 180, 140, 0.5);">No reports found</td></tr>';
            return;
        }

        tbody.innerHTML = reports.map(report => `
            <tr>
                <td>#${report.report_id}</td>
                <td><span style="background: rgba(202, 164, 110, 0.2); padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">${report.report_type === 'listing' ? 'Listing' : 'Message'}</span></td>
                <td>${escapeHtml(report.reason)}${report.other_reason ? ' - ' + escapeHtml(report.other_reason) : ''}</td>
                <td>${escapeHtml(report.reporter_first_name || '')} ${escapeHtml(report.reporter_last_name || '')}</td>
                <td><span class="status-badge ${report.status}">${report.status.replace('_', ' ').toUpperCase()}</span></td>
                <td>${new Date(report.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="report-action-btn" onclick="openReportDetail(${report.report_id})">View</button>
                    <button class="report-action-btn" onclick="updateReportStatus(${report.report_id})">Update</button>
                </td>
            </tr>
        `).join('');
    }

    function openReportDetail(reportId) {
        window.location.href = `/admin/reports/${reportId}`;
    }

    function updateReportStatus(reportId) {
        // Open a modal or redirect to detail page for status update
        window.location.href = `/admin/reports/${reportId}`;
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Initialize when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReportsList);
    } else {
        initReportsList();
    }
</script>
