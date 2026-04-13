<section id="users-section" class="section-content">
                <div class="content-card">
                <h3>Users Management</h3>
                
                <!-- Filter Controls -->
                <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="filterStatus" style="color: rgba(254,250,224,.8); font-weight: 600;">Status:</label>
                        <select id="filterStatus" onchange="filterUsersTable()" style="padding: 6px 10px; border-radius: 6px; border: 1px solid rgba(149,213,178,.3); background: rgba(0,0,0,.3); color: var(--cream-100); font-weight: 600; cursor: pointer;">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="filterRole" style="color: rgba(254,250,224,.8); font-weight: 600;">Role:</label>
                        <select id="filterRole" onchange="filterUsersTable()" style="padding: 6px 10px; border-radius: 6px; border: 1px solid rgba(149,213,178,.3); background: rgba(0,0,0,.3); color: var(--cream-100); font-weight: 600; cursor: pointer;">
                            <option value="">All Roles</option>
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="filterVerification" style="color: rgba(254,250,224,.8); font-weight: 600;">Verification:</label>
                        <select id="filterVerification" onchange="filterUsersTable()" style="padding: 6px 10px; border-radius: 6px; border: 1px solid rgba(149,213,178,.3); background: rgba(0,0,0,.3); color: var(--cream-100); font-weight: 600; cursor: pointer;">
                            <option value="">All</option>
                            <option value="verified">Verified</option>
                            <option value="pending">Pending</option>
                            <option value="unverified">Unverified</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px;">
                        <label for="searchUsers" style="color: rgba(254,250,224,.8); font-weight: 600;">🔍 Search:</label>
                        <input type="text" id="searchUsers" placeholder="Name, email, or ID..." oninput="searchUsersTable()" style="flex: 1; padding: 6px 10px; border-radius: 6px; border: 1px solid rgba(149,213,178,.3); background: rgba(0,0,0,.3); color: var(--cream-100); font-weight: 500; font-family: inherit;" />
                    </div>
                    
                    <button onclick="resetUsersFilter()" style="padding: 6px 14px; border-radius: 6px; border: 1px solid rgba(149,213,178,.3); background: rgba(149,213,178,.15); color: #95d5b2; font-weight: 600; cursor: pointer; transition: all .2s ease;">Reset Filters</button>
                </div>

                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Verification Approval</th>
                            <th>Report History</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($users) && is_array($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="user-row" onclick="viewUserDetailsModal('<?= esc($user['user_id']) ?>', '<?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>', '<?= esc($user['email']) ?>', '<?= ucfirst($user['roles'] ?? 'buyer') ?>', '<?= ($user['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>', '<?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'Mar 15, 2026' ?>', '<?= ucfirst($user['verification_status'] ?? 'pending') ?>', '<?= $user['reports_filed'] ?? 0 ?>', '<?= $user['reports_against'] ?? 0 ?>')" data-status="<?= ($user['is_active'] ?? 0) ? 'active' : 'inactive' ?>" data-role="<?= strtolower($user['roles'] ?? 'buyer') ?>" data-verification="<?= strtolower($user['verification_status'] ?? 'pending') ?>">
                                    <td><?= esc($user['user_id']) ?></td>
                                    <td><?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?></td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td><?= esc(ucfirst($user['roles'] ?? 'buyer')) ?></td>
                                    <td><span class="badge <?= ($user['is_active'] ?? 0) ? 'available' : 'closed' ?>"><?= ($user['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span></td>
                                    <td><?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'Mar 15, 2026' ?></td>
                                    <td>
                                        <?php $verifyStatus = strtolower($user['verification_status'] ?? 'pending'); ?>
                                        <span class="badge <?= $verifyStatus === 'verified' ? 'available' : ($verifyStatus === 'pending' ? 'pending' : 'closed') ?>">
                                            <?= ucfirst($verifyStatus) ?>
                                        </span>
                                    </td>
                                    <td onclick="event.stopPropagation();">
                                        <div style="display: flex; gap: 8px; font-size: .85rem;">
                                            <span class="report-badge filed" title="Reports Filed by User" data-action="viewUserReportHistory" data-user-id="<?php echo esc($user['user_id'] ?? 'U001'); ?>" data-user-name="<?php echo esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?>" data-report-type="filed" style="cursor: pointer;">
                                                📤 <?= $user['reports_filed'] ?? 0 ?>
                                            </span>
                                            <span class="report-badge against" title="Reports Against User" data-action="viewUserReportHistory" data-user-id="<?php echo esc($user['user_id'] ?? 'U001'); ?>" data-user-name="<?php echo esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?>" data-report-type="against" style="cursor: pointer;">
                                                📥 <?= $user['reports_against'] ?? 0 ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td onclick="event.stopPropagation();">
                                        <?php if (! empty($user['is_active'])): ?>
                                            <form action="/admin/users/<?= esc($user['user_id']) ?>/deactivate" method="post" style="display:inline"><?= csrf_field() ?><button class="btn btn-warning btn-sm" type="submit">Deactivate</button></form>
                                        <?php else: ?>
                                            <form action="/admin/users/<?= esc($user['user_id']) ?>/activate" method="post" style="display:inline"><?= csrf_field() ?><button class="btn btn-success btn-sm" type="submit">Activate</button></form>
                                        <?php endif; ?>
                                        <form action="/admin/users/<?= esc($user['user_id']) ?>/delete" method="post" style="display:inline" onsubmit="return confirm('Delete user?')"><?= csrf_field() ?><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Sample users for visualization -->
                            <tr class="user-row" onclick="viewUserDetailsModal('U001', 'John Buyer', 'john.buyer@email.com', 'Buyer', 'Active', 'Mar 15, 2026', 'Verified', '0', '0')" data-status="active" data-role="buyer" data-verification="verified">
                                <td>U001</td>
                                <td>John Buyer</td>
                                <td>john.buyer@email.com</td>
                                <td>Buyer</td>
                                <td><span class="badge available">Active</span></td>
                                <td>Mar 15, 2026</td>
                                <td><span class="badge available">Verified</span></td>
                                <td onclick="event.stopPropagation();">
                                    <div style="display: flex; gap: 8px; font-size: .85rem;">
                                        <span class="report-badge filed" title="Reports Filed by User" onclick="viewUserReportHistory('U001', 'John Buyer', 'filed')" style="cursor: pointer;">📤 0</span>
                                        <span class="report-badge against" title="Reports Against User" onclick="viewUserReportHistory('U001', 'John Buyer', 'against')" style="cursor: pointer;">📥 0</span>
                                    </div>
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <form action="/admin/users/U001/deactivate" method="post" style="display:inline"><button class="btn btn-warning btn-sm" type="submit">Deactivate</button></form>
                                    <form action="/admin/users/U001/delete" method="post" style="display:inline" onsubmit="return confirm('Delete user?')"><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                                </td>
                            </tr>
                            <tr class="user-row" onclick="viewUserDetailsModal('U002', 'Maria Santos', 'maria.santos@email.com', 'Seller', 'Active', 'Feb 28, 2026', 'Verified', '0', '1')" data-status="active" data-role="seller" data-verification="verified">
                                <td>U002</td>
                                <td>Maria Santos</td>
                                <td>maria.santos@email.com</td>
                                <td>Seller</td>
                                <td><span class="badge available">Active</span></td>
                                <td>Feb 28, 2026</td>
                                <td><span class="badge available">Verified</span></td>
                                <td onclick="event.stopPropagation();">
                                    <div style="display: flex; gap: 8px; font-size: .85rem;">
                                        <span class="report-badge filed" title="Reports Filed by User" onclick="viewUserReportHistory('U002', 'Maria Santos', 'filed')" style="cursor: pointer;">📤 0</span>
                                        <span class="report-badge against" title="Reports Against User" onclick="viewUserReportHistory('U002', 'Maria Santos', 'against')" style="cursor: pointer;"><span style="color: #e74c3c;">📥 1</span></span>
                                    </div>
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <form action="/admin/users/U002/deactivate" method="post" style="display:inline"><button class="btn btn-warning btn-sm" type="submit">Deactivate</button></form>
                                    <form action="/admin/users/U002/delete" method="post" style="display:inline" onsubmit="return confirm('Delete user?')"><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                                </td>
                            </tr>
                            <tr class="user-row" onclick="viewUserDetailsModal('U003', 'Juan Dela Cruz', 'juan.delacruz@email.com', 'Seller', 'Active', 'Mar 10, 2026', 'Pending', '0', '1')" data-status="active" data-role="seller" data-verification="pending">
                                <td>U003</td>
                                <td>Juan Dela Cruz</td>
                                <td>juan.delacruz@email.com</td>
                                <td>Seller</td>
                                <td><span class="badge available">Active</span></td>
                                <td>Mar 10, 2026</td>
                                <td><span class="badge pending">Pending</span></td>
                                <td onclick="event.stopPropagation();">
                                    <div style="display: flex; gap: 8px; font-size: .85rem;">
                                        <span class="report-badge filed" title="Reports Filed by User" onclick="viewUserReportHistory('U003', 'Juan Dela Cruz', 'filed')" style="cursor: pointer;">📤 0</span>
                                        <span class="report-badge against" title="Reports Against User" onclick="viewUserReportHistory('U003', 'Juan Dela Cruz', 'against')" style="cursor: pointer;"><span style="color: #e74c3c;">📥 1</span></span>
                                    </div>
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <form action="/admin/users/U003/deactivate" method="post" style="display:inline"><button class="btn btn-warning btn-sm" type="submit">Deactivate</button></form>
                                    <form action="/admin/users/U003/delete" method="post" style="display:inline" onsubmit="return confirm('Delete user?')"><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                                </td>
                            </tr>
                            <tr class="user-row" onclick="viewUserDetailsModal('U004', 'Carlos Lopez', 'carlos.lopez@email.com', 'Buyer', 'Inactive', 'Jan 20, 2026', 'Unverified', '1', '0')" data-status="inactive" data-role="buyer" data-verification="unverified">
                                <td>U004</td>
                                <td>Carlos Lopez</td>
                                <td>carlos.lopez@email.com</td>
                                <td>Buyer</td>
                                <td><span class="badge closed">Inactive</span></td>
                                <td>Jan 20, 2026</td>
                                <td><span class="badge closed">Unverified</span></td>
                                <td onclick="event.stopPropagation();">
                                    <div style="display: flex; gap: 8px; font-size: .85rem;">
                                        <span class="report-badge filed" title="Reports Filed by User" onclick="viewUserReportHistory('U004', 'Carlos Lopez', 'filed')" style="cursor: pointer;"><span style="color: #e74c3c;">📤 1</span></span>
                                        <span class="report-badge against" title="Reports Against User" onclick="viewUserReportHistory('U004', 'Carlos Lopez', 'against')" style="cursor: pointer;">📥 0</span>
                                    </div>
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <form action="/admin/users/U004/activate" method="post" style="display:inline"><button class="btn btn-success btn-sm" type="submit">Activate</button></form>
                                    <form action="/admin/users/U004/delete" method="post" style="display:inline" onsubmit="return confirm('Delete user?')"><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                                </td>
                            </tr>
                            <tr class="user-row" onclick="viewUserDetailsModal('U005', 'Admin User', 'admin@email.com', 'Admin', 'Active', 'Jan 01, 2026', 'Verified', '0', '0')" data-status="active" data-role="admin" data-verification="verified">
                                <td>U005</td>
                                <td>Admin User</td>
                                <td>admin@email.com</td>
                                <td>Admin</td>
                                <td><span class="badge available">Active</span></td>
                                <td>Jan 01, 2026</td>
                                <td><span class="badge available">Verified</span></td>
                                <td onclick="event.stopPropagation();">
                                    <div style="display: flex; gap: 8px; font-size: .85rem;">
                                        <span class="report-badge filed" title="Reports Filed by User" onclick="viewUserReportHistory('U005', 'Admin User', 'filed')" style="cursor: pointer;">📤 0</span>
                                        <span class="report-badge against" title="Reports Against User" onclick="viewUserReportHistory('U005', 'Admin User', 'against')" style="cursor: pointer;">📥 0</span>
                                    </div>
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <form action="/admin/users/U005/deactivate" method="post" style="display:inline"><button class="btn btn-warning btn-sm" type="submit">Deactivate</button></form>
                                    <form action="/admin/users/U005/delete" method="post" style="display:inline" onsubmit="return confirm('Delete user?')"><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                                </td>
                            </tr>
                            <tr class="user-row" onclick="viewUserDetailsModal('U006', 'Jane Investor', 'jane.investor@email.com', 'Buyer', 'Active', 'Mar 20, 2026', 'Pending', '1', '0')" data-status="active" data-role="buyer" data-verification="pending">
                                <td>U006</td>
                                <td>Jane Investor</td>
                                <td>jane.investor@email.com</td>
                                <td>Buyer</td>
                                <td><span class="badge available">Active</span></td>
                                <td>Mar 20, 2026</td>
                                <td><span class="badge pending">Pending</span></td>
                                <td onclick="event.stopPropagation();">
                                    <div style="display: flex; gap: 8px; font-size: .85rem;">
                                        <span class="report-badge filed" onclick="viewUserReportHistory('U006', 'Jane Investor', 'filed')" style="cursor: pointer;" title="Reports Filed by User"><span style="color: #e74c3c;">📤 1</span></span>
                                        <span class="report-badge against" onclick="viewUserReportHistory('U006', 'Jane Investor', 'against')" style="cursor: pointer;" title="Reports Against User">📥 0</span>
                                    </div>
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <form action="/admin/users/U006/deactivate" method="post" style="display:inline"><button class="btn btn-warning btn-sm" type="submit">Deactivate</button></form>
                                    <form action="/admin/users/U006/delete" method="post" style="display:inline" onsubmit="return confirm('Delete user?')"><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                                </td>
                            </tr>
                            <tr class="user-row" onclick="viewUserDetailsModal('U007', 'Ana Rodriguez', 'ana.rodriguez@email.com', 'Seller', 'Active', 'Feb 10, 2026', 'Verified', '0', '0')" data-status="active" data-role="seller" data-verification="verified">
                                <td>U007</td>
                                <td>Ana Rodriguez</td>
                                <td>ana.rodriguez@email.com</td>
                                <td>Seller</td>
                                <td><span class="badge available">Active</span></td>
                                <td>Feb 10, 2026</td>
                                <td><span class="badge available">Verified</span></td>
                                <td onclick="event.stopPropagation();">
                                    <div style="display: flex; gap: 8px; font-size: .85rem;">
                                        <span class="report-badge filed" onclick="viewUserReportHistory('U007', 'Ana Rodriguez', 'filed')" style="cursor: pointer;" title="Reports Filed by User">📤 0</span>
                                        <span class="report-badge against" onclick="viewUserReportHistory('U007', 'Ana Rodriguez', 'against')" style="cursor: pointer;" title="Reports Against User">📥 0</span>
                                    </div>
                                </td>
                                <td onclick="event.stopPropagation();">
                                    <form action="/admin/users/U007/deactivate" method="post" style="display:inline"><button class="btn btn-warning btn-sm" type="submit">Deactivate</button></form>
                                    <form action="/admin/users/U007/delete" method="post" style="display:inline" onsubmit="return confirm('Delete user?')"><button class="btn btn-danger btn-sm" type="submit">Delete</button></form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            </section>