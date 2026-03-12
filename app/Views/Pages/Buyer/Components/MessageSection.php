<section id="section-messages" class="content-section">
    <div class="messages-container">
        <div class="messages-list">
            <div class="messages-list-header">
                <h3>Conversations</h3>
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

        <div class="message-chat">
            <div class="chat-header">
                <div class="message-avatar" id="buyerChatAvatar">--</div>
                <div class="chat-user-info">
                    <h4 id="buyerChatName">Select a conversation</h4>
                    <span id="buyerChatListing">Choose a conversation from the left</span>
                </div>
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
            send: '<?= base_url('messages/send') ?>'
        };

        const conversationList = document.getElementById('buyerConversationList');
        const chatBody = document.getElementById('buyerChatBody');
        const chatInput = document.getElementById('buyerChatInput');
        const sendButton = document.getElementById('buyerChatSendBtn');
        const chatName = document.getElementById('buyerChatName');
        const chatListing = document.getElementById('buyerChatListing');
        const chatAvatar = document.getElementById('buyerChatAvatar');

        const state = {
            sessions: [],
            activeSessionId: null
        };

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

        function formatRelativeTime(dateText) {
            if (!dateText) {
                return 'Now';
            }

            const value = new Date(dateText.replace(' ', 'T'));
            if (Number.isNaN(value.getTime())) {
                return 'Now';
            }

            const diffMs = Date.now() - value.getTime();
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

            const value = new Date(dateText.replace(' ', 'T'));
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
                return;
            }

            const sellerLabel = `Seller #${session.seller_id ?? ''}`.trim();
            chatName.textContent = sellerLabel;
            chatListing.textContent = `Re: ${session.listing_title || 'Property Inquiry'}`;
            chatAvatar.textContent = getInitials(sellerLabel);
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
                            <p class="message-preview">Your messages will appear here.</p>
                        </div>
                    </div>
                `;
                return;
            }

            conversationList.innerHTML = state.sessions.map((session) => {
                const isActive = Number(session.session_id) === Number(state.activeSessionId);
                const sellerLabel = `Seller #${session.seller_id ?? ''}`.trim();
                const listingTitle = session.listing_title || 'Property Inquiry';

                return `
                    <div class="message-item ${isActive ? 'active' : ''}" data-session-id="${session.session_id}">
                        <div class="message-avatar">${escapeHtml(getInitials(sellerLabel))}</div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">${escapeHtml(sellerLabel)}</span>
                                <span class="message-time">${escapeHtml(formatRelativeTime(session.last_message_at))}</span>
                            </div>
                            <p class="message-preview">${escapeHtml(`Re: ${listingTitle}`)}</p>
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
            state.sessions = Array.isArray(data.sessions) ? data.sessions : [];

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

        async function selectSession(sessionId) {
            state.activeSessionId = Number(sessionId);
            renderSessions();
            sendButton.disabled = false;
            await fetchMessages(state.activeSessionId);
        }

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

        sendButton.addEventListener('click', sendCurrentMessage);
        chatInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendCurrentMessage();
            }
        });

        (async function initMessaging() {
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
        })();
    })();
</script>