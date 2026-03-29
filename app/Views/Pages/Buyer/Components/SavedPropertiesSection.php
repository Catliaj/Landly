<section id="section-saved" class="content-section">
                <div class="browse-toolbar">
                    <div class="toolbar-filters">
                        <button class="filter-btn active">All Saved (<span id="favorites-count">0</span>)</button>
                        <button class="filter-btn">Agricultural</button>
                        <button class="filter-btn">Commercial</button>
                        <button class="filter-btn">Residential</button>
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
            <div class="listing-card" onclick="openPropertyModal(${favorite.listing_id})" data-property-id="${favorite.listing_id}">
                <div class="listing-card-image">
                    <img src="https://images.unsplash.com/photo-1628624747186-a941c476b7ef?w=400" alt="${escapeHtml(favorite.title)}">
                    <span class="listing-card-badge listing-status available">Available</span>
                    <div class="listing-card-actions">
                        <button class="listing-card-action saved favorite-btn" data-listing-id="${favorite.listing_id}" title="Remove from Saved" onclick="toggleFavorite(event, this, ${favorite.listing_id})">
                            <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        </button>
                        <button class="listing-card-action" title="Contact Seller" onclick="event.stopPropagation()">
                            <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="listing-card-content">
                    <h4 class="listing-card-title">${escapeHtml(favorite.title)}</h4>
                    <div class="listing-card-location">
                        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        Location
                    </div>
                    <div class="listing-card-details">
                        <div class="listing-card-detail"><strong>${favorite.property_type || 'Unspecified'}</strong></div>
                        <div class="listing-card-detail"><strong>Available</strong></div>
                        <div class="listing-card-detail"><strong>Clean Title</strong></div>
                    </div>
                    <div class="listing-card-footer">
                        <span class="listing-card-price">₱${favorite.price ? parseInt(favorite.price).toLocaleString() : '0'}</span>
                        <div class="listing-card-seller">
                            <span class="seller-avatar">NA</span>
                            Seller
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
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
</script>