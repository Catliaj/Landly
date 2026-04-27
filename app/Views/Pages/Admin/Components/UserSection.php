<section id="users-section" class="section-content">
                <div class="content-card">
                <h3>Users Management</h3>
                
                <!-- Filter Controls -->
                <div class="filter-bar">
                    <div style="display: flex; align-items: center;">
                        <label for="filterStatus">Status:</label>
                        <select id="filterStatus" onchange="filterUsersTable()" style="margin-left: 0;">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; align-items: center;">
                        <label for="filterRole">Role:</label>
                        <select id="filterRole" onchange="filterUsersTable()" style="margin-left: 0;">
                            <option value="">All Roles</option>
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div style="display: flex; align-items: center;">
                        <label for="filterVerification">Verification:</label>
                        <select id="filterVerification" onchange="filterUsersTable()" style="margin-left: 0;">
                            <option value="">All</option>
                            <option value="verified">Verified</option>
                            <option value="pending">Pending</option>
                            <option value="unverified">Unverified</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; align-items: center; flex: 1; min-width: 200px;">
                        <label for="searchUsers">🔍 Search:</label>
                        <input type="text" id="searchUsers" placeholder="Name, email, or ID..." oninput="searchUsersTable()" style="flex: 1; margin-left: 6px;" />
                    </div>
                    
                    <button onclick="resetUsersFilter()" class="btn btn-neutral">Reset Filters</button>
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
                                    <td style="color: rgba(255,255,255,0.35); font-size: 13px;"><?= esc($user['user_id']) ?></td>
                                    <td style="font-weight: 500; color: #ffffff;"><?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?></td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td>
                                        <?php $roleClass = 'role-' . strtolower($user['roles'] ?? 'buyer'); ?>
                                        <span class="badge <?= $roleClass ?>"><?= esc(ucfirst($user['roles'] ?? 'buyer')) ?></span>
                                    </td>
                                    <td><span class="badge <?= ($user['is_active'] ?? 0) ? 'active' : '' ?>"><?= ($user['is_active'] ?? 0) ? 'Active' : 'Inactive' ?></span></td>
                                    <td><?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'Mar 15, 2026' ?></td>
                                    <td>
                                        <?php $verifyStatus = strtolower($user['verification_status'] ?? 'pending'); ?>
                                        <span class="badge <?= $verifyStatus === 'verified' ? 'verified' : ($verifyStatus === 'pending' ? 'pending' : 'rejected') ?>">
                                            <?= ucfirst($verifyStatus) ?>
                                        </span>
                                    </td>
                                    <td onclick="event.stopPropagation();">
                                        <div style="display: flex; gap: 8px; font-size: 12px;">
                                            <span class="report-badge filed" title="Reports Filed by User" data-action="viewUserReportHistory" data-user-id="<?php echo esc($user['user_id'] ?? 'U001'); ?>" data-user-name="<?php echo esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?>" data-report-type="filed" style="cursor: pointer;">
                                                👤 <?= $user['reports_filed'] ?? 0 ?>
                                            </span>
                                            <span class="report-badge against" title="Reports Against User" data-action="viewUserReportHistory" data-user-id="<?php echo esc($user['user_id'] ?? 'U001'); ?>" data-user-name="<?php echo esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?>" data-report-type="against" style="cursor: pointer;">
                                                🚩 <?= $user['reports_against'] ?? 0 ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td onclick="event.stopPropagation();" style="display: flex; gap: 8px;">
                                        <?php if (! empty($user['is_active'])): ?>
                                            <button class="btn btn-danger btn-sm" type="button" onclick="confirmDeactivateUser('<?= esc($user['user_id']) ?>', '<?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>')">Deactivate</button>
                                        <?php else: ?>
                                            <button class="btn btn-primary btn-sm" type="button" onclick="confirmActivateUser('<?= esc($user['user_id']) ?>', '<?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>')">Activate</button>
                                        <?php endif; ?>
                                        <button class="btn btn-danger btn-sm" type="button" onclick="confirmDeleteUser('<?= esc($user['user_id']) ?>', '<?= esc(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="table-empty-state">No users found in the system.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            </section>