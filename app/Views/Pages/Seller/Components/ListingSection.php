<section id="section-listings" class="content-section">
                <div class="listings-toolbar">
                    <div class="toolbar-filters">
                        <button class="filter-btn active">All (12)</button>
                        <button class="filter-btn">Active (8)</button>
                        <button class="filter-btn">Pending (2)</button>
                        <button class="filter-btn">Sold (2)</button>
                    </div>
                    <button class="btn-primary" onclick="showSection('add-listing')">
                        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add New Listing
                    </button>
                </div>

                <div class="listings-grid">
                    <div class="listing-card">
                        <div class="listing-card-image">
                            <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400" alt="Land">
                            <span class="listing-card-badge listing-status active">Active</span>
                            <div class="listing-card-actions">
                                <button class="listing-card-action">
                                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                                <button class="listing-card-action">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="listing-card-content">
                            <h4 class="listing-card-title">Agricultural Land in Batangas</h4>
                            <div class="listing-card-location">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                Lipa City, Batangas
                            </div>
                            <div class="listing-card-details">
                                <div class="listing-card-detail"><strong>5,000</strong> sqm</div>
                                <div class="listing-card-detail"><strong>Agricultural</strong></div>
                                <div class="listing-card-detail"><strong>Clean</strong> Title</div>
                            </div>
                            <div class="listing-card-footer">
                                <span class="listing-card-price">₱4,500,000</span>
                                <span class="listing-card-views">
                                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    245 views
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="listing-card">
                        <div class="listing-card-image">
                            <img src="https://images.unsplash.com/photo-1628624747186-a941c476b7ef?w=400" alt="Land">
                            <span class="listing-card-badge listing-status pending">Pending</span>
                            <div class="listing-card-actions">
                                <button class="listing-card-action">
                                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                                <button class="listing-card-action">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="listing-card-content">
                            <h4 class="listing-card-title">Commercial Lot in Tagaytay</h4>
                            <div class="listing-card-location">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                Tagaytay City, Cavite
                            </div>
                            <div class="listing-card-details">
                                <div class="listing-card-detail"><strong>1,200</strong> sqm</div>
                                <div class="listing-card-detail"><strong>Commercial</strong></div>
                                <div class="listing-card-detail"><strong>Clean</strong> Title</div>
                            </div>
                            <div class="listing-card-footer">
                                <span class="listing-card-price">₱8,200,000</span>
                                <span class="listing-card-views">
                                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    189 views
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="listing-card">
                        <div class="listing-card-image">
                            <img src="https://images.unsplash.com/photo-1500076656116-558758c991c1?w=400" alt="Land">
                            <span class="listing-card-badge listing-status sold">Sold</span>
                            <div class="listing-card-actions">
                                <button class="listing-card-action">
                                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </button>
                                <button class="listing-card-action">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="listing-card-content">
                            <h4 class="listing-card-title">Residential Lot in Laguna</h4>
                            <div class="listing-card-location">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                San Pablo City, Laguna
                            </div>
                            <div class="listing-card-details">
                                <div class="listing-card-detail"><strong>800</strong> sqm</div>
                                <div class="listing-card-detail"><strong>Residential</strong></div>
                                <div class="listing-card-detail"><strong>Clean</strong> Title</div>
                            </div>
                            <div class="listing-card-footer">
                                <span class="listing-card-price">₱2,800,000</span>
                                <span class="listing-card-views">
                                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    412 views
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>