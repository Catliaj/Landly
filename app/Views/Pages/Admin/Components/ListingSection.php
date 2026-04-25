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
                                $listingTitle = esc($listing['title'] ?? 'Untitled Listing');
                                $listingDesc = esc(strlen($listing['description'] ?? '') > 100 ? substr($listing['description'], 0, 100) . '...' : ($listing['description'] ?? ''));
                                $location = esc(($listing['barangay'] ?? '') . ', ' . ($listing['city'] ?? '') . ', ' . ($listing['province'] ?? ''));
                                $price = isset($listing['price']) ? '₱' . number_format($listing['price'], 0) : 'N/A';
                                $listingId = $listing['listing_id'];
                                $listingStatus = $listing['listing_status'] ?? 'pending';
                                $isVerified = $listing['is_verified_listing'] ?? 'pending';
                                
                                // Determine current status button class
                                $statusButton = '';
                                if ($isVerified === 'verified' || $listingStatus === 'approved') {
                                    $statusButton = 'btn-approved';
                                    $statusText = 'Approved';
                                } elseif ($listingStatus === 'rejected' || $isVerified === 'rejected') {
                                    $statusButton = 'btn-rejected';
                                    $statusText = 'Rejected';
                                } else {
                                    $statusButton = 'btn-incomplete';
                                    $statusText = 'Pending';
                                }
                            ?>
                            <div class="listing-card">
                                <div class="listing-card-image" style="background: linear-gradient(135deg, #2a6c62, #1f4f48); color: var(--cream-100); font-size: 2rem;">🏞️</div>
                                <div class="listing-card-content">
                                    <div class="listing-card-title"><?php echo $listingTitle; ?></div>
                                    <div class="listing-card-seller">By: <?php echo esc($sellerName); ?></div>
                                    <div class="listing-card-description"><?php echo $listingDesc; ?></div>
                                    <div style="font-size: 0.85rem; color: rgba(254,250,224,.65); margin-bottom: 8px;">
                                        📍 <?php echo $location; ?> | 💰 <?php echo $price; ?>
                                    </div>
                                    <div class="listing-card-actions">
                                        <button type="button" class="btn btn-sm btn-view" onclick="viewListingDetails('<?php echo $listingId; ?>', '<?php echo $listingTitle; ?>', '<?php echo esc($sellerName); ?>')">View Details</button>
                                        <button class="btn btn-sm <?php echo $statusButton; ?>" type="button" disabled><?php echo $statusText; ?></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="listing-card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <p style="color: rgba(254,250,224,.65);">No listings found in the system.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            </section>