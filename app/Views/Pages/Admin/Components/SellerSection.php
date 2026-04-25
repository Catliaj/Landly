<section id="sellers-section" class="section-content">
                <div class="content-card">
                <h3>Seller Verification</h3>
                <div class="sellers-grid">
                    <?php if (!empty($sellers) && is_array($sellers)): ?>
                        <?php foreach ($sellers as $seller): ?>
                            <?php
                                $sellerId = $seller['user_id'];
                                $sellerName = esc($seller['fullname']);
                                $sellerEmail = esc($seller['email'] ?? 'N/A');
                                $sellerCreatedAt = isset($seller['created_at']) ? date('Y-m-d', strtotime($seller['created_at'])) : 'N/A';
                                $documentCount = !empty($seller['documents']) ? count($seller['documents']) : 0;
                                $documentPaths = [];
                                
                                // Build array of real document paths
                                if (!empty($seller['documents']) && is_array($seller['documents'])) {
                                    foreach ($seller['documents'] as $doc) {
                                        if (!empty($doc['file_path'])) {
                                            $documentPaths[] = base_url($doc['file_path']);
                                        }
                                    }
                                }
                            ?>
                            <div class="seller-card">
                                <div class="seller-profile-pic" style="background: linear-gradient(135deg, #2a6c62, #1f4f48); display: flex; align-items: center; justify-content: center; color: var(--cream-100); font-size: 2rem;">👤</div>
                                <div class="seller-card-info">
                                    <div class="seller-card-header">
                                        <div>
                                            <div class="seller-card-name"><?php echo $sellerName; ?></div>
                                            <div class="seller-card-id">ID: <?php echo $sellerId; ?> | Registered: <?php echo $sellerCreatedAt; ?></div>
                                        </div>
                                        <div class="seller-card-actions">
                                            <button class="btn btn-approve" type="button" onclick="approveSeller(<?php echo $sellerId; ?>)">✓ Approve</button>
                                            <button class="btn btn-reject" type="button" onclick="rejectSeller(<?php echo $sellerId; ?>)">✗ Reject</button>
                                        </div>
                                    </div>
                                    <div class="seller-card-details">
                                        <strong>Email:</strong> <?php echo $sellerEmail; ?><br>
                                        <strong>Documents:</strong> <?php echo $documentCount; ?> file(s) uploaded
                                    </div>
                                    <?php if ($documentCount > 0): ?>
                                        <div class="seller-documents">
                                            <div class="seller-documents-title">📄 Documents Uploaded</div>
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
                                            <div class="seller-documents-title">📄 Documents Uploaded</div>
                                            <p style="color: rgba(254,250,224,.65); font-size: 0.85rem;">No documents uploaded yet</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="seller-card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <p style="color: rgba(254,250,224,.65);">No sellers found in the system.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </section>