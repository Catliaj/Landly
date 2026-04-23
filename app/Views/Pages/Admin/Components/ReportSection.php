<section id="reports-section" class="section-content">
                <div class="content-card">
                <h3>Reports & Disputes Management</h3>
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
                                    <td><?= esc($report['report_id'] ?? 'R001') ?></td>
                                    <td><?= esc($report['subject'] ?? 'Fake Documents') ?></td>
                                    <td><?= esc($report['reported_by_name'] ?? 'John Buyer') ?></td>
                                    <td><?= esc($report['reported_against_name'] ?? 'Maria Santos') ?></td>
                                    <td><span class="report-reason"><?= esc($report['reason'] ?? 'Fraudulent Documents') ?></span></td>
                                    <td><span class="badge <?= ($report['status'] ?? 'pending') === 'pending' ? 'pending' : (($report['status'] ?? '') === 'resolved' ? 'available' : 'closed') ?>"><?= ucfirst($report['status'] ?? 'Pending') ?></span></td>
                                    <td><?= isset($report['created_at']) ? date('M d, Y', strtotime($report['created_at'])) : 'Apr 10, 2026' ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-view" type="button" onclick="viewReportDetails('<?= esc($report['report_id'] ?? '1') ?>')">View</button>
                                        <button class="btn btn-sm btn-success" type="button" onclick="showReplyModal('<?= esc($report['report_id'] ?? '1') ?>')">Reply</button>
                                        <button class="btn btn-sm btn-danger" type="button" onclick="confirmSuspendAccount('<?= esc($report['reported_against_name'] ?? '') ?>', '<?= esc($report['report_id'] ?? '1') ?>')">Suspend</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </section>