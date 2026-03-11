<section id="section-add-listing" class="content-section">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Property Information</h3>
                    </div>
                    <div class="card-body">
                        <form class="form-grid">
                            <div class="form-group full-width">
                                <label>Property Title <span>*</span></label>
                                <input type="text" class="form-control" placeholder="e.g., Agricultural Land in Batangas">
                            </div>

                            <div class="form-group">
                                <label>Property Type <span>*</span></label>
                                <select class="form-control">
                                    <option value="">Select type</option>
                                    <option value="agricultural">Agricultural</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="industrial">Industrial</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Land Area (sqm) <span>*</span></label>
                                <input type="number" class="form-control" placeholder="e.g., 5000">
                            </div>

                            <div class="form-group">
                                <label>Price (₱) <span>*</span></label>
                                <input type="number" class="form-control" placeholder="e.g., 4500000">
                            </div>

                            <div class="form-group">
                                <label>Price per sqm (₱)</label>
                                <input type="number" class="form-control" placeholder="Auto-calculated">
                            </div>

                            <div class="form-group">
                                <label>Province <span>*</span></label>
                                <select class="form-control">
                                    <option value="">Select province</option>
                                    <option value="batangas">Batangas</option>
                                    <option value="cavite">Cavite</option>
                                    <option value="laguna">Laguna</option>
                                    <option value="rizal">Rizal</option>
                                    <option value="quezon">Quezon</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>City/Municipality <span>*</span></label>
                                <input type="text" class="form-control" placeholder="e.g., Lipa City">
                            </div>

                            <div class="form-group full-width">
                                <label>Complete Address</label>
                                <input type="text" class="form-control" placeholder="e.g., Brgy. San Jose, Lipa City, Batangas">
                            </div>

                            <div class="form-group full-width">
                                <label>Description <span>*</span></label>
                                <textarea class="form-control" placeholder="Describe your property in detail..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Title Status <span>*</span></label>
                                <select class="form-control">
                                    <option value="">Select status</option>
                                    <option value="clean">Clean Title</option>
                                    <option value="tax-declaration">Tax Declaration</option>
                                    <option value="untitled">Untitled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Road Access</label>
                                <select class="form-control">
                                    <option value="">Select access</option>
                                    <option value="concrete">Concrete Road</option>
                                    <option value="asphalt">Asphalt Road</option>
                                    <option value="gravel">Gravel Road</option>
                                    <option value="dirt">Dirt Road</option>
                                    <option value="none">No Road Access</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label>Property Images <span>*</span></label>
                                <div class="image-upload">
                                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    <p>Drag and drop images here, or <span>browse</span></p>
                                    <p style="font-size: 0.8rem; margin-top: 8px; opacity: 0.6;">PNG, JPG up to 10MB (Max 10 images)</p>
                                </div>
                            </div>

                            <div class="form-group full-width form-actions">
                                <button type="button" class="btn-secondary">Save as Draft</button>
                                <button type="submit" class="btn-primary">
                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Publish Listing
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>