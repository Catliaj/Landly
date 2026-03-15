<?php $buyerInquiries = $buyerInquiries ?? []; ?>

<section id="section-inquiries" class="content-section">
    <div class="inquiries-list">
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
    function openInquiryConversation(sessionId, listingId, inquiryId) {
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
    }
</script>