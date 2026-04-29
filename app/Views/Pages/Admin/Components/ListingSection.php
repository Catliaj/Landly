<section id="listings-section" class="section-content">
                <div class="content-card">
                <h3>Land Listings Management</h3>
                
                <!-- Status Filter Tabs -->
                <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid rgba(149,213,178,.2); padding-bottom: 12px;">
                    <button class="listing-filter-btn active" data-filter="all" onclick="filterListingsByStatus('all')" style="padding: 6px 14px; border: none; background: rgba(149,213,178,.2); color: var(--accent); font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">All Listings</button>
                    <button class="listing-filter-btn" data-filter="pending" onclick="filterListingsByStatus('pending')" style="padding: 6px 14px; border: none; background: transparent; color: rgba(254,250,224,.6); font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">⏳ Pending</button>
                    <button class="listing-filter-btn" data-filter="verified" onclick="filterListingsByStatus('verified')" style="padding: 6px 14px; border: none; background: transparent; color: rgba(254,250,224,.6); font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">✅ Approved</button>
                    <button class="listing-filter-btn" data-filter="rejected" onclick="filterListingsByStatus('rejected')" style="padding: 6px 14px; border: none; background: transparent; color: rgba(254,250,224,.6); font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">❌ Rejected</button>
                </div>
                
                <div class="listings-grid" id="listingsGrid">
                    <?php if (!empty($listings) && is_array($listings)): ?>
                        <?php 
                            // Create a map of user IDs to names for quick lookup
                            $sellerMap = [];
                            if (!empty($users) && is_array($users)) {
                                foreach ($users as $user) {
                                    $sellerMap[$user['user_id']] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['fullname'] ?? 'Unknown Seller');
                                }
                            }
                        ?>
                        <?php foreach ($listings as $listing): ?>
                            <?php
                                $sellerId = $listing['seller_id'] ?? null;
                                $sellerName = $listing['seller_name'] ?? ($sellerMap[$sellerId] ?? 'Unknown Seller');
                                $listingTitle = esc($listing['title'] ?? '');
                                $listingDesc = esc(strlen($listing['description'] ?? '') > 100 ? substr($listing['description'], 0, 100) . '...' : ($listing['description'] ?? ''));
                                $location = esc(($listing['barangay'] ?? '') . ', ' . ($listing['city'] ?? '') . ', ' . ($listing['province'] ?? ''));
                                $price = isset($listing['price']) ? '₱' . number_format($listing['price'], 0) : '';
                                $listingId = $listing['listing_id'];
                                $listingStatus = $listing['listing_status'] ?? 'pending';
                                $isVerified = $listing['is_verified_listing'] ?? 'pending';
                                $listingImages = !empty($listing['images']) && is_array($listing['images']) ? $listing['images'] : [($listing['image_url'] ?? '')];
                                $listingImages = array_values(array_filter($listingImages, static function ($imageUrl) {
                                    return trim((string) $imageUrl) !== '';
                                }));
                                $listingImagesJson = htmlspecialchars(json_encode($listingImages), ENT_QUOTES, 'UTF-8');
                                $landArea = trim((string) ($listing['land_area'] ?? ''));
                                $landType = trim((string) ($listing['property_type_label'] ?? $listing['property_type'] ?? ''));
                                
                                // Determine current status (is_verified_listing: 'true'/'false'/'pending'/'rejected', listing_status: 'available'/'approved'/'rejected')
                                if ($isVerified === 'true' || $listingStatus === 'approved') {
                                    $statusClass = 'verified';
                                    $statusText = 'Approved';
                                } elseif ($listingStatus === 'rejected' || $isVerified === 'rejected') {
                                    $statusClass = 'rejected';
                                    $statusText = 'Rejected';
                                } else {
                                    $statusClass = 'pending';
                                    $statusText = 'Pending';
                                }
                            ?>
                            <div class="listing-card" data-status="<?= $statusClass ?>">
                                <div class="listing-card-image" style="position: relative;">
                                    <div class="listing-card-carousel" data-listing-carousel="<?= esc($listingId) ?>" data-images="<?= $listingImagesJson ?>" data-index="0" style="width: 100%; height: 100%; position: relative; overflow: hidden; background: #183127;">
                                        <?php if (!empty($listingImages)): ?>
                                            <img src="<?= esc($listingImages[0]) ?>" alt="<?= esc($listingTitle) ?>" data-carousel-image style="width: 100%; height: 100%; object-fit: cover; background: #183127;" onerror="this.onerror=null;this.src='<?= base_url('default1.png') ?>';">
                                        <?php else: ?>
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.45); font-size: 13px;">No image available</div>
                                        <?php endif; ?>
                                        <?php if (count($listingImages) > 1): ?>
                                            <button type="button" onclick="event.stopPropagation(); shiftListingCardImage(this, -1)" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); border: none; width: 28px; height: 28px; border-radius: 50%; background: rgba(0,0,0,0.45); color: #fff; cursor: pointer;">‹</button>
                                            <button type="button" onclick="event.stopPropagation(); shiftListingCardImage(this, 1)" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); border: none; width: 28px; height: 28px; border-radius: 50%; background: rgba(0,0,0,0.45); color: #fff; cursor: pointer;">›</button>
                                            <div style="position: absolute; left: 50%; bottom: 10px; transform: translateX(-50%); display: flex; gap: 5px; pointer-events: none;">
                                                <?php foreach ($listingImages as $imageIndex => $imageUrl): ?>
                                                    <span data-carousel-dot="<?= $imageIndex ?>" style="width: 6px; height: 6px; border-radius: 50%; background: <?= $imageIndex === 0 ? '#95d5b2' : 'rgba(255,255,255,0.35)' ?>;"></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
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
                                        <span>🏷️ <?php echo esc($landType !== '' ? $landType : 'N/A'); ?></span>
                                        <span>📐 <?php echo esc($landArea !== '' ? $landArea . ' sq.m' : 'N/A'); ?></span>
                                    </div>
                                    <div class="listing-card-actions">
                                        <button type="button" class="btn btn-neutral btn-sm" onclick="viewListingDetails('<?php echo $listingId; ?>', '<?php echo $listingTitle; ?>', '<?php echo esc($sellerName); ?>')">View</button>
                                        <?php if ($statusClass !== 'verified'): ?>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="approveListing('<?php echo $listingId; ?>', '<?php echo $listingTitle; ?>')">Approve</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="rejectListing('<?php echo $listingId; ?>', '<?php echo $listingTitle; ?>')">Reject</button>
                                        <?php endif; ?>
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
