<?php $buyerInquiries = $buyerInquiries ?? []; ?>

<section id="section-inquiries" class="content-section">
    <div class="inquiries-list" id="buyerInquiriesList">
        <?php if ($buyerInquiries === []): ?>
            <div class="inquiry-card">
                <div class="inquiry-message">You have no inquiries yet. Browse listings and click Contact Seller to create one.</div>
            </div>
        <?php else: ?>
            <?php foreach ($buyerInquiries as $inquiry): ?>
                <div class="inquiry-card" data-inquiry-id="<?= (int) ($inquiry['inquiry_id'] ?? 0) ?>">
                    <div class="inquiry-header">
                        <div class="inquiry-property">
                            <div class="inquiry-property-thumb">
                                <img src="<?= esc((string) ($inquiry['image_url'] ?? '')) ?>" alt="<?= esc((string) ($inquiry['title'] ?? 'Property')) ?>">
                            </div>
                            <div class="inquiry-property-info">
                                <h4><?= esc((string) ($inquiry['title'] ?? 'Untitled Listing')) ?></h4>
                                <span><?= esc((string) ($inquiry['price_label'] ?? '₱0.00')) ?></span>
                            </div>
                        </div>
                        <span class="inquiry-status <?= esc((string) ($inquiry['status_class'] ?? 'pending')) ?>"><?= esc((string) ($inquiry['status_label'] ?? 'Pending')) ?></span>
                    </div>
                    <div class="inquiry-message">
                        <?= esc((string) ($inquiry['message_preview'] ?? 'Inquiry submitted.')) ?>
                    </div>
                    <div class="inquiry-footer">
                        <span class="inquiry-date"><?= esc((string) ($inquiry['date_label'] ?? 'Date unavailable')) ?></span>
                        <div class="inquiry-actions">
                            <button class="inquiry-btn view" onclick="openInquiryConversation(<?= (int) ($inquiry['session_id'] ?? 0) ?>, <?= (int) ($inquiry['listing_id'] ?? 0) ?>, <?= (int) ($inquiry['inquiry_id'] ?? 0) ?>)">View Conversation</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script>
    (function () {
        const listContainer = document.getElementById('buyerInquiriesList');
        if (!listContainer) {
            return;
        }

        const currentUserId = Number('<?= (int) (session('user_id') ?? session('UserID') ?? 0) ?>');

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

        function statusLabel(status) {
            const normalized = String(status || 'pending').toLowerCase();
            return normalized.charAt(0).toUpperCase() + normalized.slice(1);
        }

        function statusClass(status) {
            const normalized = String(status || 'pending').toLowerCase();
            if (normalized === 'accepted' || normalized === 'reserved' || normalized === 'closed') {
                return 'replied';
            }

            if (normalized === 'rejected') {
                return 'viewed';
            }

            return 'pending';
        }

        function formatDate(createdAt, updatedAt) {
            const created = createdAt ? new window.Date(String(createdAt).replace(' ', 'T')) : null;
            const updated = updatedAt ? new window.Date(String(updatedAt).replace(' ', 'T')) : null;

            const createdLabel = created && !Number.isNaN(created.getTime())
                ? created.toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })
                : 'Date unavailable';

            const updatedLabel = updated && !Number.isNaN(updated.getTime())
                ? updated.toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })
                : '';

            if (updatedLabel && createdAt && updatedAt && createdAt !== updatedAt) {
                return `Sent ${createdLabel} • Updated ${updatedLabel}`;
            }

            return `Sent ${createdLabel}`;
        }

        function previewText(status) {
            const normalized = String(status || 'pending').toLowerCase();
            if (normalized === 'accepted') return 'Your inquiry has been accepted by the seller.';
            if (normalized === 'rejected') return 'Your inquiry was reviewed by the seller.';
            if (normalized === 'reserved') return 'This inquiry is now marked as reserved.';
            if (normalized === 'closed') return 'This inquiry thread has been closed.';
            return 'Inquiry submitted. Waiting for seller response.';
        }

        function renderInquiries(inquiries) {
            const buyerInquiries = (Array.isArray(inquiries) ? inquiries : [])
                .filter((inquiry) => Number(inquiry.buyer_id || 0) === currentUserId);

            if (buyerInquiries.length === 0) {
                listContainer.innerHTML = `
                    <div class="inquiry-card">
                        <div class="inquiry-message">You have no inquiries yet. Browse listings and click Contact Seller to create one.</div>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = buyerInquiries.map((inquiry) => {
                const status = String(inquiry.inquiry_status || 'pending').toLowerCase();
                const mappedClass = statusClass(status);

                return `
                    <div class="inquiry-card" data-inquiry-id="${Number(inquiry.inquiry_id || 0)}">
                        <div class="inquiry-header">
                            <div class="inquiry-property">
                                <div class="inquiry-property-thumb"></div>
                                <div class="inquiry-property-info">
                                    <h4>${escapeHtml(inquiry.listing_title || 'Untitled Listing')}</h4>
                                    <span>₱${Number(inquiry.listing_price || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                                </div>
                            </div>
                            <span class="inquiry-status ${escapeHtml(mappedClass)}">${escapeHtml(statusLabel(status))}</span>
                        </div>
                        <div class="inquiry-message">${escapeHtml(previewText(status))}</div>
                        <div class="inquiry-footer">
                            <span class="inquiry-date">${escapeHtml(formatDate(inquiry.created_at, inquiry.updated_at))}</span>
                            <div class="inquiry-actions">
                                <button class="inquiry-btn view" onclick="openInquiryConversation(${Number(inquiry.session_id || 0)}, ${Number(inquiry.listing_id || 0)}, ${Number(inquiry.inquiry_id || 0)})">View Conversation</button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function refreshBuyerInquiries() {
            try {
                const response = await fetch('<?= base_url('messages/inquiries') ?>', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) {
                    throw new Error('Failed to load inquiries');
                }

                const data = await response.json();
                renderInquiries(data.inquiries || []);
            } catch (error) {
            }
        }

        window.openInquiryConversation = function (sessionId, listingId, inquiryId) {
            if (typeof window.openBuyerConversation === 'function') {
                window.openBuyerConversation({
                    sessionId: Number(sessionId || 0),
                    listingId: Number(listingId || 0),
                    inquiryId: Number(inquiryId || 0)
                });
                return;
            }

            if (typeof showSection === 'function') {
                showSection('messages');
            }
        };

        window.addEventListener('buyer:section-changed', (event) => {
            if (event.detail?.sectionName === 'inquiries') {
                refreshBuyerInquiries();
            }
        });

        window.addEventListener('buyer:inquiry-updated', refreshBuyerInquiries);
    })();
</script>