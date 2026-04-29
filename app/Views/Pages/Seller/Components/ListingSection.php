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
                            data-listing-id="<?= (int) ($listing['listing_id'] ?? 0) ?>"
                            data-listing-status="<?= esc($listing['status_key']) ?>"
                            data-verification-status="<?= esc($listing['verification_class'] ?? 'pending') ?>"
                        >
                            <div class="listing-card-image">
                                <img src="<?= esc($listing['image_url']) ?>" alt="<?= esc($listing['title']) ?>" onerror="this.onerror=null;this.src='<?= base_url('default1.png') ?>';">
                                <div class="listing-card-badges">
                                    <span class="listing-card-badge listing-status <?= esc($listing['status_class']) ?>"><?= esc($listing['status_label']) ?></span>
                                    <span class="listing-card-badge verification-status <?= esc($listing['verification_class'] ?? 'pending') ?>"><?= esc($listing['verification_label']) ?></span>
                                </div>
                                <div class="listing-card-actions">
                                    <button type="button" class="listing-card-action listing-edit-btn" aria-label="Edit <?= esc($listing['title']) ?>">
                                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button type="button" class="listing-card-action listing-delete-btn" aria-label="Delete <?= esc($listing['title']) ?>">
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

                <div class="seller-listing-modal" id="seller-edit-listing-modal" aria-hidden="true">
                    <div class="seller-listing-modal-backdrop" data-modal-close="edit"></div>
                    <div class="seller-listing-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="seller-edit-listing-title">
                        <div class="seller-listing-modal-header">
                            <h3 id="seller-edit-listing-title">Edit Land Listing</h3>
                            <button type="button" class="seller-listing-modal-close" data-modal-close="edit" aria-label="Close edit modal">&times;</button>
                        </div>
                        <form id="seller-edit-listing-form" class="form-grid">
                            <input type="hidden" name="listing_id" id="edit-listing-id">
                            <input type="hidden" name="latitude" id="edit-latitude">
                            <input type="hidden" name="longitude" id="edit-longitude">

                            <div class="form-group full-width">
                                <label>Property Title <span>*</span></label>
                                <input type="text" name="title" id="edit-title" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Property Type <span>*</span></label>
                                <select name="property_type" id="edit-property-type" class="form-control" required>
                                    <option value="">Select type</option>
                                    <option value="agricultural_land">Agricultural</option>
                                    <option value="residential_land">Residential</option>
                                    <option value="commercial_land">Commercial</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Price (₱) <span>*</span></label>
                                <input type="number" name="price" id="edit-price" class="form-control" min="0" step="0.01" required>
                            </div>

                            <div class="form-group">
                                <label>Province <span>*</span></label>
                                <select name="province" id="edit-province" class="form-control" required>
                                    <option value="">Select province</option>
                                    <option value="Batangas">Batangas</option>
                                    <option value="Cavite">Cavite</option>
                                    <option value="Laguna">Laguna</option>
                                    <option value="Rizal">Rizal</option>
                                    <option value="Quezon">Quezon</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>City/Municipality <span>*</span></label>
                                <input type="text" name="city" id="edit-city" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Barangay</label>
                                <input type="text" name="barangay" id="edit-barangay" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Title Status <span>*</span></label>
                                <select name="title_status" id="edit-title-status" class="form-control" required>
                                    <option value="">Select status</option>
                                    <option value="clean">Clean Title</option>
                                    <option value="tax-declaration">Tax Declaration</option>
                                    <option value="untitled">Untitled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Road Access <span>*</span></label>
                                <select name="road_access_type" id="edit-road-access" class="form-control" required>
                                    <option value="">Select access</option>
                                    <option value="cemented">Cemented Road</option>
                                    <option value="right_of_way">Right of Way</option>
                                    <option value="none">No Road Access</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>View Type</label>
                                <select name="view_type" id="edit-view-type" class="form-control">
                                    <option value="none">No Specific View</option>
                                    <option value="mountain_view">Mountain View</option>
                                    <option value="sea_view">Sea View</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Area Status</label>
                                <select name="developing_area" id="edit-developing-area" class="form-control">
                                    <option value="0">Not Developing Area</option>
                                    <option value="1">Developing Area</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Investment Ready</label>
                                <select name="investment_ready" id="edit-investment-ready" class="form-control">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label>Description <span>*</span></label>
                                <textarea name="description" id="edit-description" class="form-control" required></textarea>
                            </div>

                            <div class="form-group full-width">
                                <div id="seller-edit-status" class="seller-listing-modal-status" role="status" aria-live="polite" hidden></div>
                            </div>

                            <div class="form-group full-width form-actions">
                                <button type="button" class="btn-secondary" data-modal-close="edit">Cancel</button>
                                <button type="submit" class="btn-primary" id="seller-edit-submit-btn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="seller-listing-modal" id="seller-delete-listing-modal" aria-hidden="true">
                    <div class="seller-listing-modal-backdrop" data-modal-close="delete"></div>
                    <div class="seller-listing-modal-dialog seller-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="seller-delete-listing-title">
                        <div class="seller-listing-modal-header">
                            <h3 id="seller-delete-listing-title">Delete Listing</h3>
                            <button type="button" class="seller-listing-modal-close" data-modal-close="delete" aria-label="Close delete modal">&times;</button>
                        </div>
                        <div class="seller-delete-warning">
                            <p><strong>Warning:</strong> Do you want to delete this listing?</p>
                            <p>This action cannot be undone.</p>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn-secondary" data-modal-close="delete">Cancel</button>
                            <button type="button" class="btn-primary seller-delete-confirm-btn" id="seller-delete-confirm-btn">Delete</button>
                        </div>
                    </div>
                </div>
            </section>
