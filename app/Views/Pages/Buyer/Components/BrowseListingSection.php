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
                    <div class="listing-card" onclick="openPropertyModal(1)" data-property-id="1">
                        <div class="listing-card-image">
                            <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400" alt="Land">
                            <span class="listing-card-badge listing-status available">Available</span>
                            <div class="listing-card-actions">
                                <button class="listing-card-action" title="Save Property" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                </button>
                                <button class="listing-card-action" title="Contact Seller" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
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
                                <div class="listing-card-seller">
                                    <span class="seller-avatar">JS</span>
                                    John Seller
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="listing-card" onclick="openPropertyModal(2)" data-property-id="2">
                        <div class="listing-card-image">
                            <img src="https://images.unsplash.com/photo-1628624747186-a941c476b7ef?w=400" alt="Land">
                            <span class="listing-card-badge listing-status available">Available</span>
                            <div class="listing-card-actions">
                                <button class="listing-card-action saved" title="Saved" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                </button>
                                <button class="listing-card-action" title="Contact Seller" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
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
                                <div class="listing-card-seller">
                                    <span class="seller-avatar">MS</span>
                                    Maria Seller
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="listing-card" onclick="openPropertyModal(3)" data-property-id="3">
                        <div class="listing-card-image">
                            <img src="https://images.unsplash.com/photo-1500076656116-558758c991c1?w=400" alt="Land">
                            <span class="listing-card-badge listing-status available">Available</span>
                            <div class="listing-card-actions">
                                <button class="listing-card-action" title="Save Property" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                </button>
                                <button class="listing-card-action" title="Contact Seller" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
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
                                <div class="listing-card-seller">
                                    <span class="seller-avatar">PS</span>
                                    Pedro Seller
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="listing-card" onclick="openPropertyModal(4)" data-property-id="4">
                        <div class="listing-card-image">
                            <img src="https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=400" alt="Land">
                            <span class="listing-card-badge listing-status available">Available</span>
                            <div class="listing-card-actions">
                                <button class="listing-card-action" title="Save Property" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                </button>
                                <button class="listing-card-action" title="Contact Seller" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="listing-card-content">
                            <h4 class="listing-card-title">Farm Land in Quezon</h4>
                            <div class="listing-card-location">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                Lucena City, Quezon
                            </div>
                            <div class="listing-card-details">
                                <div class="listing-card-detail"><strong>10,000</strong> sqm</div>
                                <div class="listing-card-detail"><strong>Agricultural</strong></div>
                                <div class="listing-card-detail"><strong>Tax Dec</strong></div>
                            </div>
                            <div class="listing-card-footer">
                                <span class="listing-card-price">₱6,500,000</span>
                                <div class="listing-card-seller">
                                    <span class="seller-avatar">AS</span>
                                    Ana Seller
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="listing-card" onclick="openPropertyModal(5)" data-property-id="5">
                        <div class="listing-card-image">
                            <img src="https://images.unsplash.com/photo-1518173946687-a4c036bc1bf3?w=400" alt="Land">
                            <span class="listing-card-badge listing-status available">Available</span>
                            <div class="listing-card-actions">
                                <button class="listing-card-action saved" title="Saved" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                </button>
                                <button class="listing-card-action" title="Contact Seller" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="listing-card-content">
                            <h4 class="listing-card-title">Beach Lot in Batangas</h4>
                            <div class="listing-card-location">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                Nasugbu, Batangas
                            </div>
                            <div class="listing-card-details">
                                <div class="listing-card-detail"><strong>2,500</strong> sqm</div>
                                <div class="listing-card-detail"><strong>Commercial</strong></div>
                                <div class="listing-card-detail"><strong>Clean</strong> Title</div>
                            </div>
                            <div class="listing-card-footer">
                                <span class="listing-card-price">₱15,000,000</span>
                                <div class="listing-card-seller">
                                    <span class="seller-avatar">RS</span>
                                    Rico Seller
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="listing-card" onclick="openPropertyModal(6)" data-property-id="6">
                        <div class="listing-card-image">
                            <img src="https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=400" alt="Land">
                            <span class="listing-card-badge listing-status available">Available</span>
                            <div class="listing-card-actions">
                                <button class="listing-card-action" title="Save Property" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                </button>
                                <button class="listing-card-action" title="Contact Seller" onclick="event.stopPropagation()">
                                    <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="listing-card-content">
                            <h4 class="listing-card-title">Mountain View Lot in Rizal</h4>
                            <div class="listing-card-location">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                Tanay, Rizal
                            </div>
                            <div class="listing-card-details">
                                <div class="listing-card-detail"><strong>3,000</strong> sqm</div>
                                <div class="listing-card-detail"><strong>Residential</strong></div>
                                <div class="listing-card-detail"><strong>Clean</strong> Title</div>
                            </div>
                            <div class="listing-card-footer">
                                <span class="listing-card-price">₱5,400,000</span>
                                <div class="listing-card-seller">
                                    <span class="seller-avatar">CS</span>
                                    Carlo Seller
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>