<?php
$sellerListings = $sellerListings ?? [];
$listingCounts = $listingCounts ?? [
    'all' => count($sellerListings),
    'available' => 0,
    'in_inquiry' => 0,
    'reserved' => 0,
    'closed' => 0,
    'pending' => 0,
    'rejected' => 0,
];
?>

<section id="section-listings" class="content-section">
                <div class="listings-toolbar">
                    <div class="toolbar-filters">
                        <button type="button" class="filter-btn active" data-filter="all">All (<?= (int) ($listingCounts['all'] ?? 0) ?>)</button>
                        <button type="button" class="filter-btn" data-filter="available">Available (<?= (int) ($listingCounts['available'] ?? 0) ?>)</button>
                        <button type="button" class="filter-btn" data-filter="in_inquiry">In Inquiry (<?= (int) ($listingCounts['in_inquiry'] ?? 0) ?>)</button>
                        <button type="button" class="filter-btn" data-filter="reserved">Reserved (<?= (int) ($listingCounts['reserved'] ?? 0) ?>)</button>
                        <button type="button" class="filter-btn" data-filter="closed">Closed (<?= (int) ($listingCounts['closed'] ?? 0) ?>)</button>
                        <button type="button" class="filter-btn" data-filter="pending">Pending (<?= (int) ($listingCounts['pending'] ?? 0) ?>)</button>
                        <button type="button" class="filter-btn" data-filter="rejected">Rejected (<?= (int) ($listingCounts['rejected'] ?? 0) ?>)</button>
                    </div>
                </div>

                <div class="listings-grid" id="seller-listings-grid">
                    <?php foreach ($sellerListings as $listing): ?>
                        <div
                            class="listing-card"
                            data-listing-status="<?= esc($listing['status_key']) ?>"
                            data-verification-status="<?= esc($listing['verification_class'] ?? 'pending') ?>"
                        >
                            <div class="listing-card-image">
                                <img src="<?= esc($listing['image_url']) ?>" alt="<?= esc($listing['title']) ?>">
                                <div class="listing-card-badges">
                                    <span class="listing-card-badge listing-status <?= esc($listing['status_class']) ?>"><?= esc($listing['status_label']) ?></span>
                                    <span class="listing-card-badge verification-status <?= esc($listing['verification_class'] ?? 'pending') ?>"><?= esc($listing['verification_label']) ?></span>
                                </div>
                                <div class="listing-card-actions">
                                    <button type="button" class="listing-card-action" aria-label="Edit <?= esc($listing['title']) ?>">
                                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button type="button" class="listing-card-action" aria-label="Delete <?= esc($listing['title']) ?>">
                                        <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="listing-card-content">
                                <h4 class="listing-card-title"><?= esc($listing['title']) ?></h4>
                                <div class="listing-card-location">
                                    <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <?= esc($listing['location_label']) ?>
                                </div>
                                <div class="listing-card-details">
                                    <div class="listing-card-detail"><strong><?= esc($listing['property_type_label']) ?></strong></div>
                                    <div class="listing-card-detail"><strong><?= esc($listing['document_status_label']) ?></strong></div>
                                    <div class="listing-card-detail"><strong><?= esc($listing['verification_label']) ?></strong></div>
                                </div>
                                <div class="listing-card-verification-note <?= esc($listing['verification_class'] ?? 'pending') ?>">
                                    <strong>Seller visibility:</strong> <?= esc($listing['verification_note'] ?? 'Seller-only status') ?>
                                </div>
                                <div class="listing-card-footer">
                                    <span class="listing-card-price">₱<?= number_format((float) ($listing['price_value'] ?? 0), 0) ?></span>
                                    <span class="listing-card-views">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <?= number_format((int) ($listing['view_count'] ?? 0)) ?> views
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="listings-empty-state<?= $sellerListings === [] ? ' is-visible' : '' ?>" id="seller-listings-empty"<?= $sellerListings === [] ? '' : ' hidden' ?>>
                    <h3 id="seller-listings-empty-title"><?= $sellerListings === [] ? 'No listings yet' : 'No matching listings' ?></h3>
                        <p id="seller-listings-empty-description"><?= $sellerListings === [] ? 'Your property listings for this account will appear here once they are created.' : 'Try a different status filter.' ?></p>
                </div>
            </section>