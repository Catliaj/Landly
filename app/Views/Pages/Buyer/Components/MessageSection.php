<section id="section-messages" class="content-section">
    <style>
        #section-messages .messages-shell {
            display: grid;
            grid-template-columns: 350px minmax(0, 1fr);
            gap: 25px;
            height: calc(100vh - 200px);
            min-height: 520px;
            max-height: 760px;
        }

        #section-messages .messages-list,
        #section-messages .message-chat {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 20px;
            overflow: hidden;
            min-width: 0;
            min-height: 0;
        }

        #section-messages .messages-list {
            display: flex;
            flex-direction: column;
        }

        #section-messages .messages-list-header,
        #section-messages .chat-header,
        #section-messages .chat-input {
            backdrop-filter: blur(8px);
        }

        #section-messages .messages-list-header {
            padding: 20px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        #section-messages .messages-list-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--cream-100);
            margin: 0;
        }

        #section-messages .messages-list-body {
            overflow-y: auto;
            min-height: 0;
            flex: 1;
            scrollbar-width: thin;
            scrollbar-color: rgba(149, 213, 178, 0.45) rgba(255, 255, 255, 0.04);
        }

        #section-messages .messages-list-body::-webkit-scrollbar,
        #section-messages .chat-body::-webkit-scrollbar {
            width: 8px;
        }

        #section-messages .messages-list-body::-webkit-scrollbar-track,
        #section-messages .chat-body::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.04);
        }

        #section-messages .messages-list-body::-webkit-scrollbar-thumb,
        #section-messages .chat-body::-webkit-scrollbar-thumb {
            background: rgba(149, 213, 178, 0.45);
            border-radius: 999px;
        }

        #section-messages .message-item {
            display: flex;
            gap: 12px;
            padding: 15px 18px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.08);
            cursor: pointer;
            transition: all 0.3s ease;
            align-items: center;
        }

        #section-messages .message-item:hover,
        #section-messages .message-item.active {
            background: rgba(149, 213, 178, 0.08);
        }

        #section-messages .message-item.unread {
            background: rgba(149, 213, 178, 0.06);
        }

        #section-messages .message-item.unread .message-sender,
        #section-messages .message-item.unread .message-preview {
            font-weight: 600;
            color: var(--cream-100);
        }

        #section-messages .message-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: grid;
            place-items: center;
            font-weight: 700;
            color: var(--green-900);
            flex-shrink: 0;
            overflow: hidden;
        }

        #section-messages .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        #section-messages .message-content {
            flex: 1;
            min-width: 0;
        }

        #section-messages .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        #section-messages .message-sender {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        #section-messages .message-time {
            font-size: 0.75rem;
            color: rgba(254, 250, 224, 0.45);
            white-space: nowrap;
        }

        #section-messages .message-preview-row {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        #section-messages .message-preview {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.68);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }

        #section-messages .message-unread-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            background: #e74c3c;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        #section-messages .message-chat {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        #section-messages .chat-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 20px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
            justify-content: space-between;
        }

        #section-messages .chat-header-main {
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 0;
            flex: 1;
        }

        #section-messages .chat-back-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid rgba(149, 213, 178, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: var(--cream-100);
            flex-shrink: 0;
        }

        #section-messages .chat-user-info {
            min-width: 0;
        }

        #section-messages .chat-user-info h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--cream-100);
            margin: 0;
        }

        #section-messages .chat-user-info span {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.5);
        }

        #section-messages .chat-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.76rem;
            color: rgba(254, 250, 224, 0.55);
            white-space: nowrap;
        }

        #section-messages .chat-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2ecc71;
            box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.12);
        }

        #section-messages .chat-report-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 10px;
            border: 1px solid rgba(149, 213, 178, 0.3);
            background: transparent;
            color: var(--cream-100);
            cursor: pointer;
            transition: all 0.25s ease;
            flex-shrink: 0;
        }

        #section-messages .chat-report-btn:hover {
            background: rgba(149, 213, 178, 0.08);
            box-shadow: 0 5px 15px rgba(210, 180, 140, 0.15);
        }

        #section-messages .chat-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            min-height: 0;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: rgba(149, 213, 178, 0.45) rgba(255, 255, 255, 0.04);
        }

        #section-messages .chat-message {
            max-width: min(70%, 480px);
            margin-bottom: 15px;
        }

        #section-messages .chat-message.received {
            margin-right: auto;
        }

        #section-messages .chat-message.sent {
            margin-left: auto;
        }

        #section-messages .chat-bubble {
            padding: 12px 18px;
            border-radius: 18px;
            font-size: 0.9rem;
            line-height: 1.5;
            word-break: break-word;
        }

        #section-messages .chat-message.received .chat-bubble {
            background: rgba(255, 255, 255, 0.1);
            color: var(--cream-100);
            border-bottom-left-radius: 4px;
        }

        #section-messages .chat-message.sent .chat-bubble {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: var(--green-900);
            border-bottom-right-radius: 4px;
        }

        #section-messages .chat-time {
            font-size: 0.7rem;
            color: rgba(254, 250, 224, 0.4);
            margin-top: 5px;
        }

        #section-messages .chat-message.sent .chat-time {
            text-align: right;
        }

        #section-messages .chat-input {
            display: flex;
            gap: 12px;
            padding: 18px 20px 20px;
            border-top: 1px solid rgba(149, 213, 178, 0.1);
            position: sticky;
            bottom: 0;
            background: linear-gradient(180deg, rgba(15, 40, 24, 0.92), rgba(13, 40, 24, 0.98));
        }

        #section-messages .chat-input input {
            flex: 1;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(149, 213, 178, 0.2);
            border-radius: 30px;
            color: var(--cream-100);
            font-size: 0.9rem;
            outline: none;
            min-width: 0;
        }

        #section-messages .chat-input input:focus {
            border-color: var(--accent);
        }

        #section-messages .chat-send-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            border: none;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        #section-messages .chat-send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(149, 213, 178, 0.4);
        }

        #section-messages .chat-send-btn svg {
            width: 20px;
            height: 20px;
            stroke: var(--green-900);
            stroke-width: 2;
            fill: none;
        }

        @media (max-width: 991.98px) {
            #section-messages .messages-shell {
                grid-template-columns: 1fr;
                height: calc(100vh - 180px);
                min-height: 480px;
                max-height: none;
            }

            #section-messages .messages-list,
            #section-messages .message-chat {
                height: 100%;
            }

            #section-messages .messages-list {
                min-height: 0;
            }

            #section-messages .message-chat {
                display: none;
            }

            #section-messages.chat-open .messages-list {
                display: none;
            }

            #section-messages.chat-open .message-chat {
                display: flex;
                height: 100%;
            }

            #section-messages.chat-open .chat-back-btn {
                display: inline-flex;
            }

            #section-messages .chat-message {
                max-width: 88%;
            }
        }
    </style>

    <div class="messages-shell">
        <div class="messages-list" data-role="conversation-list">
            <div class="messages-list-header d-flex align-items-center justify-content-between gap-3">
                <h3>Conversations</h3>
                <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis d-none d-md-inline-flex" id="buyerConversationTotalBadge">0</span>
            </div>
            <div class="messages-list-body" id="buyerConversationList">
                <div class="message-item active">
                    <div class="message-avatar">••</div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender">Loading conversations...</span>
                            <span class="message-time">Now</span>
                        </div>
                        <p class="message-preview">Please wait.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="message-chat" data-role="chat-panel">
            <div class="chat-header">
                <div class="chat-header-main">
                    <button class="chat-back-btn" id="buyerChatBackBtn" type="button" aria-label="Back to conversations">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                    <div class="message-avatar" id="buyerChatAvatar">--</div>
                    <div class="chat-user-info">
                        <h4 id="buyerChatName">Select a conversation</h4>
                        <span id="buyerChatListing">Choose a conversation from the left</span>
                    </div>
                </div>
                <div class="chat-status" id="buyerChatStatus" hidden>
                    <span class="chat-status-dot"></span>
                    <span>Online</span>
                </div>
                <button id="reportMessageBtn" class="chat-report-btn" type="button" title="Report message" aria-label="Report this conversation" onclick="openMessageReportModal()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V4s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                </button>
            </div>
            <div class="chat-body" id="buyerChatBody">
                <div class="chat-message received">
                    <div class="chat-bubble">No messages yet.</div>
                    <div class="chat-time">Now</div>
                </div>
            </div>
            <div class="chat-input">
                <input id="buyerChatInput" type="text" placeholder="Type a message..." autocomplete="off">
                <button class="chat-send-btn" id="buyerChatSendBtn" type="button" disabled>
                    <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                </button>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        const section = document.getElementById('section-messages');
        if (!section) {
            return;
        }

        const currentUserId = Number('<?= (int) (session('user_id') ?? session('UserID') ?? 0) ?>');
        const endpoints = {
            sessions: '<?= base_url('messages/sessions') ?>',
            messagesBase: '<?= base_url('messages/sessions') ?>',
            startSession: '<?= base_url('messages/sessions/start') ?>',
            send: '<?= base_url('messages/send') ?>'
        };

        const conversationList = document.getElementById('buyerConversationList');
        const chatBody = document.getElementById('buyerChatBody');
        const chatInput = document.getElementById('buyerChatInput');
        const sendButton = document.getElementById('buyerChatSendBtn');
        const chatName = document.getElementById('buyerChatName');
        const chatListing = document.getElementById('buyerChatListing');
        const chatAvatar = document.getElementById('buyerChatAvatar');
        const chatBackBtn = document.getElementById('buyerChatBackBtn');
        const conversationTotalBadge = document.getElementById('buyerConversationTotalBadge');
        const chatStatus = document.getElementById('buyerChatStatus');

        const state = {
            sessions: [],
            activeSessionId: null
        };
        let initPromise = null;
        let pollingTimer = null;
        let pollingInFlight = false;
        const POLLING_INTERVAL_MS = 5000;
        const BUYER_MESSAGES_MOBILE_BREAKPOINT = window.matchMedia('(max-width: 991.98px)');
        const DateCtor = window.Date;

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

        function getInitials(name) {
            const safeName = String(name || 'Seller').trim();
            if (!safeName) {
                return 'SE';
            }

            const parts = safeName.split(/\s+/).filter(Boolean);
            if (parts.length === 1) {
                return parts[0].slice(0, 2).toUpperCase();
            }

            return (parts[0][0] + parts[1][0]).toUpperCase();
        }

        function getAvatarMarkup(session) {
            const avatarUrl = String(session.seller_avatar_url || '').trim();
            const label = getCounterpartyLabel(session);

            if (avatarUrl !== '') {
                return `<img src="${escapeHtml(avatarUrl)}" alt="${escapeHtml(label)}">`;
            }

            return escapeHtml(getInitials(label));
        }

        function isBuyerMessagesMobile() {
            return BUYER_MESSAGES_MOBILE_BREAKPOINT.matches;
        }

        function setBuyerMessagesView(chatOpen) {
            section.classList.toggle('chat-open', Boolean(chatOpen) && isBuyerMessagesMobile());
        }

        function showConversationListView() {
            setBuyerMessagesView(false);
        }

        function showChatView() {
            setBuyerMessagesView(true);
        }

        function formatRelativeTime(dateText) {
            if (!dateText) {
                return 'Now';
            }

            const value = new DateCtor(dateText.replace(' ', 'T'));
            if (Number.isNaN(value.getTime())) {
                return 'Now';
            }

            const diffMs = DateCtor.now() - value.getTime();
            const diffMin = Math.max(0, Math.floor(diffMs / 60000));

            if (diffMin < 1) return 'Now';
            if (diffMin < 60) return `${diffMin}m ago`;

            const diffHours = Math.floor(diffMin / 60);
            if (diffHours < 24) return `${diffHours}h ago`;

            const diffDays = Math.floor(diffHours / 24);
            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return `${diffDays}d ago`;

            return value.toLocaleDateString();
        }

        function formatChatTime(dateText) {
            if (!dateText) {
                return 'Now';
            }

            const value = new DateCtor(dateText.replace(' ', 'T'));
            if (Number.isNaN(value.getTime())) {
                return 'Now';
            }

            return value.toLocaleString([], {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        }

        function setChatHeader(session) {
            if (!session) {
                chatName.textContent = 'Select a conversation';
                chatListing.textContent = 'Choose a conversation from the left';
                chatAvatar.textContent = '--';
                if (chatStatus) {
                    chatStatus.hidden = true;
                }
                return;
            }

            const sellerLabel = getCounterpartyLabel(session);
            chatName.textContent = sellerLabel;
            chatListing.textContent = `Listing: ${session.listing_title || 'Property Inquiry'}`;
            chatAvatar.textContent = getInitials(sellerLabel);
            if (chatStatus) {
                chatStatus.hidden = false;
            }
        }

        function getCounterpartyLabel(session) {
            const sellerName = String(session.seller_name || '').trim();
            if (sellerName !== '') {
                return sellerName;
            }

            const sellerId = Number(session.seller_id || 0);
            return sellerId > 0 ? `Seller #${sellerId}` : 'Seller';
        }

        function renderSessions() {
            if (conversationTotalBadge) {
                conversationTotalBadge.textContent = String(state.sessions.length);
            }

            if (!state.sessions.length) {
                conversationList.innerHTML = `
                    <div class="message-item active">
                        <div class="message-avatar">--</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">No conversations yet</span>
                                <span class="message-time">Now</span>
                            </div>
                            <p class="message-preview">Your messages will appear here.</p>
                        </div>
                    </div>
                `;
                return;
            }

            conversationList.innerHTML = state.sessions.map((session) => {
                const isActive = Number(session.session_id) === Number(state.activeSessionId);
                const sellerLabel = getCounterpartyLabel(session);
                const listingTitle = session.listing_title || 'Property Inquiry';
                const unreadCount = Number(session.unread_count || 0);
                const avatarMarkup = getAvatarMarkup(session);
                const previewText = String(session.last_message_preview || session.last_message || `Listing: ${listingTitle}`);

                return `
                    <div class="message-item ${isActive ? 'active' : ''} ${unreadCount > 0 ? 'unread' : ''}" data-session-id="${session.session_id}">
                        <div class="message-avatar">${avatarMarkup}</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">${escapeHtml(sellerLabel)}</span>
                                <span class="message-time">${escapeHtml(formatRelativeTime(session.last_message_at))}</span>
                            </div>
                            <div class="message-preview-row">
                                <p class="message-preview">${escapeHtml(previewText || `Listing: ${listingTitle}`)}</p>
                                ${unreadCount > 0 ? `<span class="message-unread-badge">${unreadCount > 99 ? '99+' : unreadCount}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function renderMessages(messages) {
            if (!Array.isArray(messages) || messages.length === 0) {
                chatBody.innerHTML = `
                    <div class="chat-message received">
                        <div class="chat-bubble">No messages in this conversation yet.</div>
                        <div class="chat-time">Now</div>
                    </div>
                `;
                return;
            }

            chatBody.innerHTML = messages.map((message) => {
                const isSent = Number(message.sender_id) === currentUserId;
                const bubbleText = message.message_text || 'Attachment sent';

                return `
                    <div class="chat-message ${isSent ? 'sent' : 'received'}" data-message-id="${Number(message.message_id || 0)}">
                        <div class="chat-bubble">${escapeHtml(bubbleText)}</div>
                        <div class="chat-time">${escapeHtml(formatChatTime(message.sent_at))}</div>
                    </div>
                `;
            }).join('');

            chatBody.scrollTop = chatBody.scrollHeight;
        }

        async function fetchSessions() {
            const response = await fetch(endpoints.sessions, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                throw new Error('Failed to load sessions');
            }

            const data = await response.json();
            const sessions = Array.isArray(data.sessions) ? data.sessions : [];
            state.sessions = sessions
                .filter((session) => Number(session.buyer_id) === currentUserId)
                .sort((left, right) => {
                    const rightTime = new DateCtor(String(right.last_message_at || '')).getTime() || 0;
                    const leftTime = new DateCtor(String(left.last_message_at || '')).getTime() || 0;
                    return rightTime - leftTime;
                });

            if (!state.activeSessionId && state.sessions.length > 0) {
                state.activeSessionId = Number(state.sessions[0].session_id);
            }

            renderSessions();
        }

        async function fetchMessages(sessionId) {
            const response = await fetch(`${endpoints.messagesBase}/${sessionId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                throw new Error('Failed to load messages');
            }

            const data = await response.json();
            renderMessages(data.messages);

            const activeSession = state.sessions.find((session) => Number(session.session_id) === Number(sessionId));
            setChatHeader(activeSession || null);

            if (activeSession) {
                activeSession.unread_count = 0;
                renderSessions();
                if (typeof refreshBuyerSidebarCounts === 'function') {
                    refreshBuyerSidebarCounts();
                }
            }
        }

        async function refreshMessagesData() {
            if (pollingInFlight || document.hidden) {
                return;
            }

            pollingInFlight = true;

            try {
                await fetchSessions();

                if (state.activeSessionId) {
                    const exists = state.sessions.some((session) => Number(session.session_id) === Number(state.activeSessionId));
                    if (exists) {
                        await fetchMessages(state.activeSessionId);
                    }
                }
            } catch (error) {
            } finally {
                pollingInFlight = false;
            }
        }

        function startPolling() {
            if (pollingTimer) {
                return;
            }

            pollingTimer = window.setInterval(refreshMessagesData, POLLING_INTERVAL_MS);
        }

        function stopPolling() {
            if (!pollingTimer) {
                return;
            }

            window.clearInterval(pollingTimer);
            pollingTimer = null;
        }

        function isMessagesSectionActive() {
            return section.classList.contains('active');
        }

        function updatePollingState() {
            if (isMessagesSectionActive()) {
                startPolling();
                refreshMessagesData();
                return;
            }

            stopPolling();
        }

        async function selectSession(sessionId) {
            state.activeSessionId = Number(sessionId);

            const activeSession = state.sessions.find((session) => Number(session.session_id) === Number(state.activeSessionId));
            if (activeSession) {
                activeSession.unread_count = 0;
            }

            renderSessions();
            sendButton.disabled = false;
            await fetchMessages(state.activeSessionId);

            if (isBuyerMessagesMobile()) {
                showChatView();
            }
        }

        async function startConversationSession(listingId, inquiryId) {
            const payload = {
                listing_id: Number(listingId || 0),
                inquiry_id: Number(inquiryId || 0)
            };

            if (!payload.listing_id || payload.listing_id <= 0) {
                return 0;
            }

            const response = await fetch(endpoints.startSession, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                return 0;
            }

            const data = await response.json();
            return Number(data.session_id || 0);
        }

        window.openBuyerConversation = async function (sessionContext) {
            let targetSessionId = 0;
            let listingId = 0;
            let inquiryId = 0;

            if (typeof sessionContext === 'object' && sessionContext !== null) {
                targetSessionId = Number(sessionContext.sessionId || 0);
                listingId = Number(sessionContext.listingId || 0);
                inquiryId = Number(sessionContext.inquiryId || 0);
            } else {
                targetSessionId = Number(sessionContext || 0);
            }

            if (typeof window.showSection === 'function') {
                window.showSection('messages');
            }

            if (initPromise) {
                await initPromise;
            }

            if (!targetSessionId || targetSessionId <= 0) {
                targetSessionId = await startConversationSession(listingId, inquiryId);
                if (!targetSessionId || targetSessionId <= 0) {
                    alert('Unable to open conversation for this inquiry right now.');
                    return;
                }
            }

            const exists = state.sessions.some((session) => Number(session.session_id) === targetSessionId);

            if (!exists) {
                await fetchSessions();
            }

            const targetExists = state.sessions.some((session) => Number(session.session_id) === targetSessionId);
            if (!targetExists) {
                alert('Conversation session not found for this inquiry yet.');
                return;
            }

            await selectSession(targetSessionId);

            if (isBuyerMessagesMobile()) {
                showChatView();
            }
        };

        async function sendCurrentMessage() {
            if (!state.activeSessionId) {
                return;
            }

            const messageText = chatInput.value.trim();
            if (!messageText) {
                return;
            }

            sendButton.disabled = true;

            try {
                const response = await fetch(endpoints.send, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        session_id: state.activeSessionId,
                        message_text: messageText
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to send message');
                }

                chatInput.value = '';
                await fetchMessages(state.activeSessionId);
                await fetchSessions();
            } catch (error) {
                chatBody.insertAdjacentHTML('beforeend', `
                    <div class="chat-message received">
                        <div class="chat-bubble">Unable to send message right now. Please try again.</div>
                        <div class="chat-time">Now</div>
                    </div>
                `);
                chatBody.scrollTop = chatBody.scrollHeight;
            } finally {
                sendButton.disabled = false;
            }
        }

        conversationList.addEventListener('click', (event) => {
            const item = event.target.closest('.message-item[data-session-id]');
            if (!item) {
                return;
            }

            const sessionId = Number(item.dataset.sessionId || 0);
            if (sessionId > 0) {
                selectSession(sessionId);
            }
        });

        chatBackBtn?.addEventListener('click', () => {
            showConversationListView();
        });

        sendButton.addEventListener('click', sendCurrentMessage);
        chatInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendCurrentMessage();
            }
        });

        window.addEventListener('buyer:section-changed', () => {
            updatePollingState();
            if (isBuyerMessagesMobile()) {
                showConversationListView();
            }
        });

        document.addEventListener('visibilitychange', () => {
            updatePollingState();
        });

        BUYER_MESSAGES_MOBILE_BREAKPOINT.addEventListener('change', () => {
            if (isBuyerMessagesMobile()) {
                showConversationListView();
            } else {
                setBuyerMessagesView(false);
            }
        });

        window.addEventListener('resize', () => {
            if (!isBuyerMessagesMobile()) {
                setBuyerMessagesView(false);
            }
        });

        initPromise = (async function initMessaging() {
            try {
                await fetchSessions();

                if (state.activeSessionId) {
                    sendButton.disabled = false;
                    await fetchMessages(state.activeSessionId);
                } else {
                    setChatHeader(null);
                    sendButton.disabled = true;
                }
            } catch (error) {
                conversationList.innerHTML = `
                    <div class="message-item active">
                        <div class="message-avatar">!!</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">Unable to load conversations</span>
                                <span class="message-time">Now</span>
                            </div>
                            <p class="message-preview">Please refresh the page.</p>
                        </div>
                    </div>
                `;
                setChatHeader(null);
                sendButton.disabled = true;
            }

            updatePollingState();
            if (isBuyerMessagesMobile()) {
                showConversationListView();
            }
        })();
    })();
</script>
