<?php
/**
 * Admin Report Detail View
 * Display full report details with ability to update status and add notes
 */
?>

<div class="admin-report-detail" style="padding: 30px; background: linear-gradient(135deg, #183127 0%, #234236 100%); min-height: 100vh;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <!-- Back Button -->
        <a href="/admin/reports" style="display: inline-flex; align-items: center; gap: 8px; color: #d2b48c; margin-bottom: 20px; text-decoration: none; transition: color 0.3s ease;" onmouseover="this.style.color='#e74c3c';" onmouseout="this.style.color='#d2b48c';">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back to Reports
        </a>

        <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(202, 164, 110, 0.2); border-radius: 12px; padding: 30px; margin-bottom: 20px;">
            <!-- Report Header -->
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 25px; gap: 20px; flex-wrap: wrap;">
                <div>
                    <h1 style="color: #d2b48c; font-size: 1.8rem; font-weight: 700; margin: 0 0 10px;">Report #<span id="reportId">--</span></h1>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <span style="background: rgba(202, 164, 110, 0.2); padding: 6px 12px; border-radius: 8px; color: #d2b48c; font-size: 0.85rem;">
                            <span id="reportType">--</span>
                        </span>
                        <span class="status-badge" id="statusBadge">PENDING</span>
                        <span style="color: rgba(210, 180, 140, 0.6); font-size: 0.85rem;">Created: <span id="createdAt">--</span></span>
                    </div>
                </div>
                <select id="statusSelect" style="padding: 10px 15px; border-radius: 8px; border: 1px solid rgba(202, 164, 110, 0.3); background: rgba(255, 255, 255, 0.05); color: #d2b48c; cursor: pointer;">
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="dismissed">Dismissed</option>
                    <option value="action_taken">Action Taken</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>

            <!-- Report Details Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <!-- Reason -->
                <div>
                    <h3 style="color: rgba(210, 180, 140, 0.8); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin: 0 0 8px;">Reason</h3>
                    <p style="color: #d2b48c; margin: 0; font-size: 0.95rem;" id="reportReason">--</p>
                    <p style="color: rgba(210, 180, 140, 0.6); margin: 8px 0 0; font-size: 0.85rem;" id="otherReasonText"></p>
                </div>

                <!-- Reporter -->
                <div>
                    <h3 style="color: rgba(210, 180, 140, 0.8); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin: 0 0 8px;">Reported By</h3>
                    <p style="color: #d2b48c; margin: 0; font-size: 0.95rem;">
                        <span id="reporterName">--</span>
                        <br><span style="color: rgba(210, 180, 140, 0.6); font-size: 0.85rem;" id="reporterEmail">--</span>
                    </p>
                </div>

                <!-- Reported User (if applicable) -->
                <div id="reportedUserSection">
                    <h3 style="color: rgba(210, 180, 140, 0.8); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin: 0 0 8px;">Reported User</h3>
                    <p style="color: #d2b48c; margin: 0; font-size: 0.95rem;">
                        <span id="reportedName">--</span>
                        <br><span style="color: rgba(210, 180, 140, 0.6); font-size: 0.85rem;" id="reportedEmail">--</span>
                    </p>
                </div>

                <!-- Target Reference -->
                <div id="targetRefSection">
                    <h3 style="color: rgba(210, 180, 140, 0.8); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin: 0 0 8px;">Reference</h3>
                    <p style="color: #d2b48c; margin: 0; font-size: 0.95rem;" id="targetRef">--</p>
                </div>
            </div>

            <!-- Description -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: rgba(210, 180, 140, 0.8); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin: 0 0 10px;">Description</h3>
                <p style="color: rgba(210, 180, 140, 0.75); margin: 0; line-height: 1.6; background: rgba(255, 255, 255, 0.02); padding: 15px; border-radius: 8px; border-left: 2px solid var(--accent);" id="reportDescription">No description provided.</p>
            </div>

            <!-- Evidence -->
            <div id="evidenceSection" style="margin-bottom: 25px;">
                <h3 style="color: rgba(210, 180, 140, 0.8); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin: 0 0 10px;">Evidence</h3>
                <a id="evidenceLink" href="#" target="_blank" style="color: var(--accent); text-decoration: underline; cursor: pointer;">View Evidence</a>
            </div>

            <!-- Review Info -->
            <div style="background: rgba(202, 164, 110, 0.08); border: 1px solid rgba(202, 164, 110, 0.2); border-radius: 8px; padding: 15px; margin-bottom: 25px;">
                <p style="margin: 0; color: rgba(210, 180, 140, 0.7); font-size: 0.85rem;">
                    <strong>Last Reviewed By:</strong> <span id="reviewedBy">Not yet reviewed</span><br>
                    <strong>Review Date:</strong> <span id="reviewDate">--</span>
                </p>
            </div>

            <!-- Admin Notes -->
            <div style="margin-bottom: 25px;">
                <h3 style="color: rgba(210, 180, 140, 0.8); font-size: 0.85rem; font-weight: 600; text-transform: uppercase; margin: 0 0 10px;">Admin Notes</h3>
                <textarea id="adminNotes" placeholder="Add internal notes about this report..." style="width: 100%; min-height: 120px; padding: 15px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(202, 164, 110, 0.2); border-radius: 8px; color: #d2b48c; font-family: inherit; resize: vertical;"></textarea>
                <small style="color: rgba(210, 180, 140, 0.5); display: block; margin-top: 5px;">Max 5000 characters</small>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <button id="updateBtn" class="action-btn" style="background: linear-gradient(135deg, var(--accent), var(--accent-dark)); color: var(--green-900); padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;" onclick="saveReportUpdate()">
                    Save Changes
                </button>
                <button id="suspendBtn" class="action-btn" style="background: linear-gradient(135deg, #e67e22, #d35400); color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;" onclick="suspendReportWithTargets()">
                    Suspend
                </button>
                <button id="dismissBtn" class="action-btn secondary" style="background: transparent; border: 1px solid rgba(202, 164, 110, 0.3); color: #d2b48c; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;" onclick="dismissReport()">
                    Dismiss Report
                </button>
                <button id="backBtn" class="action-btn secondary" style="background: transparent; border: 1px solid rgba(202, 164, 110, 0.3); color: #d2b48c; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;" onclick="window.history.back()">
                    Back
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-report-detail {
        color: #d2b48c;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 8px;
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

    textarea {
        font-size: 0.9rem;
    }

    .action-btn {
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }

    .action-btn.secondary:hover {
        background: rgba(202, 164, 110, 0.1);
        border-color: #d2b48c;
    }
</style>

<script>
    let reportId = null;
    let currentReport = null;

    async function loadReport() {
        const pathParts = window.location.pathname.split('/');
        reportId = parseInt(pathParts[pathParts.length - 1]);

        if (!reportId || isNaN(reportId)) {
            document.body.innerHTML = '<p style="color: #e74c3c; padding: 20px;">Invalid report ID</p>';
            return;
        }

        try {
            const response = await fetch(`/admin/reports/${reportId}`);
            if (!response.ok) throw new Error('Failed to load report');

            const data = await response.json();
            currentReport = data.data;
            renderReportDetail();
        } catch (error) {
            console.error('Error loading report:', error);
            document.body.innerHTML = '<p style="color: #e74c3c; padding: 20px;">Failed to load report details</p>';
        }
    }

    function renderReportDetail() {
        if (!currentReport) return;

        // Basic info
        document.getElementById('reportId').textContent = currentReport.report_id;
        document.getElementById('reportType').textContent = currentReport.report_type === 'listing' ? 'Listing Report' : 'Message Report';
        document.getElementById('createdAt').textContent = new Date(currentReport.created_at).toLocaleString();
        document.getElementById('statusSelect').value = currentReport.status;
        updateStatusBadge(currentReport.status);

        // Reason
        document.getElementById('reportReason').textContent = currentReport.reason;
        if (currentReport.other_reason) {
            document.getElementById('otherReasonText').textContent = 'Other: ' + escapeHtml(currentReport.other_reason);
        }

        // Reporter
        document.getElementById('reporterName').textContent = `${currentReport.reporter_first_name || ''} ${currentReport.reporter_last_name || ''}`.trim();
        document.getElementById('reporterEmail').textContent = currentReport.reporter_email || 'N/A';

        // Reported user (if applicable)
        if (currentReport.reported_first_name) {
            document.getElementById('reportedName').textContent = `${currentReport.reported_first_name || ''} ${currentReport.reported_last_name || ''}`.trim();
            document.getElementById('reportedEmail').textContent = currentReport.reported_email || 'N/A';
        } else {
            document.getElementById('reportedUserSection').style.display = 'none';
        }

        // Target reference
        if (currentReport.report_type === 'listing' && currentReport.listing_title) {
            document.getElementById('targetRef').innerHTML = `<strong>Listing:</strong> ${escapeHtml(currentReport.listing_title)}`;
        } else if (currentReport.report_type === 'message') {
            document.getElementById('targetRef').innerHTML = `<strong>Message Session:</strong> ID ${currentReport.session_id}`;
        }

        // Description
        document.getElementById('reportDescription').textContent = currentReport.description || 'No description provided.';

        // Evidence
        if (currentReport.evidence_path) {
            document.getElementById('evidenceSection').style.display = 'block';
            document.getElementById('evidenceLink').href = `/media/download?path=${encodeURIComponent(currentReport.evidence_path)}`;
            document.getElementById('evidenceLink').textContent = 'Download Evidence (' + getFileExtension(currentReport.evidence_path) + ')';
        } else {
            document.getElementById('evidenceSection').style.display = 'none';
        }

        // Review info
        if (currentReport.reviewed_by && currentReport.reviewed_at) {
            document.getElementById('reviewedBy').textContent = `User #${currentReport.reviewed_by}`;
            document.getElementById('reviewDate').textContent = new Date(currentReport.reviewed_at).toLocaleString();
        }

        // Admin notes
        document.getElementById('adminNotes').value = currentReport.admin_notes || '';
    }

    function updateStatusBadge(status) {
        const badge = document.getElementById('statusBadge');
        badge.className = 'status-badge ' + status;
        badge.textContent = status.replace('_', ' ').toUpperCase();
    }

    async function saveReportUpdate() {
        const btn = document.getElementById('updateBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving...';

        try {
            const response = await fetch(`/admin/reports/${reportId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    status: document.getElementById('statusSelect').value,
                    admin_notes: document.getElementById('adminNotes').value
                })
            });

            if (!response.ok) throw new Error('Failed to update report');

            const data = await response.json();
            
            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Report Updated',
                    text: 'Report status has been updated successfully.',
                    customClass: {
                        popup: 'sweet-alert-popup',
                        confirmButton: 'sweet-alert-button'
                    }
                });
            } else {
                alert('Report updated successfully!');
            }

            // Reload report
            await loadReport();
        } catch (error) {
            console.error('Error updating report:', error);
            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update report. Please try again.'
                });
            } else {
                alert('Failed to update report');
            }
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    async function dismissReport() {
        if (confirm('Are you sure you want to dismiss this report?')) {
            document.getElementById('statusSelect').value = 'dismissed';
            await saveReportUpdate();
        }
    }

    async function suspendReportWithTargets() {
        if (!confirm('Are you sure you want to suspend this report?\n\nThis will:\n- Mark the report as Suspended\n- Suspend the reported user account\n- Suspend the listing (if applicable)\n\nThis action cannot be easily undone!')) {
            return;
        }

        const btn = document.getElementById('suspendBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Suspending...';

        try {
            const response = await fetch(`/admin/reports/${reportId}/suspend`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    admin_notes: document.getElementById('adminNotes').value
                })
            });

            if (!response.ok) throw new Error('Failed to suspend report');

            const data = await response.json();
            
            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Report Suspended',
                    html: 'The report has been suspended and the following actions were taken:<br><br>' +
                          '✓ Report status updated to Suspended<br>' +
                          '✓ Reported user account has been suspended<br>' +
                          (currentReport.report_type === 'listing' ? '✓ Listing has been suspended<br>' : '') +
                          '<br><small>All related parties have been notified of these actions.</small>',
                    customClass: {
                        popup: 'sweet-alert-popup',
                        confirmButton: 'sweet-alert-button'
                    }
                });
            } else {
                alert('Report suspended successfully!');
            }

            // Reload report
            await loadReport();
        } catch (error) {
            console.error('Error suspending report:', error);
            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to suspend report. Please try again.'
                });
            } else {
                alert('Failed to suspend report');
            }
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    }

    function getFileExtension(path) {
        return path.split('.').pop().toUpperCase();
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

    // Load report on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadReport);
    } else {
        loadReport();
    }
</script>
