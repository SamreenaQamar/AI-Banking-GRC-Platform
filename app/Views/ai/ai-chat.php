<?php
/**
 * AI Chat View
 * 
 * @var string $title
 * @var array $history
 * @var array $use_cases
 */
?>

<?php $page_title = 'AI Chat'; ?>
<?php $active_page = 'ai'; ?>

<div class="ai-chat-container">
    <div class="row g-4">
        <!-- Chat Sidebar -->
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-body p-0">
                    <div class="chat-sidebar-header p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0"><i class="fas fa-robot text-primary me-2"></i> AI Assistant</h6>
                                <small class="text-muted">Powered by GPT-4</small>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" id="newChatBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="p-2 border-bottom">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control form-control-sm" id="chatSearch" placeholder="Search conversations...">
                        </div>
                    </div>

                    <!-- Conversation History -->
                    <div class="chat-history-list p-2" style="max-height: 400px; overflow-y: auto;">
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $chat): ?>
                                <div class="chat-history-item <?php echo $chat->is_active ? 'active' : ''; ?>" data-id="<?php echo $chat->id; ?>">
                                    <div class="chat-history-content">
                                        <div class="chat-title"><?php echo htmlspecialchars($chat->title ?? 'New Chat'); ?></div>
                                        <div class="chat-preview"><?php echo htmlspecialchars(substr($chat->last_message ?? '', 0, 40)) . '...'; ?></div>
                                        <div class="chat-time"><?php echo timeAgo($chat->updated_at); ?></div>
                                    </div>
                                    <button class="btn btn-sm btn-link text-danger delete-chat" data-id="<?php echo $chat->id; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-comment-dots fa-2x mb-2"></i>
                                <p>No conversations yet</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="chat-sidebar-footer p-3 border-top">
                        <button class="btn btn-primary w-100" id="newChatBtn2">
                            <i class="fas fa-plus me-2"></i> New Chat
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Main -->
        <div class="col-xl-9 col-lg-8">
            <div class="card">
                <div class="card-body p-0">
                    <!-- Chat Header -->
                    <div class="chat-header p-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><i class="fas fa-robot text-primary me-2"></i> AI Assistant</h6>
                            <small class="text-muted">Online</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" id="clearChatBtn">
                                <i class="fas fa-eraser"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" id="exportChatBtn">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div class="chat-messages" id="chatMessages" style="height: 450px; overflow-y: auto; padding: 20px; background: #F8FAFC;">
                        <div class="message-item ai-message">
                            <div class="message-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="message-content">
                                <div class="message-bubble">
                                    <p>Hello! I'm your AI Compliance Assistant. I can help you with:</p>
                                    <ul>
                                        <li>Analyzing SBP circulars</li>
                                        <li>Identifying compliance gaps</li>
                                        <li>Generating policy documents</li>
                                        <li>Assessing risks</li>
                                        <li>Recommending mitigation strategies</li>
                                    </ul>
                                    <p>How can I assist you today?</p>
                                </div>
                                <span class="message-time">Just now</span>
                            </div>
                        </div>
                    </div>

                    <!-- Suggested Questions -->
                    <div class="suggested-questions p-2 border-top">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="suggestion-badge" data-question="Analyze the latest SBP circular on AML regulations">📋 Analyze SBP circular</span>
                            <span class="suggestion-badge" data-question="Generate a compliance report for this quarter">📊 Generate compliance report</span>
                            <span class="suggestion-badge" data-question="Identify risks in our current operations">⚠️ Identify risks</span>
                            <span class="suggestion-badge" data-question="Draft an information security policy">📄 Draft policy</span>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="chat-input-area p-3 border-top">
                        <form id="chatForm" class="d-flex gap-2">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                            <input type="text" class="form-control" id="chatInput" 
                                   placeholder="Type your message..." required autofocus>
                            <button type="submit" class="btn btn-primary" id="sendBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="voiceBtn">
                                <i class="fas fa-microphone"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ai-chat-container {
    padding: 0;
}

.chat-sidebar-header {
    background: linear-gradient(135deg, #0B3D91, #2563EB);
    color: #fff;
    border-radius: 12px 12px 0 0;
}

.chat-history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 2px;
}

.chat-history-item:hover {
    background: #F1F5F9;
}

.chat-history-item.active {
    background: #DBEAFE;
}

.chat-history-content {
    flex: 1;
    min-width: 0;
}

.chat-title {
    font-weight: 500;
    color: #1E293B;
    font-size: 14px;
}

.chat-preview {
    font-size: 13px;
    color: #64748B;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-time {
    font-size: 11px;
    color: #94A3B8;
}

.chat-messages {
    scroll-behavior: smooth;
}

.message-item {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}

.message-item.user-message {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 16px;
}

.message-item.ai-message .message-avatar {
    background: #DBEAFE;
    color: #2563EB;
}

.message-item.user-message .message-avatar {
    background: #D1FAE5;
    color: #10B981;
}

.message-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 12px;
    background: #FFFFFF;
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
}

.message-item.user-message .message-bubble {
    background: #2563EB;
    color: #FFFFFF;
}

.message-bubble p {
    margin: 0 0 8px;
}

.message-bubble p:last-child {
    margin-bottom: 0;
}

.message-bubble ul {
    margin: 8px 0;
    padding-left: 20px;
}

.message-bubble code {
    background: #F1F5F9;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 13px;
}

.message-bubble pre {
    background: #1E293B;
    color: #F1F5F9;
    padding: 12px;
    border-radius: 8px;
    overflow-x: auto;
    font-size: 13px;
}

.message-time {
    font-size: 11px;
    color: #94A3B8;
    display: block;
    margin-top: 4px;
}

.message-item.user-message .message-time {
    text-align: right;
}

.suggested-questions {
    background: #FFFFFF;
}

.suggestion-badge {
    padding: 4px 12px;
    border-radius: 20px;
    background: #F1F5F9;
    color: #64748B;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.suggestion-badge:hover {
    background: #DBEAFE;
    color: #2563EB;
}

.typing-indicator {
    display: flex;
    gap: 4px;
    padding: 8px 0;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #94A3B8;
    animation: typing 1.4s infinite both;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
    40% { transform: scale(1); opacity: 1; }
}

@media (max-width: 992px) {
    .chat-messages {
        height: 350px !important;
    }
    
    .message-bubble {
        max-width: 85%;
    }
}
</style>

<script>
$(document).ready(function() {
    // Chat form submission
    $('#chatForm').on('submit', function(e) {
        e.preventDefault();
        
        const input = $('#chatInput');
        const message = input.val().trim();
        
        if (!message) return;
        
        // Add user message
        addMessage('user', message);
        input.val('');
        
        // Show typing indicator
        showTypingIndicator();
        
        // Send to AI
        const csrfToken = $('input[name="csrf_token"]').val();
        
        $.ajax({
            url: '/api/ai/chat',
            method: 'POST',
            data: {
                _csrf: csrfToken,
                message: message,
                context: 'general'
            },
            success: function(response) {
                removeTypingIndicator();
                
                if (response.success) {
                    addMessage('ai', response.data.response);
                } else {
                    addMessage('ai', 'Sorry, I encountered an error. Please try again.');
                }
            },
            error: function() {
                removeTypingIndicator();
                addMessage('ai', 'Sorry, I encountered an error. Please try again.');
            }
        });
    });
    
    function addMessage(type, text) {
        const messages = $('#chatMessages');
        const messageHtml = `
            <div class="message-item ${type}-message">
                <div class="message-avatar">
                    <i class="fas fa-${type === 'ai' ? 'robot' : 'user'}"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        ${text.replace(/\n/g, '<br>')}
                    </div>
                    <span class="message-time">Just now</span>
                </div>
            </div>
        `;
        messages.append(messageHtml);
        messages.scrollTop(messages[0].scrollHeight);
    }
    
    function showTypingIndicator() {
        const messages = $('#chatMessages');
        const indicator = `
            <div class="message-item ai-message" id="typingIndicator">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        <div class="typing-indicator">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        messages.append(indicator);
        messages.scrollTop(messages[0].scrollHeight);
    }
    
    function removeTypingIndicator() {
        $('#typingIndicator').remove();
    }
    
    // Suggestion click
    $('.suggestion-badge').on('click', function() {
        $('#chatInput').val($(this).data('question'));
        $('#chatForm').submit();
    });
    
    // New chat
    $('#newChatBtn, #newChatBtn2').on('click', function() {
        $('#chatMessages').empty();
        addMessage('ai', 'Hello! I\'m your AI Compliance Assistant. How can I help you today?');
    });
    
    // Clear chat
    $('#clearChatBtn').on('click', function() {
        if (confirm('Clear this conversation?')) {
            $('#chatMessages').empty();
            addMessage('ai', 'Conversation cleared. How can I help you?');
        }
    });
    
    // Enter key support
    $('#chatInput').on('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#chatForm').submit();
        }
    });
    
    // Search conversations
    $('#chatSearch').on('keyup', function() {
        const query = $(this).val().toLowerCase();
        $('.chat-history-item').each(function() {
            const title = $(this).find('.chat-title').text().toLowerCase();
            $(this).toggle(title.includes(query));
        });
    });
});

function timeAgo(date) {
    const diff = Math.floor((Date.now() - new Date(date)) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}
</script>