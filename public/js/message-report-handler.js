/**
 * Message Report Modal Handler
 * Add to your messaging component to enable message reporting
 */

(function() {
    // Message report reasons
    const messageReportReasons = [
        'Harassment',
        'Scam or fraud',
        'Spam',
        'Offensive content',
        'Fake information',
        'Unsafe payment request',
        'Other'
    ];

    function getLatestReportableMessageId(chatBodyId) {
        const chatBody = document.getElementById(chatBodyId);
        if (!chatBody) {
            return 0;
        }

        const messages = Array.from(chatBody.querySelectorAll('.chat-message.received[data-message-id]'));
        const latestMessage = messages[messages.length - 1];
        return Number(latestMessage?.dataset?.messageId || 0);
    }

    // Get active received message ID for reporting
    window.getActiveMessageIdForReport = function() {
        // Try to get from seller messages section
        const sellerMessageId = getLatestReportableMessageId('sellerChatBody');
        if (sellerMessageId > 0) {
            return sellerMessageId;
        }

        // Try to get from buyer messages section if it exists
        const buyerMessageId = getLatestReportableMessageId('buyerChatBody');
        if (buyerMessageId > 0) {
            return buyerMessageId;
        }

        return 0;
    };

    // Open message report modal
    window.openMessageReportModal = function() {
        const messageId = window.getActiveMessageIdForReport();
        if (messageId > 0 && typeof window.openReportModal === 'function') {
            window.openReportModal('message', messageId, messageReportReasons);
        } else {
            alert('Please select a conversation with a message from the other user first.');
        }
    };

    // Attach to report buttons if they exist
    document.addEventListener('DOMContentLoaded', function() {
        const reportBtn = document.getElementById('reportMessageBtn');
        if (reportBtn) {
            reportBtn.addEventListener('click', window.openMessageReportModal);
        }
    });
})();
