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
                                <tr>
                                    <td style="color: rgba(255,255,255,0.35); font-family: monospace; font-size: 12px;"><?= esc($report['report_id'] ?? '') ?></td>
                                    <td style="font-weight: 500;"><?= esc($report['subject'] ?? '') ?></td>
                                    <td><?= esc($report['reported_by_name'] ?? '') ?></td>
                                    <td><?= esc($report['reported_against_name'] ?? '') ?></td>
                                    <td><span class="report-reason"><?= esc($report['reason'] ?? '') ?></span></td>
                                    <td>
                                        <?php $status = $report['status'] ?? 'pending'; ?>
                                        <span class="badge <?= $status === 'pending' ? 'pending' : ($status === 'resolved' ? 'verified' : 'suspended') ?>">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td><?= isset($report['created_at']) ? date('M d, Y', strtotime($report['created_at'])) : '' ?></td>
                                    <td style="display: flex; gap: 8px;">
                                        <button class="btn btn-neutral btn-sm" type="button" onclick="viewReportDetails('<?= esc($report['report_id'] ?? '') ?>')">View</button>
                                        <button class="btn btn-primary btn-sm" type="button" onclick="showReplyModal('<?= esc($report['report_id'] ?? '') ?>')">Resolve</button>
                                        <button class="btn btn-danger btn-sm" type="button" onclick="confirmSuspendAccount('<?= esc($report['reported_against_name'] ?? '') ?>', '<?= esc($report['report_id'] ?? '') ?>')">Suspend</button>
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