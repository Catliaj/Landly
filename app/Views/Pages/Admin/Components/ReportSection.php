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
                                        <button class="btn btn-sm btn-view" type="button" data-action="viewReportDetails" data-report-id="<?php echo esc($report['report_id'] ?? '1'); ?>">View</button>
                                        <button class="btn btn-sm btn-success" type="button" data-action="showReplyModal" data-report-id="<?php echo esc($report['report_id'] ?? '1'); ?>">Reply</button>
                                        <button class="btn btn-sm btn-danger" type="button" data-action="suspendAccount" data-user-name="<?php echo esc($report['reported_against_name'] ?? ''); ?>" data-report-id="<?php echo esc($report['report_id'] ?? '1'); ?>">Suspend</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td>R001</td>
                                <td>Fake Documents</td>
                                <td>John Buyer</td>
                                <td>Maria Santos</td>
                                <td><span class="report-reason">Fraudulent Documents Submitted</span></td>
                                <td><span class="badge pending">Pending</span></td>
                                <td>Apr 10, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-view" type="button" onclick="viewReportDetails('R001')">View</button>
                                    <button class="btn btn-sm btn-success" type="button" onclick="showReplyModal('R001')">Reply</button>
                                    <button class="btn btn-sm btn-danger" type="button" onclick="suspendAccount('Maria Santos', 'R001')">Suspend</button>
                                </td>
                            </tr>
                            <tr>
                                <td>R002</td>
                                <td>Misrepresented Property</td>
                                <td>Jane Investor</td>
                                <td>Juan Dela Cruz</td>
                                <td><span class="report-reason">Property Photos Don't Match Actual</span></td>
                                <td><span class="badge pending">Pending</span></td>
                                <td>Apr 09, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-view" type="button" onclick="viewReportDetails('R002')">View</button>
                                    <button class="btn btn-sm btn-success" type="button" onclick="showReplyModal('R002')">Reply</button>
                                    <button class="btn btn-sm btn-danger" type="button" onclick="suspendAccount('Juan Dela Cruz', 'R002')">Suspend</button>
                                </td>
                            </tr>
                            <tr>
                                <td>R003</td>
                                <td>Unprofessional Conduct</td>
                                <td>Ana Rodriguez</td>
                                <td>Carlos Lopez</td>
                                <td><span class="report-reason">Harassment and Rude Behavior</span></td>
                                <td><span class="badge available">Resolved</span></td>
                                <td>Apr 08, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-view" type="button" onclick="viewReportDetails('R003')">View</button>
                                    <button class="btn btn-sm btn-success" type="button" onclick="showReplyModal('R003')">Reply</button>
                                    <button class="btn btn-sm btn-danger" type="button" onclick="suspendAccount('Carlos Lopez', 'R003')">Suspend</button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </section>