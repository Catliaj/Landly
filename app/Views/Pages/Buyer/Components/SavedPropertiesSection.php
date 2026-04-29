<section id="section-saved" class="content-section">
                <div class="browse-toolbar">
                    <div class="toolbar-filters">
                        <button type="button" class="filter-btn active" data-filter="all">All Saved (<span id="favorites-count">0</span>)</button>
                        <button type="button" class="filter-btn" data-filter="agricultural_land">Agricultural</button>
                        <button type="button" class="filter-btn" data-filter="commercial_land">Commercial</button>
                        <button type="button" class="filter-btn" data-filter="residential_land">Residential</button>
                    </div>
                </div>

                <div class="listings-grid" id="saved-listings-container">
                    <div class="listing-card" style="grid-column: 1 / -1; cursor: default;">
                        <div class="listing-card-content">
                            <h4 class="listing-card-title">Loading saved properties...</h4>
                        </div>
                    </div>
                </div>
            </section>

<script>
    // Load saved properties when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadSavedProperties();
        
        // Reload saved properties every 30 seconds to keep in sync
        setInterval(loadSavedProperties, 30000);
    });

    function loadSavedProperties() {
        fetch('<?= base_url('buyer/favorites/get-all') ?>', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.favorites) {
                renderSavedListings(data.favorites);
                document.getElementById('favorites-count').textContent = data.count;
            }
        })
        .catch(error => console.error('Error loading favorites:', error));
    }

    function renderSavedListings(favorites) {
        const container = document.getElementById('saved-listings-container');
        
        if (favorites.length === 0) {
            container.innerHTML = `
                <div class="listing-card" style="grid-column: 1 / -1; cursor: default;">
                    <div class="listing-card-content">
                        <h4 class="listing-card-title">No saved properties yet</h4>
                        <div class="listing-card-location">Browse listings and click the heart icon to save your favorite properties.</div>
                    </div>
                </div>
            `;
            return;
        }

        container.innerHTML = favorites.map(favorite => `
            <div class="listing-card" onclick="openPropertyModal(${favorite.listing_id})" data-property-id="${favorite.listing_id}" data-property-type="${normalizePropertyTypeKey(favorite.property_type)}">
                <div class="listing-card-image">
                    <img class="img-fluid" src="${escapeHtml(favorite.image_url || '<?= base_url('default1.png') ?>')}" alt="${escapeHtml(favorite.title)}" onerror="this.onerror=null;this.src='<?= base_url('default1.png') ?>';">
                    <span class="listing-card-badge listing-status ${escapeHtml(favorite.listing_status || 'available')}">${escapeHtml(favorite.listing_status_label || 'Available')}</span>
                    <div class="listing-card-actions">
                        <button class="listing-card-action saved favorite-btn" data-listing-id="${favorite.listing_id}" title="Remove from Saved" onclick="toggleFavorite(event, this, ${favorite.listing_id})">
                            <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        </button>
                        <button class="listing-card-action" title="Contact Seller" onclick="createInquiryForListing(event, ${favorite.listing_id})">
                            <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="listing-card-content">
                    <h4 class="listing-card-title">${escapeHtml(favorite.title)}</h4>
                    <div class="listing-card-location">
                        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        ${escapeHtml(favorite.location_label || 'Location unavailable')}
                    </div>
                    <div class="listing-card-details">
                        <div class="listing-card-detail"><strong>${escapeHtml(favorite.property_type_label || formatPropertyTypeLabel(favorite.property_type))}</strong></div>
                        <div class="listing-card-detail"><strong>${escapeHtml(favorite.listing_status_label || 'Available')}</strong></div>
                        <div class="listing-card-detail"><strong>${escapeHtml(favorite.document_status_label || 'Documents Pending')}</strong></div>
                    </div>
                    <div class="listing-card-footer">
                        <span class="listing-card-price">${escapeHtml(favorite.price_label || '₱0.00')}</span>
                        <div class="listing-card-seller">
                            <span class="seller-avatar">${escapeHtml(favorite.seller_initials || 'NA')}</span>
                            ${escapeHtml(favorite.seller_name || 'Unknown Seller')}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        // Re-attach event listeners for the newly rendered buttons
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        applyListingFilters();
    }

    function normalizePropertyTypeKey(propertyType) {
        const raw = String(propertyType || '').trim().toLowerCase();
        if (raw === '') {
            return '';
        }

        if (raw === 'residential' || raw === 'residential_land') {
            return 'residential_land';
        }

        if (raw === 'commercial' || raw === 'commercial_land') {
            return 'commercial_land';
        }

        if (raw === 'agricultural' || raw === 'agricultural_land') {
            return 'agricultural_land';
        }

        return raw;
    }

    function formatPropertyTypeLabel(propertyType) {
        const normalized = normalizePropertyTypeKey(propertyType);

        switch (normalized) {
            case 'residential_land':
                return 'Residential';
            case 'commercial_land':
                return 'Commercial';
            case 'agricultural_land':
                return 'Agricultural';
            default:
                return propertyType || 'Unspecified';
        }
    }

    function escapeHtml(text) {
        const value = String(text ?? '');
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return value.replace(/[&<>"']/g, m => map[m]);
    }
</script>
