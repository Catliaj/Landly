<section id="reports-section" class="section-content">
                <div class="content-card">
                <h3>Reports & Disputes Management</h3>
                
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div style="display: flex; align-items: center;">
                        <label for="filterReportStatus">Status:</label>
                        <select id="filterReportStatus" onchange="filterReportsTable()" style="margin-left: 0;">
                            <option value="">All Reports</option>
                            <option value="pending">Pending</option>
                            <option value="resolved">Resolved</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; align-items: center; flex: 1; min-width: 200px;">
                        <label for="searchReports">🔍 Search:</label>
                        <input type="text" id="searchReports" placeholder="Subject or reported user..." oninput="searchReportsTable()" style="flex: 1; margin-left: 6px;" />
                    </div>
                    
                    <button onclick="resetReportsFilter()" class="btn btn-neutral">Reset Filters</button>
                </div>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Subject</th>
                                <th>Reported By</th>
                                <th>Against</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (! empty($reports) && is_array($reports)): ?>
                            <?php foreach ($reports as $report): ?>
                                <tr class="report-row" data-report-status="<?= esc($report['status'] ?? 'pending') ?>">
                                    <td style="color: rgba(255,255,255,0.35); font-family: monospace; font-size: 12px;"><?= esc($report['report_id'] ?? '') ?></td>
                                    <td style="font-weight: 500;"><?= esc($report['subject'] ?? '') ?></td>
                                    <td><?= esc($report['reported_by_name'] ?? '') ?></td>
                                    <td><?= esc($report['reported_against_name'] ?? '') ?></td>
                                    <td><span class="report-reason"><?= esc($report['reason'] ?? '') ?></span></td>
                                    <td>
                                        <?php $status = $report['status'] ?? 'pending'; ?>
                                        <?php
                                            $badgeClass = 'suspended';
                                            $statusLabel = ucfirst($status);

                                            if ($status === 'pending') {
                                                $badgeClass = 'pending';
                                            } elseif ($status === 'reviewed' || $status === 'action_taken' || $status === 'resolved') {
                                                $badgeClass = 'verified';
                                                $statusLabel = $status === 'action_taken' ? 'Action Taken' : 'Reviewed';
                                            } elseif ($status === 'dismissed') {
                                                $badgeClass = 'suspended';
                                                $statusLabel = 'Dismissed';
                                            }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= esc($statusLabel) ?>
                                        </span>
                                    </td>
                                    <td><?= isset($report['created_at']) ? date('M d, Y', strtotime($report['created_at'])) : '' ?></td>
                                    <td class="report-action-cell">
                                        <button class="btn btn-neutral btn-sm" type="button" onclick="viewReportDetails('<?= esc($report['report_id'] ?? '') ?>')">View</button>
                                        <?php if ($status === 'pending'): ?>
                                            <button class="btn btn-primary btn-sm" type="button" onclick="showReplyModal('<?= esc($report['report_id'] ?? '') ?>')">Resolve</button>
                                            <button class="btn btn-danger btn-sm" type="button" onclick="confirmSuspendAccount('<?= esc($report['reported_against_name'] ?? '') ?>', '<?= esc($report['report_id'] ?? '') ?>')">Suspend</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="table-empty-state">No reports found in the system.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </section>

            <script>
                function filterReportsTable() {
                    const statusSelect = document.getElementById('filterReportStatus');
                    const searchInput = document.getElementById('searchReports');
                    const selectedStatus = statusSelect ? statusSelect.value.trim().toLowerCase() : '';
                    const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : '';
                    const rows = document.querySelectorAll('#reports-section .report-row');

                    rows.forEach(row => {
                        const rowStatus = (row.dataset.reportStatus || '').toLowerCase();
                        const subjectCell = row.cells[1] ? row.cells[1].textContent.trim().toLowerCase() : '';
                        const reportedBy = row.cells[2] ? row.cells[2].textContent.trim().toLowerCase() : '';
                        const reportedAgainst = row.cells[3] ? row.cells[3].textContent.trim().toLowerCase() : '';
                        const statusText = row.cells[5] ? row.cells[5].textContent.trim().toLowerCase() : '';

                        let matchesStatus = true;
                        if (selectedStatus) {
                            if (selectedStatus === 'resolved') {
                                matchesStatus = ['reviewed', 'action_taken', 'resolved'].includes(rowStatus) || statusText.includes('reviewed') || statusText.includes('action taken') || statusText.includes('resolved');
                            } else {
                                matchesStatus = rowStatus === selectedStatus || statusText.includes(selectedStatus);
                            }
                        }

                        const matchesSearch = !searchTerm || subjectCell.includes(searchTerm) || reportedBy.includes(searchTerm) || reportedAgainst.includes(searchTerm);
                        row.style.display = matchesStatus && matchesSearch ? '' : 'none';
                    });
                }

                function searchReportsTable() {
                    filterReportsTable();
                }

                function resetReportsFilter() {
                    const statusSelect = document.getElementById('filterReportStatus');
                    const searchInput = document.getElementById('searchReports');
                    if (statusSelect) statusSelect.value = '';
                    if (searchInput) searchInput.value = '';
                    filterReportsTable();
                }

                document.addEventListener('DOMContentLoaded', function() {
                    filterReportsTable();
                });
            </script>