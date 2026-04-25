<section id="section-messages" class="content-section">
    <div class="messages-container">
        <div class="messages-list">
            <div class="messages-list-header">
                <h3>Conversations</h3>
            </div>
            <div class="messages-list-body" id="sellerConversationList">
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

        <div class="message-chat">
            <div class="chat-header">
                <div class="message-avatar" id="sellerChatAvatar">--</div>
                <div class="chat-user-info">
                    <h4 id="sellerChatName">Select a conversation</h4>
                    <span id="sellerChatListing">Choose a conversation from the left</span>
                </div>
                <div class="seller-inquiry-controls">
                    <span id="sellerInquiryStatusBadge" class="seller-inquiry-status-pill status-pending">Pending</span>
                    <select id="sellerInquiryStatusSelect" class="seller-inquiry-status-select status-pending" disabled>
                        <option value="pending">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="reserved">Reserved</option>
                        <option value="closed">Closed</option>
                    </select>
                    <button id="sellerInquiryStatusUpdateBtn" class="seller-inquiry-status-btn" type="button" disabled>Update Status</button>
                </div>
            </div>
            <div class="chat-body" id="sellerChatBody">
                <div class="chat-message received">
                    <div class="chat-bubble">No messages yet.</div>
                    <div class="chat-time">Now</div>
                </div>
            </div>
            <div class="chat-input">
                <input id="sellerChatInput" type="text" placeholder="Type a message..." autocomplete="off">
                <button class="chat-send-btn" id="sellerChatSendBtn" type="button" disabled>
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
            send: '<?= base_url('messages/send') ?>'
        };

        const conversationList = document.getElementById('sellerConversationList');
        const chatBody = document.getElementById('sellerChatBody');
        const chatInput = document.getElementById('sellerChatInput');
        const sendButton = document.getElementById('sellerChatSendBtn');
        const chatName = document.getElementById('sellerChatName');
        const chatListing = document.getElementById('sellerChatListing');
        const chatAvatar = document.getElementById('sellerChatAvatar');
        const inquiryStatusBadge = document.getElementById('sellerInquiryStatusBadge');
        const inquiryStatusSelect = document.getElementById('sellerInquiryStatusSelect');
        const inquiryStatusUpdateBtn = document.getElementById('sellerInquiryStatusUpdateBtn');

        const state = {
            sessions: [],
            activeSessionId: null,
            activeInquiryId: null,
            activeInquiryStatus: 'pending'
        };
        let initPromise = null;
        let pollingTimer = null;
        let pollingInFlight = false;
        const POLLING_INTERVAL_MS = 2000;

        const allowedInquiryStatuses = ['pending', 'accepted', 'rejected', 'reserved', 'closed'];

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

        function getInitials(name) {
            const safeName = String(name || 'Buyer').trim();
            if (!safeName) {
                return 'BY';
            }

            const parts = safeName.split(/\s+/).filter(Boolean);
            if (parts.length === 1) {
                return parts[0].slice(0, 2).toUpperCase();
            }

            return (parts[0][0] + parts[1][0]).toUpperCase();
        }

        function formatRelativeTime(dateText) {
            if (!dateText) {
                return 'Now';
            }

            const value = new window.Date(dateText.replace(' ', 'T'));
            if (Number.isNaN(value.getTime())) {
                return 'Now';
            }

            const diffMs = window.Date.now() - value.getTime();
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

            const value = new window.Date(dateText.replace(' ', 'T'));
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

        function getCounterpartyLabel(session) {
            const buyerName = String(session.buyer_name || '').trim();
            if (buyerName !== '') {
                return buyerName;
            }

            const buyerId = Number(session.buyer_id || 0);
            return buyerId > 0 ? `Buyer #${buyerId}` : 'Buyer';
        }

        function setChatHeader(session) {
            if (!session) {
                chatName.textContent = 'Select a conversation';
                chatListing.textContent = 'Choose a conversation from the left';
                chatAvatar.textContent = '--';
                setInquiryControls(null);
                return;
            }

            const buyerLabel = getCounterpartyLabel(session);
            chatName.textContent = buyerLabel;
            chatListing.textContent = `Listing: ${session.listing_title || 'Property Inquiry'}`;
            chatAvatar.textContent = getInitials(buyerLabel);
            setInquiryControls(session);
        }

        function normalizeInquiryStatus(statusValue) {
            const status = String(statusValue || '').toLowerCase();
            return allowedInquiryStatuses.includes(status) ? status : 'pending';
        }

        function statusToLabel(status) {
            return status.charAt(0).toUpperCase() + status.slice(1);
        }

        function applyInquiryStatusTheme(status) {
            const normalized = normalizeInquiryStatus(status);
            inquiryStatusBadge.className = `seller-inquiry-status-pill status-${normalized}`;
            inquiryStatusSelect.className = `seller-inquiry-status-select status-${normalized}`;
            inquiryStatusBadge.textContent = statusToLabel(normalized);
        }

        function setInquiryControls(session) {
            if (!session) {
                state.activeInquiryId = null;
                state.activeInquiryStatus = 'pending';
                inquiryStatusSelect.value = 'pending';
                inquiryStatusSelect.disabled = true;
                inquiryStatusUpdateBtn.disabled = true;
                applyInquiryStatusTheme('pending');
                return;
            }

            state.activeInquiryId = Number(session.inquiry_id || 0) || null;
            state.activeInquiryStatus = normalizeInquiryStatus(session.inquiry_status);
            inquiryStatusSelect.value = state.activeInquiryStatus;
            inquiryStatusSelect.disabled = !state.activeInquiryId;
            inquiryStatusUpdateBtn.disabled = !state.activeInquiryId;
            applyInquiryStatusTheme(state.activeInquiryStatus);
        }

        async function updateActiveInquiryStatus(nextStatus, options = {}) {
            const { silent = false, refreshSessions = true } = options;

            if (!state.activeInquiryId) {
                if (!silent) {
                    alert('No inquiry linked to this conversation.');
                }
                return false;
            }

            const normalizedStatus = normalizeInquiryStatus(nextStatus);

            const response = await fetch(`<?= base_url('messages/inquiries') ?>/${state.activeInquiryId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ inquiry_status: normalizedStatus })
            });

            if (!response.ok) {
                if (!silent) {
                    alert('Unable to update inquiry status right now.');
                }
                return false;
            }

            const data = await response.json();
            const resolvedStatus = normalizeInquiryStatus(data.inquiry_status || normalizedStatus);

            state.activeInquiryStatus = resolvedStatus;
            inquiryStatusSelect.value = resolvedStatus;
            applyInquiryStatusTheme(resolvedStatus);

            state.sessions = state.sessions.map((session) => {
                if (Number(session.inquiry_id || 0) !== Number(state.activeInquiryId || 0)) {
                    return session;
                }

                return {
                    ...session,
                    inquiry_status: resolvedStatus,
                };
            });

            if (refreshSessions) {
                await fetchSessions();
            } else {
                renderSessions();
            }

            window.dispatchEvent(new window.CustomEvent('seller:inquiry-updated', {
                detail: {
                    inquiryId: Number(state.activeInquiryId || 0),
                    inquiryStatus: resolvedStatus
                }
            }));

            return true;
        }

        function renderSessions() {
            if (!state.sessions.length) {
                conversationList.innerHTML = `
                    <div class="message-item active">
                        <div class="message-avatar">--</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">No conversations yet</span>
                                <span class="message-time">Now</span>
                            </div>
                            <p class="message-preview">Buyer inquiries will appear here.</p>
                        </div>
                    </div>
                `;
                return;
            }

            conversationList.innerHTML = state.sessions.map((session) => {
                const isActive = Number(session.session_id) === Number(state.activeSessionId);
                const buyerLabel = getCounterpartyLabel(session);
                const listingTitle = session.listing_title || 'Property Inquiry';

                return `
                    <div class="message-item ${isActive ? 'active' : ''}" data-session-id="${session.session_id}">
                        <div class="message-avatar">${escapeHtml(getInitials(buyerLabel))}</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">${escapeHtml(buyerLabel)}</span>
                                <span class="message-time">${escapeHtml(formatRelativeTime(session.last_message_at))}</span>
                            </div>
                            <p class="message-preview">${escapeHtml(`Listing: ${listingTitle}`)}</p>
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
                    <div class="chat-message ${isSent ? 'sent' : 'received'}">
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
            state.sessions = sessions.filter((session) => Number(session.seller_id) === currentUserId);

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
            renderSessions();
            sendButton.disabled = false;
            await fetchMessages(state.activeSessionId);
        }

        window.openSellerConversation = async function (sessionId) {
            const targetSessionId = Number(sessionId || 0);

            if (typeof window.showSection === 'function') {
                window.showSection('messages');
            }

            if (initPromise) {
                await initPromise;
            }

            if (!targetSessionId || targetSessionId <= 0) {
                return;
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

                if (state.activeInquiryId && state.activeInquiryStatus === 'pending') {
                    await updateActiveInquiryStatus('accepted', {
                        silent: true,
                        refreshSessions: false
                    });
                }

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

        sendButton.addEventListener('click', sendCurrentMessage);
        inquiryStatusSelect.addEventListener('change', () => {
            applyInquiryStatusTheme(inquiryStatusSelect.value);
            inquiryStatusUpdateBtn.disabled = !state.activeInquiryId || inquiryStatusSelect.value === state.activeInquiryStatus;
        });

        inquiryStatusUpdateBtn.addEventListener('click', async () => {
            if (!state.activeInquiryId) {
                return;
            }

            const desiredStatus = normalizeInquiryStatus(inquiryStatusSelect.value);
            if (desiredStatus === state.activeInquiryStatus) {
                return;
            }

            inquiryStatusUpdateBtn.disabled = true;

            try {
                const updated = await updateActiveInquiryStatus(desiredStatus, { silent: false, refreshSessions: true });
                if (!updated) {
                    inquiryStatusSelect.value = state.activeInquiryStatus;
                    applyInquiryStatusTheme(state.activeInquiryStatus);
                }
            } finally {
                inquiryStatusUpdateBtn.disabled = !state.activeInquiryId || inquiryStatusSelect.value === state.activeInquiryStatus;
            }
        });

        chatInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendCurrentMessage();
            }
        });

        window.addEventListener('seller:section-changed', () => {
            updatePollingState();
        });

        document.addEventListener('visibilitychange', () => {
            updatePollingState();
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
        })();
    })();
</script>