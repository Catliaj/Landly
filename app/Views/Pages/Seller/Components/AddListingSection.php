<section id="section-add-listing" class="content-section">
                <div class="content-card">
                    <div class="card-header">
                        <h3>Property Information</h3>
                    </div>
                    <div class="card-body">
                        <form id="add-listing-form" class="form-grid" method="post" action="<?= base_url('seller/listings') ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>

                            <input type="hidden" name="barangay" id="listing-barangay" value="">
                            <input type="hidden" name="is_titled" id="is-titled" value="0">
                            <input type="hidden" name="has_tax_declaration" id="has-tax-declaration" value="0">
                            <input type="hidden" name="has_lra_approved_plan" value="0">
                            <input type="hidden" name="mother_titled_disclosed" value="0">
                            <input type="hidden" name="document_status" id="document-status" value="pending">
                            <input type="hidden" name="listing_status" value="available">
                            <input type="hidden" name="is_verified_listing" value="0">

                            <div class="form-group full-width">
                                <label>Property Title <span>*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="e.g., Agricultural Land in Batangas" required>
                            </div>

                            <div class="form-group">
                                <label>Property Type <span>*</span></label>
                                <select name="property_type" class="form-control" required>
                                    <option value="">Select type</option>
                                    <option value="agricultural_land">Agricultural</option>
                                    <option value="residential_land">Residential</option>
                                    <option value="commercial_land">Commercial</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Land Area (sqm)</label>
                                <input type="number" step="0.01" min="0" id="land-area" class="form-control" placeholder="e.g., 5000">
                            </div>

                            <div class="form-group">
                                <label>Price (₱) <span>*</span></label>
                                <input type="number" name="price" step="0.01" min="0" id="listing-price" class="form-control" placeholder="e.g., 4500000" required>
                            </div>

                            <div class="form-group">
                                <label>Price per sqm (₱)</label>
                                <input type="text" id="price-per-sqm" class="form-control" placeholder="Auto-calculated" readonly>
                            </div>

                            <div class="form-group">
                                <label>Province <span>*</span></label>
                                <select name="province" class="form-control" required>
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
                                <input type="text" name="city" id="listing-city" class="form-control" placeholder="e.g., Lipa City" required>
                            </div>

                            <div class="form-group full-width">
                                <label>Complete Address</label>
                                <input type="text" id="complete-address" class="form-control" placeholder="e.g., Brgy. San Jose, Lipa City, Batangas">
                            </div>

                            <div class="form-group full-width">
                                <label>Description <span>*</span></label>
                                <textarea name="description" class="form-control" placeholder="Describe your property in detail..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Title Status <span>*</span></label>
                                <select id="title-status" class="form-control" required>
                                    <option value="">Select status</option>
                                    <option value="clean">Clean Title</option>
                                    <option value="tax-declaration">Tax Declaration</option>
                                    <option value="untitled">Untitled</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Road Access <span>*</span></label>
                                <select name="road_access_type" class="form-control" required>
                                    <option value="">Select access</option>
                                    <option value="cemented">Cemented Road</option>
                                    <option value="right_of_way">Right of Way</option>
                                    <option value="none">No Road Access</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>View Type</label>
                                <select name="view_type" class="form-control">
                                    <option value="none">No Specific View</option>
                                    <option value="mountain_view">Mountain View</option>
                                    <option value="sea_view">Sea View</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Area Status</label>
                                <select name="developing_area" class="form-control">
                                    <option value="0">Not Developing Area</option>
                                    <option value="1">Developing Area</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Investment Ready</label>
                                <select name="investment_ready" class="form-control">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label>Property Images <span>*</span></label>
                                <div class="image-upload">
                                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    <p>Choose property images</p>
                                    <p style="font-size: 0.8rem; margin-top: 8px; opacity: 0.6;">PNG, JPG up to 10MB (Max 10 images)</p>
                                    <input type="file" name="images[]" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" multiple required>
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label>Property Documents <span>*</span></label>
                                <input type="file" name="documents[]" class="form-control" accept=".pdf,.doc,.docx" multiple required>
                            </div>

                            <div class="form-group full-width" id="add-listing-feedback" style="display:none;"></div>

                            <div class="form-group full-width form-actions">
                                <button type="button" class="btn-secondary" id="save-draft-btn">Save as Draft</button>
                                <button type="submit" class="btn-primary" id="publish-listing-btn">
                                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Publish Listing
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <script>
                (function () {
                    const form = document.getElementById('add-listing-form');
                    if (!form) return;

                    const titleStatus = document.getElementById('title-status');
                    const isTitled = document.getElementById('is-titled');
                    const hasTaxDeclaration = document.getElementById('has-tax-declaration');
                    const documentStatus = document.getElementById('document-status');
                    const city = document.getElementById('listing-city');
                    const address = document.getElementById('complete-address');
                    const barangay = document.getElementById('listing-barangay');
                    const price = document.getElementById('listing-price');
                    const area = document.getElementById('land-area');
                    const pricePerSqm = document.getElementById('price-per-sqm');
                    const feedback = document.getElementById('add-listing-feedback');
                    const submitBtn = document.getElementById('publish-listing-btn');
                    const saveDraftBtn = document.getElementById('save-draft-btn');
                    const swalCdn = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';

                    const ensureSwal = () => {
                        if (window.Swal) return window.Promise.resolve(window.Swal);

                        const existingScript = document.querySelector('script[data-swal2="true"]');
                        if (existingScript) {
                            return new window.Promise((resolve) => {
                                existingScript.addEventListener('load', () => resolve(window.Swal), { once: true });
                                existingScript.addEventListener('error', () => resolve(null), { once: true });
                            });
                        }

                        return new window.Promise((resolve) => {
                            const script = document.createElement('script');
                            script.src = swalCdn;
                            script.async = true;
                            script.dataset.swal2 = 'true';
                            script.onload = () => resolve(window.Swal);
                            script.onerror = () => resolve(null);
                            document.head.appendChild(script);
                        });
                    };

                    const updatePricePerSqm = () => {
                        const priceVal = parseFloat(price?.value || '0');
                        const areaVal = parseFloat(area?.value || '0');
                        if (pricePerSqm) {
                            pricePerSqm.value = (priceVal > 0 && areaVal > 0)
                                ? (priceVal / areaVal).toFixed(2)
                                : '';
                        }
                    };

                    const syncStatusFields = () => {
                        const status = titleStatus?.value || '';

                        if (status === 'clean') {
                            isTitled.value = '1';
                            hasTaxDeclaration.value = '1';
                            documentStatus.value = 'complete';
                            return;
                        }

                        if (status === 'tax-declaration') {
                            isTitled.value = '0';
                            hasTaxDeclaration.value = '1';
                            documentStatus.value = 'partial';
                            return;
                        }

                        isTitled.value = '0';
                        hasTaxDeclaration.value = '0';
                        documentStatus.value = 'pending';
                    };

                    const syncBarangay = () => {
                        const addressValue = (address?.value || '').trim();
                        const cityValue = (city?.value || '').trim();
                        barangay.value = addressValue !== '' ? addressValue : cityValue;
                    };

                    const showFeedback = (message, isSuccess) => {
                        if (!feedback) return;
                        feedback.style.display = 'block';
                        feedback.style.padding = '12px';
                        feedback.style.borderRadius = '8px';
                        feedback.style.border = '1px solid rgba(255, 255, 255, 0.12)';
                        feedback.textContent = `${isSuccess ? 'Success: ' : 'Error: '}${message}`;
                    };

                    const notify = async ({ title, message, icon }) => {
                        const Swal = await ensureSwal();
                        if (Swal) {
                            await Swal.fire({
                                title,
                                text: message,
                                icon,
                                confirmButtonText: 'OK'
                            });
                            return;
                        }

                        showFeedback(message, icon === 'success');
                    };

                    price?.addEventListener('input', updatePricePerSqm);
                    area?.addEventListener('input', updatePricePerSqm);
                    titleStatus?.addEventListener('change', syncStatusFields);
                    city?.addEventListener('input', syncBarangay);
                    address?.addEventListener('input', syncBarangay);

                    saveDraftBtn?.addEventListener('click', () => {
                        const listingStatusInput = form.querySelector('input[name="listing_status"]');
                        if (listingStatusInput) {
                            listingStatusInput.value = 'in_inquiry';
                        }
                        notify({
                            title: 'Draft Enabled',
                            message: 'Click Publish Listing to save this draft to your listings.',
                            icon: 'info'
                        });
                    });

                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        syncStatusFields();
                        syncBarangay();

                        submitBtn.disabled = true;
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = 'Publishing...';

                        try {
                            const formData = new window.FormData(form);
                            const response = await window.fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const result = await response.json();

                            if (!response.ok || result.status !== 'success') {
                                throw new Error(result.message || 'Failed to create listing.');
                            }

                            await notify({
                                title: 'Success',
                                message: 'Listing published successfully.',
                                icon: 'success'
                            });
                            form.reset();
                            updatePricePerSqm();
                        } catch (error) {
                            await notify({
                                title: 'Error',
                                message: error.message || 'Failed to create listing.',
                                icon: 'error'
                            });
                        } finally {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    });
                })();
            </script>