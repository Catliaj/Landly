<?php
$browseListings = $browseListings ?? [];
$browseFilterOptions = $browseFilterOptions ?? [
    'locationLabel' => 'Nasugbu, Batangas',
    'barangays' => [],
    'price' => ['min' => 0, 'max' => 0],
    'size' => ['min' => 0, 'max' => 0],
];
?>

<style>
    .browse-results-bar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin: 18px 0 12px;
        flex-wrap: wrap;
    }

    .browse-results-count {
        font-size: 0.98rem;
        color: rgba(239, 231, 216, 0.82);
    }

    .browse-location-note {
        margin-top: 6px;
        font-size: 0.84rem;
        color: rgba(239, 231, 216, 0.58);
    }

    .browse-filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .browse-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid rgba(210, 180, 140, 0.25);
        background: rgba(11, 27, 16, 0.72);
        color: var(--cream-100);
        font-size: 0.82rem;
    }

    .browse-filter-chip button {
        border: 0;
        background: transparent;
        color: inherit;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        font-size: 1rem;
    }

    .browse-filter-loading {
        display: none;
        align-items: center;
        justify-content: center;
        min-height: 160px;
        border-radius: 24px;
        margin-top: 14px;
        background: rgba(11, 27, 16, 0.72);
        border: 1px solid rgba(210, 180, 140, 0.12);
        color: rgba(239, 231, 216, 0.82);
    }

    .browse-filter-loading.is-visible {
        display: flex;
    }

    .browse-pagination {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        margin-top: 20px;
    }

    .browse-pagination button {
        border: 1px solid rgba(210, 180, 140, 0.2);
        background: rgba(11, 27, 16, 0.72);
        color: var(--cream-100);
        border-radius: 12px;
        padding: 10px 14px;
        cursor: pointer;
    }

    .browse-pagination button.active,
    .browse-pagination button:hover {
        background: var(--accent);
        color: var(--green-900);
    }

    .advanced-filter-overlay {
        position: fixed;
        inset: 0;
        z-index: 1200;
        background: rgba(0, 0, 0, 0.58);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
    }

    .advanced-filter-overlay.is-visible {
        display: flex;
    }

    .advanced-filter-panel {
        width: min(1100px, 100%);
        max-height: 92vh;
        overflow: auto;
        border-radius: 28px;
        background: linear-gradient(180deg, rgba(15, 33, 20, 0.98), rgba(9, 22, 14, 0.98));
        border: 1px solid rgba(210, 180, 140, 0.16);
        box-shadow: 0 30px 90px rgba(0, 0, 0, 0.38);
        padding: 22px;
    }

    .advanced-filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }

    .advanced-filter-title {
        margin: 0;
        font-size: 1.4rem;
        color: var(--cream-100);
    }

    .advanced-filter-close {
        border: 0;
        background: transparent;
        color: var(--cream-100);
        font-size: 1.8rem;
        cursor: pointer;
        line-height: 1;
    }

    .advanced-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .advanced-filter-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .advanced-filter-group label {
        font-size: 0.88rem;
        color: rgba(239, 231, 216, 0.88);
    }

    .advanced-filter-group input,
    .advanced-filter-group select {
        width: 100%;
        border: 1px solid rgba(210, 180, 140, 0.2);
        border-radius: 14px;
        background: rgba(11, 27, 16, 0.92);
        color: var(--cream-100);
        padding: 12px 14px;
        font: inherit;
    }

    .advanced-filter-group input::placeholder {
        color: rgba(239, 231, 216, 0.45);
    }

    .advanced-filter-hint {
        font-size: 0.78rem;
        color: rgba(239, 231, 216, 0.58);
        margin-top: -2px;
    }

    .filter-pill-note {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(210, 180, 140, 0.12);
        color: var(--cream-100);
        font-size: 0.82rem;
    }

    .advanced-filter-preset-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .advanced-filter-preset {
        border: 1px solid rgba(210, 180, 140, 0.24);
        border-radius: 999px;
        background: rgba(11, 27, 16, 0.82);
        color: var(--cream-100);
        padding: 8px 12px;
        cursor: pointer;
    }

    .advanced-filter-toggle-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0 2px;
    }

    .advanced-filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .advanced-filter-actions .btn-secondary,
    .advanced-filter-actions .btn-primary {
        min-width: 150px;
    }
</style>

<section id="section-browse" class="content-section">
    <div class="browse-toolbar">
        <div class="toolbar-filters">
            <button type="button" class="filter-btn active" data-filter="all">All</button>
            <button type="button" class="filter-btn" data-filter="agricultural_land">Agricultural</button>
            <button type="button" class="filter-btn" data-filter="residential_land">Residential</button>
            <button type="button" class="filter-btn" data-filter="commercial_land">Commercial</button>
            <button type="button" class="filter-btn" data-filter="industrial_land">Industrial</button>
        </div>
        <button type="button" class="btn-primary" id="browse-advanced-filter-btn">
            <svg viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            Advanced Filter
        </button>
    </div>

    <div class="browse-results-bar">
        <div>
            <div class="browse-results-count" id="browse-results-count"><?= count($browseListings) ?> properties found</div>
            <div class="browse-location-note">Browsing listings in <?= esc((string) ($browseFilterOptions['locationLabel'] ?? 'Nasugbu, Batangas')) ?> only.</div>
            <div class="browse-filter-chips" id="browse-filter-chips"></div>
        </div>
    </div>

    <div class="browse-filter-loading" id="browse-filter-loading" hidden>Filtering listings...</div>

    <div class="listings-grid" id="browse-listings-grid">
        <?php if ($browseListings === []): ?>
            <div class="listing-card" style="grid-column: 1 / -1; cursor: default;">
                <div class="listing-card-content">
                    <h4 class="listing-card-title">No verified listings yet</h4>
                    <div class="listing-card-location">Check back later for newly approved properties.</div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($browseListings as $listing): ?>
                <div class="listing-card" onclick="openPropertyModal(<?= (int) ($listing['listing_id'] ?? 0) ?>)" data-property-id="<?= (int) ($listing['listing_id'] ?? 0) ?>" data-property-type="<?= esc((string) ($listing['property_type_key'] ?? '')) ?>">
                    <div class="listing-card-image">
                        <img class="img-fluid" src="<?= esc((string) ($listing['image_url'] ?? '')) ?>" alt="<?= esc((string) ($listing['title'] ?? 'Land Listing')) ?>">
                        <span class="listing-card-badge listing-status <?= esc((string) ($listing['status_class'] ?? 'available')) ?>"><?= esc((string) ($listing['status_label'] ?? 'Available')) ?></span>
                        <div class="listing-card-actions">
                            <button class="listing-card-action favorite-btn<?= !empty($listing['is_saved']) ? ' saved' : '' ?>" data-listing-id="<?= (int) ($listing['listing_id'] ?? 0) ?>" title="<?= !empty($listing['is_saved']) ? 'Remove from Saved' : 'Save Property' ?>" aria-pressed="<?= !empty($listing['is_saved']) ? 'true' : 'false' ?>" onclick="toggleFavorite(event, this, <?= (int) ($listing['listing_id'] ?? 0) ?>)">
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

    <div class="browse-pagination" id="browse-pagination" hidden></div>

    <div class="advanced-filter-overlay" id="browse-advanced-filter-overlay" aria-hidden="true">
        <div class="advanced-filter-panel" role="dialog" aria-modal="true" aria-labelledby="browseAdvancedFilterTitle">
            <div class="advanced-filter-header">
                <h3 class="advanced-filter-title" id="browseAdvancedFilterTitle">Advanced Filter</h3>
                <button type="button" class="advanced-filter-close" id="browse-advanced-filter-close" aria-label="Close advanced filter">&times;</button>
            </div>

            <form id="browse-advanced-filter-form">
                <div class="advanced-filter-grid">
                    <div class="advanced-filter-group">
                        <label for="browse-filter-barangay">Barangay</label>
                        <select id="browse-filter-barangay" name="barangay">
                            <option value="">All Barangays</option>
                            <?php foreach (($browseFilterOptions['barangays'] ?? []) as $barangay): ?>
                                <option value="<?= esc((string) $barangay) ?>"><?= esc((string) $barangay) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="advanced-filter-hint">Nasugbu, Batangas only.</div>
                    </div>



                    <div class="advanced-filter-group">
                        <label for="browse-filter-property-type">Property Type</label>
                        <select id="browse-filter-property-type" name="property_type">
                            <option value="">All Types</option>
                            <option value="residential_land">Residential</option>
                            <option value="agricultural_land">Agricultural / Farm</option>
                            <option value="commercial_land">Commercial</option>
                            <option value="beach_lot">Beach Lot</option>
                        </select>
                    </div>
                    <div class="advanced-filter-group">
                        <label for="browse-filter-road-access">Road Access</label>
                        <select id="browse-filter-road-access" name="road_access">
                            <option value="">Any</option>
                            <option value="cemented">Concrete</option>
                            <option value="right_of_way">Dirt Road</option>
                            <option value="none">Highway Access</option>
                        </select>
                    </div>
                    <div class="advanced-filter-group">
                        <label for="browse-filter-view-type">View Type</label>
                        <select id="browse-filter-view-type" name="view_type">
                            <option value="">Any</option>
                            <option value="sea_view">Beach View</option>
                            <option value="mountain_view">Mountain View</option>
                            <option value="none">Plain</option>
                        </select>
                    </div>

                    <div class="advanced-filter-group">
                        <label>Price Range</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="browse-filter-min-price" name="min_price" placeholder="Min (e.g., 1M)" style="flex: 1;">
                            <span style="color: rgba(239, 231, 216, 0.6);">to</span>
                            <input type="text" id="browse-filter-max-price" name="max_price" placeholder="Max (e.g., 3M)" style="flex: 1;">
                        </div>
                    </div>

                    <div class="advanced-filter-group">
                        <label>Lot Size (sqm)</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="browse-filter-min-size" name="min_size" placeholder="Min" style="flex: 1;">
                            <span style="color: rgba(239, 231, 216, 0.6);">to</span>
                            <input type="text" id="browse-filter-max-size" name="max_size" placeholder="Max" style="flex: 1;">
                        </div>
                    </div>

                    <div class="advanced-filter-group">
                        <label for="browse-filter-sort">Sort By</label>
                        <select id="browse-filter-sort" name="sort">
                            <option value="newest">Newest Listings</option>
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                            <option value="largest_lot">Largest Lot Size</option>
                        </select>
                    </div>
                </div>

                <div class="advanced-filter-actions">
                    <button type="button" class="btn-secondary" id="browse-filter-clear">Clear All Filters</button>
                    <button type="submit" class="btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</section>