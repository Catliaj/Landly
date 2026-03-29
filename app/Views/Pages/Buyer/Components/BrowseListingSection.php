<?php $browseListings = $browseListings ?? []; ?>

<section id="section-browse" class="content-section">
    <div class="browse-toolbar">
        <div class="toolbar-filters">
            <button class="filter-btn active">All</button>
            <button class="filter-btn">Agricultural</button>
            <button class="filter-btn">Residential</button>
            <button class="filter-btn">Commercial</button>
            <button class="filter-btn">Industrial</button>
        </div>
        <button class="btn-primary">
            <svg viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            Advanced Filter
        </button>
    </div>

    <div class="listings-grid">
        <?php if ($browseListings === []): ?>
            <div class="listing-card" style="grid-column: 1 / -1; cursor: default;">
                <div class="listing-card-content">
                    <h4 class="listing-card-title">No verified listings yet</h4>
                    <div class="listing-card-location">Check back later for newly approved properties.</div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($browseListings as $listing): ?>
                <div class="listing-card" onclick="openPropertyModal(<?= (int) ($listing['listing_id'] ?? 0) ?>)" data-property-id="<?= (int) ($listing['listing_id'] ?? 0) ?>">
                    <div class="listing-card-image">
                        <img src="<?= esc((string) ($listing['image_url'] ?? '')) ?>" alt="<?= esc((string) ($listing['title'] ?? 'Land Listing')) ?>">
                        <span class="listing-card-badge listing-status <?= esc((string) ($listing['status_class'] ?? 'available')) ?>"><?= esc((string) ($listing['status_label'] ?? 'Available')) ?></span>
                        <div class="listing-card-actions">
                            <button class="listing-card-action favorite-btn" data-listing-id="<?= (int) ($listing['listing_id'] ?? 0) ?>" title="Save Property" onclick="toggleFavorite(event, this, <?= (int) ($listing['listing_id'] ?? 0) ?>)">
                                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            </button>
                            <button class="listing-card-action" title="Contact Seller" onclick="createInquiryForListing(event, <?= (int) ($listing['listing_id'] ?? 0) ?>)">
                                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="listing-card-content">
                        <h4 class="listing-card-title"><?= esc((string) ($listing['title'] ?? 'Untitled Listing')) ?></h4>
                        <div class="listing-card-location">
                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?= esc((string) ($listing['location_label'] ?? 'Location unavailable')) ?>
                        </div>
                        <div class="listing-card-details">
                            <div class="listing-card-detail"><strong><?= esc((string) ($listing['status_label'] ?? 'Available')) ?></strong></div>
                            <div class="listing-card-detail"><strong><?= esc((string) ($listing['property_type_label'] ?? 'Unspecified')) ?></strong></div>
                            <div class="listing-card-detail"><strong><?= esc((string) ($listing['document_status_label'] ?? 'Documents Pending')) ?></strong></div>
                        </div>
                        <div class="listing-card-footer">
                            <span class="listing-card-price"><?= esc((string) ($listing['price_label'] ?? '₱0.00')) ?></span>
                            <div class="listing-card-seller">
                                <span class="seller-avatar"><?= esc((string) ($listing['seller_initials'] ?? 'NA')) ?></span>
                                <?= esc((string) ($listing['seller_name'] ?? 'Unknown Seller')) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>