<?php $sellerInquiries = $sellerInquiries ?? []; ?>

<section id="section-inquiries" class="content-section">
    <div class="inquiries-list" id="sellerInquiriesList">
        <?php if ($sellerInquiries === []): ?>
            <div class="inquiry-card">
                <p class="inquiry-message">No buyer inquiries yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($sellerInquiries as $inquiry): ?>
                <div class="inquiry-card" data-inquiry-id="<?= (int) ($inquiry['inquiry_id'] ?? 0) ?>">
                    <div class="inquiry-header">
                        <div class="inquiry-user">
                            <div class="inquiry-user-avatar"><?= esc((string) ($inquiry['buyer_initials'] ?? 'NA')) ?></div>
                            <div class="inquiry-user-info">
                                <h4><?= esc((string) ($inquiry['buyer_name'] ?? 'Buyer')) ?></h4>
                                <span><?= esc((string) ($inquiry['date_label'] ?? 'Date unavailable')) ?></span>
                            </div>
                        </div>
                        <span class="inquiry-status <?= esc((string) ($inquiry['status_class'] ?? 'new')) ?>"><?= esc((string) ($inquiry['status_label'] ?? 'New')) ?></span>
                    </div>
                    <div class="inquiry-property">
                        <div class="inquiry-property-title"><?= esc((string) ($inquiry['title'] ?? 'Untitled Listing')) ?></div>
                        <div class="inquiry-property-detail"><?= esc((string) ($inquiry['price_label'] ?? '₱0.00')) ?></div>
                    </div>
                    <p class="inquiry-message"><?= esc((string) ($inquiry['message_preview'] ?? 'New inquiry received.')) ?></p>
                    <div class="inquiry-actions">
                        <button class="inquiry-btn reply" onclick="openSellerInquiryConversation(<?= (int) ($inquiry['session_id'] ?? 0) ?>)">View Conversation</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script>
    (function () {
        const listContainer = document.getElementById('sellerInquiriesList');
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

        function formatDate(value) {
            if (!value) {
                return 'Date unavailable';
            }

            const date = new window.Date(String(value).replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) {
                return 'Date unavailable';
            }

            return date.toLocaleString([], {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        }

        function statusLabel(status) {
            const normalized = String(status || 'pending').toLowerCase();
            return normalized.charAt(0).toUpperCase() + normalized.slice(1);
        }

        function statusClass(status) {
            const normalized = String(status || 'pending').toLowerCase();
            return ['pending', 'accepted', 'rejected', 'reserved', 'closed'].includes(normalized)
                ? normalized
                : 'pending';
        }

        function initials(firstName, lastName) {
            const first = String(firstName || '').trim().charAt(0);
            const last = String(lastName || '').trim().charAt(0);
            return (first + last).toUpperCase() || 'BY';
        }

        function renderInquiries(inquiries) {
            if (!Array.isArray(inquiries) || inquiries.length === 0) {
                listContainer.innerHTML = `
                    <div class="inquiry-card">
                        <p class="inquiry-message">No buyer inquiries yet.</p>
                    </div>
                `;
                return;
            }

            const sellerInquiries = inquiries.filter((inquiry) => Number(inquiry.seller_id || 0) === currentUserId);
            if (sellerInquiries.length === 0) {
                listContainer.innerHTML = `
                    <div class="inquiry-card">
                        <p class="inquiry-message">No buyer inquiries yet.</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = sellerInquiries.map((inquiry) => {
                const buyerName = `${inquiry.buyer_first_name || ''} ${inquiry.buyer_last_name || ''}`.trim() || 'Buyer';
                const inquiryStatus = statusClass(inquiry.inquiry_status);

                return `
                    <div class="inquiry-card" data-inquiry-id="${Number(inquiry.inquiry_id || 0)}">
                        <div class="inquiry-header">
                            <div class="inquiry-user">
                                <div class="inquiry-user-avatar">${escapeHtml(initials(inquiry.buyer_first_name, inquiry.buyer_last_name))}</div>
                                <div class="inquiry-user-info">
                                    <h4>${escapeHtml(buyerName)}</h4>
                                    <span>${escapeHtml(formatDate(inquiry.created_at))}</span>
                                </div>
                            </div>
                            <span class="inquiry-status ${escapeHtml(inquiryStatus)}">${escapeHtml(statusLabel(inquiryStatus))}</span>
                        </div>
                        <div class="inquiry-property">
                            <div class="inquiry-property-title">${escapeHtml(inquiry.listing_title || 'Untitled Listing')}</div>
                            <div class="inquiry-property-detail">₱${Number(inquiry.listing_price || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                        </div>
                        <p class="inquiry-message">${escapeHtml(inquiryStatus === 'pending' ? 'New inquiry received. Open conversation to reply.' : 'Inquiry status updated. Open conversation for details.')}</p>
                        <div class="inquiry-actions">
                            <button class="inquiry-btn reply" onclick="openSellerInquiryConversation(${Number(inquiry.session_id || 0)})">View Conversation</button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function refreshSellerInquiries() {
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

        window.openSellerInquiryConversation = function (sessionId) {
            if (typeof window.openSellerConversation === 'function') {
                window.openSellerConversation(Number(sessionId || 0));
                return;
            }

            if (typeof showSection === 'function') {
                showSection('messages');
            }
        };

        window.addEventListener('seller:section-changed', (event) => {
            if (event.detail?.sectionName === 'inquiries') {
                refreshSellerInquiries();
            }
        });

        window.addEventListener('seller:inquiry-updated', refreshSellerInquiries);
    })();
</script>