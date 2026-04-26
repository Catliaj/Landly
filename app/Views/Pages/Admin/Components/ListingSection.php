<section id="listings-section" class="section-content">
                <div class="content-card">
                <h3>Land Listings Management</h3>
                <div class="listings-grid">
                    <?php if (!empty($listings) && is_array($listings)): ?>
                        <?php 
                            // Create a map of user IDs to names for quick lookup
                            $sellerMap = [];
                            if (!empty($users) && is_array($users)) {
                                foreach ($users as $user) {
                                    $sellerMap[$user['user_id']] = $user['fullname'] ?? 'Unknown Seller';
                                }
                            }
                        ?>
                        <?php foreach ($listings as $listing): ?>
                            <?php
                                $sellerId = $listing['seller_id'] ?? null;
                                $sellerName = $sellerMap[$sellerId] ?? 'Unknown Seller';
                                $listingTitle = esc($listing['title'] ?? '');
                                $listingDesc = esc(strlen($listing['description'] ?? '') > 100 ? substr($listing['description'], 0, 100) . '...' : ($listing['description'] ?? ''));
                                $location = esc(($listing['barangay'] ?? '') . ', ' . ($listing['city'] ?? '') . ', ' . ($listing['province'] ?? ''));
                                $price = isset($listing['price']) ? '₱' . number_format($listing['price'], 0) : '';
                                $listingId = $listing['listing_id'];
                                $listingStatus = $listing['listing_status'] ?? 'pending';
                                $isVerified = $listing['is_verified_listing'] ?? 'pending';
                                
                                // Determine current status
                                if ($isVerified === 'verified' || $listingStatus === 'approved') {
                                    $statusClass = 'verified';
                                    $statusText = 'Verified';
                                } elseif ($listingStatus === 'rejected' || $isVerified === 'rejected') {
                                    $statusClass = 'rejected';
                                    $statusText = 'Rejected';
                                } else {
                                    $statusClass = 'pending';
                                    $statusText = 'Pending';
                                }
                            ?>
                            <div class="listing-card">
                                <div class="listing-card-image" style="position: relative;">
                                    🏞️
                                    <!-- Status Badge Top Right -->
                                    <div style="position: absolute; top: 12px; right: 12px;">
                                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                    </div>
                                </div>
                                <div class="listing-card-content">
                                    <div class="listing-card-title"><?php echo $listingTitle; ?></div>
                                    <div class="listing-card-seller">By: <?php echo esc($sellerName); ?></div>
                                    <div class="listing-card-description"><?php echo $listingDesc; ?></div>
                                    <div style="font-size: 13px; color: rgba(255,255,255,0.65); margin-bottom: 10px; display: flex; gap: 12px; flex-wrap: wrap;">
                                        <span>📍 <?php echo $location; ?></span>
                                        <span>💰 <?php echo $price; ?></span>
                                    </div>
                                    <div class="listing-card-actions">
                                        <button type="button" class="btn btn-neutral btn-sm" onclick="viewListingDetails('<?php echo $listingId; ?>', '<?php echo $listingTitle; ?>', '<?php echo esc($sellerName); ?>')">View</button>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="approveListing('<?php echo $listingId; ?>', '<?php echo $listingTitle; ?>')">Approve</button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="rejectListing('<?php echo $listingId; ?>', '<?php echo $listingTitle; ?>')">Reject</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 48px;">
                            <p class="table-empty-state">No listings found in the system.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </section>