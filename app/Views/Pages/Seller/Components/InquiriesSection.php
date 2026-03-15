<?php $sellerInquiries = $sellerInquiries ?? []; ?>

<section id="section-inquiries" class="content-section">
    <div class="inquiries-list">
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
                        <button class="inquiry-btn reply" onclick="showSection('messages')">View Conversation</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>