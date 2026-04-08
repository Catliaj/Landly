<?php $geoapifyApiKey = trim((string) ($geoapifyApiKey ?? '')); ?>

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
                                <div id="address-suggestions" class="address-suggestions" style="display:none;"></div>
                                <small class="address-hint">Only addresses within Nasugbu, Batangas are allowed.</small>
                            </div>


                           

                            <div class="  form-group full-width" style="display:none;">
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">
                            </div>

                            <div class="form-group full-width">
                                <label>Pin Location on Map</label>
                                <div id="map" style="width: 100%; height: 300px; border: 1px solid #ccc; border-radius: 8px;"></div>
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

            <style>
                .swal2-popup.landly-swal {
                    background: var(--green-900);
                    color: var(--cream-100);
                    border: 1px solid rgba(149, 213, 178, 0.35);
                    border-radius: 18px;
                    box-shadow: 0 20px 45px rgba(5, 18, 18, 0.45);
                }

                .swal2-popup.landly-swal .swal2-title,
                .swal2-popup.landly-swal .swal2-html-container {
                    color: var(--cream-100);
                }

                .swal2-popup.landly-swal .swal2-confirm {
                    background: linear-gradient(135deg, var(--green-700) 0%, var(--green-800) 100%);
                    color: var(--cream-100);
                    border-radius: 10px;
                    box-shadow: 0 8px 20px rgba(15, 27, 27, 0.25);
                }

                .swal2-popup.landly-swal .swal2-confirm:focus {
                    box-shadow: 0 0 0 3px rgba(149, 213, 178, 0.35);
                }

                @keyframes landlyFadeInUp {
                    from { opacity: 0; transform: translateY(16px) scale(0.98); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }

                @keyframes landlyFadeOutDown {
                    from { opacity: 1; transform: translateY(0) scale(1); }
                    to { opacity: 0; transform: translateY(12px) scale(0.98); }
                }

                .landly-swal-show {
                    animation: landlyFadeInUp 0.28s ease-out;
                }

                .landly-swal-hide {
                    animation: landlyFadeOutDown 0.2s ease-in;
                }

                .address-hint {
                    display: block;
                    margin-top: 8px;
                    color: rgba(245, 245, 220, 0.72);
                    font-size: 0.8rem;
                }

                .address-suggestions {
                    margin-top: 8px;
                    border: 1px solid rgba(149, 213, 178, 0.35);
                    border-radius: 10px;
                    overflow: hidden;
                    background: rgba(15, 27, 27, 0.96);
                }

                .address-suggestion-item {
                    width: 100%;
                    border: 0;
                    border-bottom: 1px solid rgba(149, 213, 178, 0.12);
                    background: transparent;
                    color: var(--cream-100);
                    text-align: left;
                    cursor: pointer;
                    padding: 10px 12px;
                    font-size: 0.92rem;
                }

                .address-suggestion-item:last-child {
                    border-bottom: 0;
                }

                .address-suggestion-item:hover {
                    background: rgba(149, 213, 178, 0.16);
                }

                .address-suggestion-empty {
                    padding: 10px 12px;
                    color: rgba(245, 245, 220, 0.7);
                    font-size: 0.9rem;
                }
            </style>

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
                    const addressSuggestions = document.getElementById('address-suggestions');
                    const barangay = document.getElementById('listing-barangay');
                    const province = form.querySelector('select[name="province"]');
                    const latitudeInput = document.getElementById('latitude');
                    const longitudeInput = document.getElementById('longitude');
                    const mapElement = document.getElementById('map');
                    const price = document.getElementById('listing-price');
                    const area = document.getElementById('land-area');
                    const pricePerSqm = document.getElementById('price-per-sqm');
                    const feedback = document.getElementById('add-listing-feedback');
                    const submitBtn = document.getElementById('publish-listing-btn');
                    const saveDraftBtn = document.getElementById('save-draft-btn');
                    const geoapifyApiKey = <?= json_encode($geoapifyApiKey, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                    const nasugbuCenter = { lat: 14.0722, lng: 120.6319 };
                    const maxDistanceFromNasugbuKm = 35;
                    const swalCdn = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                    const leafletCssCdn = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    const leafletJsCdn = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';

                    let listingMap = null;
                    let listingMarker = null;
                    let autocompleteTimer = null;
                    let autocompleteRequestId = 0;

                    const toRadians = (value) => (value * Math.PI) / 180;

                    const getDistanceKm = (latA, lngA, latB, lngB) => {
                        const earthRadiusKm = 6371;
                        const dLat = toRadians(latB - latA);
                        const dLng = toRadians(lngB - lngA);

                        const a =
                            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                            Math.cos(toRadians(latA)) * Math.cos(toRadians(latB)) *
                            Math.sin(dLng / 2) * Math.sin(dLng / 2);

                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                        return earthRadiusKm * c;
                    };

                    const isWithinNasugbuBounds = (lat, lng) => {
                        const distance = getDistanceKm(lat, lng, nasugbuCenter.lat, nasugbuCenter.lng);
                        return distance <= maxDistanceFromNasugbuKm;
                    };

                    const isNasugbuResult = (feature) => {
                        if (!feature) return false;

                        const textParts = [
                            feature.city,
                            feature.town,
                            feature.village,
                            feature.municipality,
                            feature.county,
                            feature.state_district,
                            feature.formatted,
                        ]
                            .map((value) => (value || '').toString().trim().toLowerCase())
                            .filter(Boolean);

                        return textParts.some((value) => value.includes('nasugbu'));
                    };

                    const clearMapPin = () => {
                        if (listingMarker && listingMap) {
                            listingMap.removeLayer(listingMarker);
                            listingMarker = null;
                        }

                        if (latitudeInput) latitudeInput.value = '';
                        if (longitudeInput) longitudeInput.value = '';
                    };

                    const hideAddressSuggestions = () => {
                        if (!addressSuggestions) return;
                        addressSuggestions.style.display = 'none';
                        addressSuggestions.innerHTML = '';
                    };

                    const enforceNasugbuInputValidity = (isValid) => {
                        if (!address) return;
                        const message = isValid ? '' : 'Address must be within Nasugbu, Batangas.';
                        address.setCustomValidity(message);
                    };

                    const ensureLeaflet = () => {
                        if (window.L?.map) return window.Promise.resolve(window.L);

                        const existingScript = document.querySelector('script[data-leaflet="true"]');
                        const existingCss = document.querySelector('link[data-leaflet="true"]');

                        if (!existingCss) {
                            const css = document.createElement('link');
                            css.rel = 'stylesheet';
                            css.href = leafletCssCdn;
                            css.dataset.leaflet = 'true';
                            document.head.appendChild(css);
                        }

                        if (existingScript) {
                            return new window.Promise((resolve) => {
                                existingScript.addEventListener('load', () => resolve(window.L || null), { once: true });
                                existingScript.addEventListener('error', () => resolve(null), { once: true });
                            });
                        }

                        return new window.Promise((resolve) => {
                            const script = document.createElement('script');
                            script.src = leafletJsCdn;
                            script.async = true;
                            script.dataset.leaflet = 'true';
                            script.onload = () => resolve(window.L || null);
                            script.onerror = () => resolve(null);
                            document.head.appendChild(script);
                        });
                    };

                    const updateMapPin = (lat, lng, zoomLevel = 16) => {
                        if (!listingMap || !window.L) return;

                        if (!listingMarker) {
                            listingMarker = window.L.marker([lat, lng]).addTo(listingMap);
                        } else {
                            listingMarker.setLatLng([lat, lng]);
                        }

                        listingMap.setView([lat, lng], zoomLevel);
                        if (latitudeInput) latitudeInput.value = Number(lat).toFixed(8);
                        if (longitudeInput) longitudeInput.value = Number(lng).toFixed(8);
                    };

                    const applyAddressFromGeoapify = (feature) => {
                        if (!feature) return;

                        const formatted = (feature.formatted || '').trim();
                        if (formatted && address) {
                            address.value = formatted;
                        }

                        const nextCity = (
                            feature.city ||
                            feature.town ||
                            feature.village ||
                            feature.municipality ||
                            feature.county ||
                            ''
                        ).trim();

                        if (nextCity && city) {
                            city.value = nextCity;
                        }

                        const nextProvince = (feature.state || feature.region || '').trim();
                        if (nextProvince && province) {
                            const match = Array.from(province.options || []).find((option) =>
                                (option.value || '').trim().toLowerCase() === nextProvince.toLowerCase()
                            );

                            if (match) {
                                province.value = match.value;
                            }
                        }

                        syncBarangay();
                    };

                    const geocodeAddress = async (addressText) => {
                        if (!geoapifyApiKey || !addressText) {
                            return null;
                        }

                        const endpoint = `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(addressText)}&limit=1&format=json&filter=countrycode:ph&bias=proximity:${nasugbuCenter.lng},${nasugbuCenter.lat}&apiKey=${encodeURIComponent(geoapifyApiKey)}`;

                        try {
                            const response = await window.fetch(endpoint);
                            if (!response.ok) return null;

                            const payload = await response.json();
                            return payload?.results?.[0] || null;
                        } catch (_error) {
                            return null;
                        }
                    };

                    const reverseGeocodePin = async (lat, lng) => {
                        if (!geoapifyApiKey) return;

                        const endpoint = `https://api.geoapify.com/v1/geocode/reverse?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&format=json&apiKey=${encodeURIComponent(geoapifyApiKey)}`;

                        try {
                            const response = await window.fetch(endpoint);
                            if (!response.ok) return;

                            const payload = await response.json();
                            const feature = payload?.results?.[0] || null;
                            return feature;
                        } catch (_error) {
                            // Silent fail to keep map interaction smooth when geocoding is unavailable.
                            return null;
                        }
                    };

                    const renderAddressSuggestions = (items) => {
                        if (!addressSuggestions) return;

                        if (!Array.isArray(items) || items.length === 0) {
                            addressSuggestions.innerHTML = '<div class="address-suggestion-empty">No Nasugbu matches found.</div>';
                            addressSuggestions.style.display = 'block';
                            return;
                        }

                        addressSuggestions.innerHTML = items.map((item, index) => {
                            const label = (item.formatted || '').replace(/"/g, '&quot;');
                            return `<button type="button" class="address-suggestion-item" data-index="${index}">${label}</button>`;
                        }).join('');

                        addressSuggestions.style.display = 'block';

                        addressSuggestions.querySelectorAll('.address-suggestion-item').forEach((button) => {
                            button.addEventListener('click', () => {
                                const index = Number(button.getAttribute('data-index'));
                                const selected = items[index] || null;
                                if (!selected) return;

                                applyAddressFromGeoapify(selected);
                                if (typeof selected.lat === 'number' && typeof selected.lon === 'number') {
                                    updateMapPin(selected.lat, selected.lon, 16);
                                }

                                enforceNasugbuInputValidity(true);
                                hideAddressSuggestions();
                            });
                        });
                    };

                    const fetchAddressSuggestions = async (query) => {
                        if (!geoapifyApiKey || !query || query.length < 3) {
                            hideAddressSuggestions();
                            return;
                        }

                        const currentRequestId = ++autocompleteRequestId;
                        const endpoint = `https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&limit=6&format=json&filter=countrycode:ph&bias=proximity:${nasugbuCenter.lng},${nasugbuCenter.lat}&apiKey=${encodeURIComponent(geoapifyApiKey)}`;

                        try {
                            const response = await window.fetch(endpoint);
                            if (!response.ok) {
                                hideAddressSuggestions();
                                return;
                            }

                            const payload = await response.json();
                            if (currentRequestId !== autocompleteRequestId) {
                                return;
                            }

                            const allResults = Array.isArray(payload?.results) ? payload.results : [];
                            const nasugbuOnly = allResults.filter((item) => isNasugbuResult(item));
                            renderAddressSuggestions(nasugbuOnly);
                        } catch (_error) {
                            hideAddressSuggestions();
                        }
                    };

                    const ensureNasugbuLocation = async () => {
                        const lat = parseFloat(latitudeInput?.value || '');
                        const lng = parseFloat(longitudeInput?.value || '');
                        const addressText = (address?.value || '').trim();

                        if (Number.isFinite(lat) && Number.isFinite(lng)) {
                            if (!isWithinNasugbuBounds(lat, lng)) {
                                clearMapPin();
                                enforceNasugbuInputValidity(false);
                                await notify({
                                    title: 'Invalid Location',
                                    message: 'Pinned location must be within Nasugbu, Batangas.',
                                    icon: 'error'
                                });
                                return false;
                            }

                            if (geoapifyApiKey) {
                                const reversed = await reverseGeocodePin(lat, lng);
                                if (!isNasugbuResult(reversed)) {
                                    clearMapPin();
                                    enforceNasugbuInputValidity(false);
                                    await notify({
                                        title: 'Invalid Location',
                                        message: 'Only Nasugbu, Batangas addresses are allowed.',
                                        icon: 'error'
                                    });
                                    return false;
                                }

                                applyAddressFromGeoapify(reversed);
                            }

                            enforceNasugbuInputValidity(true);
                            return true;
                        }

                        if (addressText === '') {
                            return true;
                        }

                        if (!geoapifyApiKey) {
                            const localCheck = addressText.toLowerCase().includes('nasugbu');
                            enforceNasugbuInputValidity(localCheck);
                            if (!localCheck) {
                                await notify({
                                    title: 'Invalid Address',
                                    message: 'Address must include Nasugbu, Batangas.',
                                    icon: 'error'
                                });
                            }
                            return localCheck;
                        }

                        const geocoded = await geocodeAddress(addressText);
                        if (!isNasugbuResult(geocoded)) {
                            enforceNasugbuInputValidity(false);
                            await notify({
                                title: 'Invalid Address',
                                message: 'Only addresses within Nasugbu, Batangas are allowed.',
                                icon: 'error'
                            });
                            return false;
                        }

                        if (typeof geocoded.lat === 'number' && typeof geocoded.lon === 'number') {
                            updateMapPin(geocoded.lat, geocoded.lon, 16);
                            applyAddressFromGeoapify(geocoded);
                        }

                        enforceNasugbuInputValidity(true);
                        return true;
                    };

                    const initMap = async () => {
                        if (!mapElement || listingMap) return;

                        const L = await ensureLeaflet();
                        if (!L) return;

                        listingMap = L.map(mapElement, {
                            zoomControl: true,
                            scrollWheelZoom: true,
                        }).setView([nasugbuCenter.lat, nasugbuCenter.lng], 12);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(listingMap);

                        listingMap.on('click', async (event) => {
                            const lat = event?.latlng?.lat;
                            const lng = event?.latlng?.lng;
                            if (typeof lat !== 'number' || typeof lng !== 'number') return;

                            if (!isWithinNasugbuBounds(lat, lng)) {
                                clearMapPin();
                                enforceNasugbuInputValidity(false);
                                await notify({
                                    title: 'Invalid Location',
                                    message: 'Please pin a location within Nasugbu, Batangas only.',
                                    icon: 'error'
                                });
                                return;
                            }

                            if (geoapifyApiKey) {
                                const feature = await reverseGeocodePin(lat, lng);
                                if (!isNasugbuResult(feature)) {
                                    clearMapPin();
                                    enforceNasugbuInputValidity(false);
                                    await notify({
                                        title: 'Invalid Location',
                                        message: 'Pinned location is outside Nasugbu, Batangas.',
                                        icon: 'error'
                                    });
                                    return;
                                }

                                updateMapPin(lat, lng);
                                applyAddressFromGeoapify(feature);
                                enforceNasugbuInputValidity(true);
                                return;
                            }

                            updateMapPin(lat, lng);
                            enforceNasugbuInputValidity(true);
                        });

                        const hasLat = latitudeInput && latitudeInput.value !== '';
                        const hasLng = longitudeInput && longitudeInput.value !== '';
                        if (hasLat && hasLng) {
                            updateMapPin(parseFloat(latitudeInput.value), parseFloat(longitudeInput.value), 15);
                        }

                        setTimeout(() => listingMap?.invalidateSize(), 200);
                    };

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

                    const getSwalBaseConfig = () => ({
                        customClass: {
                            popup: 'landly-swal',
                            confirmButton: 'landly-swal-confirm'
                        },
                        showClass: {
                            popup: 'landly-swal-show'
                        },
                        hideClass: {
                            popup: 'landly-swal-hide'
                        }
                    });

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
                                ...getSwalBaseConfig(),
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
                    address?.addEventListener('input', () => {
                        syncBarangay();
                        enforceNasugbuInputValidity(true);

                        const query = (address.value || '').trim();
                        if (autocompleteTimer) {
                            clearTimeout(autocompleteTimer);
                        }

                        autocompleteTimer = setTimeout(() => {
                            fetchAddressSuggestions(query);
                        }, 300);
                    });

                    address?.addEventListener('blur', () => {
                        setTimeout(() => hideAddressSuggestions(), 120);
                    });

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
                            const isNasugbuValid = await ensureNasugbuLocation();
                            if (!isNasugbuValid) {
                                throw new Error('Only Nasugbu, Batangas locations are allowed.');
                            }

                            const formData = new window.FormData(form);
                            const response = await window.fetch(form.action, {
                                method: 'POST',
                                credentials: 'same-origin',
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
                            if (listingMarker && listingMap) {
                                listingMap.removeLayer(listingMarker);
                                listingMarker = null;
                            }
                            window.dispatchEvent(new window.CustomEvent('seller:listing-updated', {
                                detail: {
                                    listingId: Number(result.listing_id || 0)
                                }
                            }));
                            enforceNasugbuInputValidity(true);
                            hideAddressSuggestions();
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

                    initMap();

                    document.querySelectorAll('.nav-item[data-section="add-listing"]').forEach((item) => {
                        item.addEventListener('click', () => {
                            setTimeout(() => {
                                if (listingMap) {
                                    listingMap.invalidateSize();
                                } else {
                                    initMap();
                                }
                            }, 180);
                        });
                    });
                })();
            </script>