<section id="sellers-section" class="section-content">
                <div class="content-card">
                <h3>Seller Verification</h3>
                
                <!-- Status Filter Tabs -->
                <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid rgba(149,213,178,.2); padding-bottom: 12px;">
                    <button class="seller-filter-btn active" data-filter="all" onclick="filterSellersByStatus('all')" style="padding: 6px 14px; border: none; background: rgba(149,213,178,.2); color: var(--accent); font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">All Sellers</button>
                    <button class="seller-filter-btn" data-filter="pending" onclick="filterSellersByStatus('pending')" style="padding: 6px 14px; border: none; background: transparent; color: rgba(254,250,224,.6); font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">⏳ Pending</button>
                    <button class="seller-filter-btn" data-filter="verified" onclick="filterSellersByStatus('verified')" style="padding: 6px 14px; border: none; background: transparent; color: rgba(254,250,224,.6); font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">✅ Verified</button>
                    <button class="seller-filter-btn" data-filter="rejected" onclick="filterSellersByStatus('rejected')" style="padding: 6px 14px; border: none; background: transparent; color: rgba(254,250,224,.6); font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">❌ Rejected</button>
                </div>
                
                <div class="sellers-grid" id="sellersGrid">
                    <?php if (!empty($sellers) && is_array($sellers)): ?>
                        <?php foreach ($sellers as $seller): ?>
                            <?php
                                $sellerId = $seller['user_id'];
                                $sellerName = esc($seller['fullname']);
                                $sellerEmail = esc($seller['email'] ?? 'N/A');
                                $sellerCreatedAt = isset($seller['created_at']) ? date('Y-m-d', strtotime($seller['created_at'])) : 'N/A';
                                $documentCount = !empty($seller['documents']) ? count($seller['documents']) : 0;
                                $nameInitial = strtoupper(substr($sellerName, 0, 1));
                                
                                // Use profile picture URL resolved in controller
                                $avatarUrl = $seller['profile_picture_url'] ?? null;
                                
                                // Determine verification status from documents
                                $verificationStatus = 'pending';
                                $statusBadge = '⏳ Pending';
                                if (!empty($seller['documents']) && is_array($seller['documents'])) {
                                    $allVerified = true;
                                    $hasRejected = false;
                                    foreach ($seller['documents'] as $doc) {
                                        if (isset($doc['is_verified']) && $doc['is_verified'] == 0) {
                                            $allVerified = false;
                                            if (!empty($doc['reviewed_at'])) {
                                                $hasRejected = true;
                                            }
                                        }
                                    }
                                    if ($hasRejected) {
                                        $verificationStatus = 'rejected';
                                        $statusBadge = '❌ Rejected';
                                    } elseif ($allVerified && !empty($seller['documents'])) {
                                        $verificationStatus = 'verified';
                                        $statusBadge = '✅ Verified';
                                    }
                                }
                                
                                // Build array of real document paths
                                $documentPaths = [];
                                if (!empty($seller['documents']) && is_array($seller['documents'])) {
                                    foreach ($seller['documents'] as $doc) {
                                        if (!empty($doc['file_path'])) {
                                            $documentPaths[] = base_url($doc['file_path']);
                                        }
                                    }
                                }
                            ?>
                            <div class="seller-card" data-status="<?= $verificationStatus ?>">
                                <div class="seller-card-header" style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
                                    <!-- Profile Photo Avatar -->
                                    <div class="seller-profile-pic" style="width: 64px; height: 64px; min-width: 64px; border-radius: 8px; background: linear-gradient(135deg, #2a7a6a, #1a5a4a); display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; color: #ffffff; overflow: hidden;">
                                        <?php if ($avatarUrl): ?>
                                            <img src="<?= esc($avatarUrl) ?>" alt="<?= esc($sellerName) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <?= $nameInitial ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Name Block -->
                                    <div style="flex: 1;">
                                        <div class="seller-card-name"><?php echo $sellerName; ?></div>
                                        <div class="seller-card-id">ID: <?php echo $sellerId; ?> | Registered: <?php echo $sellerCreatedAt; ?></div>
                                    </div>
                                    
                                    <!-- Status Badge (Top-Right) -->
                                    <span class="seller-status-badge" style="margin-left: auto; align-self: flex-start; font-size: 11px; padding: 4px 14px; border-radius: 20px; font-weight: 500; white-space: nowrap; letter-spacing: 0.3px; border: 1px solid; <?= 
                                        $verificationStatus === 'verified' ? 'background: #0d3d1f; color: #4ade80; border-color: #1a6b38;' : 
                                        ($verificationStatus === 'rejected' ? 'background: #3d0d0d; color: #f87171; border-color: #6b1a1a;' : 
                                        'background: #3d2e00; color: #fbbf24; border-color: #6b4f00;')
                                    ?>"><?= $statusBadge ?></span>
                                </div>
                                <div class="seller-card-details">
                                    <strong>Email:</strong> <?php echo $sellerEmail; ?><br>
                                    <strong>Documents:</strong> <?php echo $documentCount; ?> file(s) uploaded
                                </div>
                                <?php if ($documentCount > 0): ?>
                                    <div class="seller-documents">
                                        <span class="seller-documents-title">Documents Uploaded</span>
                                        <?php 
                                            $documentUrls = [];
                                            if (!empty($seller['documents']) && is_array($seller['documents'])) {
                                                foreach ($seller['documents'] as $doc) {
                                                    $documentUrls[] = [
                                                        'id' => $doc['document_id'],
                                                        'type' => $doc['document_type'],
                                                        'url' => base_url('admin/document/' . $doc['document_id'] . '/view')
                                                    ];
                                                }
                                            }
                                            $dataJson = htmlspecialchars(json_encode($documentUrls), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <button class="btn-files documents-gallery-btn" data-seller-name="<?php echo esc($sellerName); ?>" data-documents="<?php echo $dataJson; ?>">View <?php echo $documentCount; ?> File<?php echo $documentCount > 1 ? 's' : ''; ?></button>
                                    </div>
                                <?php else: ?>
                                    <div class="seller-documents">
                                        <span class="seller-documents-title">Documents Uploaded</span>
                                        <p style="color: rgba(255,255,255,0.35); font-size: 0.85rem; margin-top: 4px;">No documents uploaded yet</p>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Conditional Action Buttons -->
                                <div class="card-actions" style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.07);">
                                    <?php if ($verificationStatus === 'pending'): ?>
                                        <button class="btn-approve" type="button" onclick="approveSeller(<?php echo $sellerId; ?>)" style="background: #1a6b38; color: #fff; padding: 7px 18px; border-radius: 7px; font-size: 13px; border: none; cursor: pointer; transition: background 0.2s ease;">✓ Approve</button>
                                        <button class="btn-reject" type="button" onclick="rejectSeller(<?php echo $sellerId; ?>)" style="background: #6b1a1a; color: #fff; padding: 7px 18px; border-radius: 7px; font-size: 13px; border: none; cursor: pointer; transition: background 0.2s ease;">✗ Reject</button>
                                    <?php elseif ($verificationStatus === 'verified'): ?>
                                        <span class="status-done" style="font-size: 12px; font-style: italic; color: rgba(255,255,255,0.3);">Account verified</span>
                                    <?php elseif ($verificationStatus === 'rejected'): ?>
                                        <span class="status-done" style="font-size: 12px; font-style: italic; color: rgba(255,255,255,0.3);">Account rejected</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 48px;">
                            <p class="table-empty-state">No sellers found in the system.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </section>