<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'includes/header.php';

$chat_id = $_GET['chat_id'] ?? '';
?>

<style>
/* WhatsApp-like styling */
.messaging-app {
    height: 100vh;
    background: #f0f2f5;
    display: flex;
    flex-direction: column;
}

.messaging-container {
    flex: 1;
    display: flex;
    overflow: hidden;
}

.sidebar {
    width: 100%;
    max-width: 400px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #e0e0e0;
    transition: transform 0.3s ease;
}

.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #e5ddd5;
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text fill="%23f0f0f0" font-size="20" y="50%">💬</text></svg>');
    background-size: 50px 50px;
}

/* Search Bar */
.search-container {
    padding: 12px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
}

.search-input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input {
    width: 100%;
    padding: 10px 45px 10px 45px;
    border: none;
    border-radius: 25px;
    background: #ffffff;
    outline: none;
    font-size: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.search-input:focus {
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
}

.search-icon {
    position: absolute;
    left: 15px;
    color: #8696a0;
    font-size: 16px;
    z-index: 1;
}

.clear-search {
    position: absolute;
    right: 50px;
    color: #8696a0;
    font-size: 16px;
    cursor: pointer;
    display: none;
}

.new-chat-btn {
    position: absolute;
    right: 15px;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #25d366;
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.new-chat-btn:hover {
    background: #128c7e;
}

/* Conversations List */
.chat-list {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

.conversation-item {
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #f0f0f0;
    position: relative;
}

.conversation-item:hover {
    background-color: #f5f5f5;
}

.conversation-item.active {
    background-color: #e1f5fe;
}

.conversation-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #25d366;
}

.conversation-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #25d366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;
    flex-shrink: 0;
    position: relative;
}

.conversation-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.conversation-name {
    font-weight: 600;
    font-size: 16px;
    color: #111b21;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-last-message {
    color: #8696a0;
    font-size: 14px;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.conversation-last-message .badge {
    font-size: 11px;
    padding: 2px 6px;
    flex-shrink: 0;
}

.conversation-time {
    color: #8696a0;
    font-size: 12px;
    align-self: flex-start;
    margin-top: 2px;
}

/* Chat Header */
.chat-header {
    background: #f0f2f5;
    padding: 12px 16px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    min-height: 60px;
}

.back-button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(0,0,0,0.1);
    border: none;
    color: #54656f;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    margin-right: 12px;
    transition: background-color 0.2s;
}

.back-button:hover {
    background: rgba(0,0,0,0.2);
}

.chat-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #25d366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;
}

.chat-info {
    flex: 1;
}

.chat-info h5 {
    margin: 0;
    font-size: 16px;
    color: #111b21;
    font-weight: 600;
}

.chat-info p {
    margin: 0;
    color: #8696a0;
    font-size: 14px;
}

.chat-actions {
    display: flex;
    gap: 8px;
}

.chat-action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(0,0,0,0.1);
    border: none;
    color: #54656f;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.chat-action-btn:hover {
    background: rgba(0,0,0,0.2);
}

/* Messages Container */
.messages-container {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    scroll-behavior: smooth;
    background: #e5ddd5;
    background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text fill="%23f0f0f0" font-size="20" y="50%">💬</text></svg>');
    background-size: 50px 50px;
}

.message-bubble {
    max-width: 65%;
    margin-bottom: 8px;
    padding: 8px 12px;
    border-radius: 8px;
    position: relative;
    word-wrap: break-word;
    font-size: 14px;
    line-height: 1.4;
    animation: messageSlideIn 0.3s ease-out;
}

.message-text {
    margin-bottom: 4px;
}

.message-sent {
    background: #dcf8c6;
    color: #111b21;
    margin-left: auto;
    border-bottom-right-radius: 4px;
    margin-right: 12px;
    position: relative;
}

.message-sent::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: -8px;
    width: 0;
    height: 0;
    border-left: 8px solid #dcf8c6;
    border-bottom: 8px solid transparent;
}

.message-received {
    background: #ffffff;
    color: #111b21;
    margin-right: auto;
    border-bottom-left-radius: 4px;
    margin-left: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    position: relative;
}

.message-received::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: -8px;
    width: 0;
    height: 0;
    border-right: 8px solid #ffffff;
    border-bottom: 8px solid transparent;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-time {
    font-size: 11px;
    color: #8696a0;
    margin-top: 4px;
    text-align: right;
}

.message-received .message-time {
    text-align: left;
}

.message-status {
    font-size: 11px;
    margin-left: 5px;
    color: #8696a0;
}

/* Message Input Area */
.message-input-area {
    background: #f0f2f5;
    padding: 12px 16px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.message-input-container {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
}

.message-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 10px 50px 10px 16px;
    border-radius: 25px;
    background: #ffffff;
    font-size: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.message-input:focus {
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
}

.emoji-btn {
    position: absolute;
    right: 50px;
    color: #8696a0;
    font-size: 20px;
    cursor: pointer;
    padding: 4px;
}

.attach-btn {
    position: absolute;
    right: 15px;
    color: #8696a0;
    font-size: 18px;
    cursor: pointer;
    padding: 4px;
}

.send-button {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #25d366;
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.send-button:hover {
    background: #128c7e;
}

.send-button:disabled {
    background: #cccccc;
    cursor: not-allowed;
}

/* Empty States */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #8696a0;
    text-align: center;
}

.empty-state i {
    font-size: 72px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h5 {
    margin: 0 0 8px 0;
    color: #41525d;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

/* Mobile Styles */
@media (max-width: 768px) {
    .messaging-app {
        height: 100vh;
    }

    .sidebar {
        position: fixed;
        top: 80px; /* Below header */
        left: 0;
        width: 100%;
        height: calc(100vh - 80px);
        z-index: 1050;
        transform: translateX(0);
        transition: transform 0.3s ease;
    }

    .sidebar.hide {
        transform: translateX(-100%);
    }

    .chat-area {
        width: 100%;
        position: fixed;
        top: 80px; /* Below header */
        left: 0;
        height: calc(100vh - 80px);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }

    .chat-area.show {
        transform: translateX(0);
    }

    .back-button {
        display: flex !important;
    }

    .message-input {
        font-size: 16px;
    }

    .conversation-item {
        padding: 16px;
    }

    .conversation-avatar {
        width: 55px;
        height: 55px;
        font-size: 18px;
    }

    .conversation-name {
        font-size: 17px;
    }

    .conversation-last-message {
        font-size: 15px;
    }

    .chat-header {
        padding: 16px;
    }

    .chat-avatar {
        width: 45px;
        height: 45px;
        font-size: 18px;
    }

    .chat-info h5 {
        font-size: 17px;
    }

    .messages-container {
        padding: 12px;
    }

    .message-bubble {
        max-width: 80%;
        font-size: 15px;
    }

    .message-input-area {
        padding: 16px;
    }
}

/* Mobile: make chat area full-screen and input fixed */
@media (max-width: 768px) {
    .messaging-container { height: 100vh; }

    /* Sidebar becomes overlay */
    .sidebar {
        position: fixed;
        top: 56px; /* below header */
        left: 0;
        width: 100%;
        height: calc(100vh - 56px);
        z-index: 1100;
        background: #fff;
        overflow-y: auto;
        transform: translateX(0);
        transition: transform 0.25s ease;
    }
    .sidebar.hide {
        transform: translateX(-100%);
    }

    /* Chat area fixed and fills remaining space */
    .chat-area {
        position: fixed;
        top: 56px;
        left: 0;
        right: 0;
        bottom: 0;
        height: calc(100vh - 56px);
        display: flex;
        flex-direction: column;
        background: #e5ddd5;
    }

    /* Messages container scrolls between fixed header and input */
    .messages-container {
        flex: 1 1 auto;
        padding: 12px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        margin-top: 0; /* header is fixed above */
    }

    /* Lock page scroll and make messaging area fill screen so header/input stay fixed */
    html, body { height: 100%; overflow: hidden; }


    /* Ensure messages are not hidden behind the input/keyboard (compact on mobile) */
    .messages-container { padding-bottom: calc(64px + env(safe-area-inset-bottom)); }

    .chat-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1200;
        background: #f0f2f5;
        padding: 8px 10px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
    }

    /* Compact input bar: use sticky bottom and smaller paddings so it stays visible */
    .message-input-area {
        position: sticky;
        bottom: env(safe-area-inset-bottom, 0);
        left: 0;
        right: 0;
        z-index: 1200;
        padding: 6px 8px;
        background: rgba(240,242,245,0.98);
        box-shadow: 0 -3px 10px rgba(0,0,0,0.05);
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .message-input-container { flex: 1; }
    .message-input { width: 100%; padding: 8px 36px 8px 12px; border-radius: 20px; font-size:14px; }
    .emoji-btn { right: 58px; font-size:18px; }
    .attach-btn { right: 34px; font-size:18px; }
    .send-button { width: 40px; height: 40px; border-radius: 10px; font-size:16px; }

    /* Bubbles should be narrower and stack nicely */
    .message-bubble { max-width: 85%; }
    .message-sent { margin-left: auto; margin-right: 6px; }
    .message-received { margin-right: auto; margin-left: 6px; }

    /* Improve tap targets in conversation list */
    .conversation-item { padding: 14px 16px; }
    .conversation-avatar { width: 48px; height: 48px; }

    /* Ensure typing indicator and other floats fit */
    .typing-indicator { margin-left: 12px; margin-bottom: 8px; }
}

/* Loading States */
.loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}

/* Typing Indicator */
.typing-indicator {
    display: none;
    padding: 8px 12px;
    background: #ffffff;
    border-radius: 18px;
    margin-bottom: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    margin-left: 12px;
    width: fit-content;
}

.typing-dots {
    display: inline-block;
}

.typing-dots span {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #8696a0;
    animation: typing 1.4s infinite;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.4;
    }
    30% {
        transform: translateY(-10px);
        opacity: 1;
    }
}

/* Floating Action Button */
.new-chat-fab {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #25d366;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: all 0.2s;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.new-chat-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.4);
}

@media (min-width: 769px) {
    .new-chat-fab {
        display: none;
    }
}

/* Move/hide floating FAB when virtual keyboard is open on mobile */
body.keyboard-open .new-chat-fab {
    transform: translateY(-140px) scale(0.95);
    opacity: 0.96;
}

/* In-page notification popup */
.inpage-notification {
    position: fixed;
    right: 20px;
    bottom: 90px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    padding: 12px 16px;
    z-index: 2000;
    width: 300px;
    display: flex;
    gap: 12px;
    align-items: center;
}
.inpage-notification .np-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #25d366;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
.inpage-notification .np-body {
    flex: 1;
}
.inpage-notification .np-title {
    font-weight: 600;
    font-size: 14px;
}
.inpage-notification .np-text {
    font-size: 13px;
    color: #666;
}
</style>
</style>

<style>
/* New message highlight */
.message-new {
    animation: newMessageGlow 3s ease-in-out;
    box-shadow: 0 6px 18px rgba(0,123,255,0.12);
}

@keyframes newMessageGlow {
    0% { background-color: rgba(11,132,255,0.08); }
    50% { background-color: rgba(11,132,255,0.12); }
    100% { background-color: transparent; }
}

.conversation-new {
    animation: convoPulse 4s ease-in-out;
    background-color: rgba(11,132,255,0.06);
}

@keyframes convoPulse {
    0% { background-color: rgba(11,132,255,0.10); }
    100% { background-color: transparent; }
}
</style>
</style>

<main class="messaging-app">
    <!-- Main Container -->
    <div class="messaging-container">
        <!-- Sidebar with conversations -->
        <div class="sidebar" id="sidebar">
            <div class="search-container">
                <div class="search-input-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search conversations..." id="search-input">
                    <i class="fas fa-times clear-search" id="clear-search" onclick="clearSearch()"></i>
                    <button class="new-chat-btn" data-bs-toggle="modal" data-bs-target="#newChatModal">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
            <div class="chat-list" id="conversations-list">
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h5>No conversations yet</h5>
                    <p>Start a new conversation to begin messaging</p>
                </div>
            </div>
        </div>

        <!-- Chat area -->
        <div class="chat-area" id="chat-area">
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h5>Select a conversation</h5>
                <p>Choose a conversation from the sidebar to start messaging</p>
            </div>
        </div>
    </div>

    <!-- Floating Action Button for Mobile -->
    <button class="new-chat-fab d-md-none" onclick="window.location.href='index.php'">
        <i class="fas fa-home"></i>
    </button>
</main>

<!-- footer removed for messaging page -->

<!-- Firebase SDK (compat builds to support namespaced APIs used below) -->
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-auth-compat.js"></script>

<script src="js/firebase-config.js"></script>
<script>
const userId = '<?php echo $_SESSION['user_id']; ?>';
const userName = '<?php echo $_SESSION['user_name']; ?>';
const userEmail = '<?php echo $_SESSION['user_email']; ?>';
const chatId = '<?php echo $chat_id; ?>';
let currentChatId = chatId;
let currentChatUsers = {};
let typingTimeouts = {};
let messageListener = { ref: null, callback: null };
let currentChatData = null; // store current chat metadata for send/receiving logic

// Initialize Firebase auth
document.addEventListener('DOMContentLoaded', function() {
    // Handle mobile initial state
    if (window.innerWidth <= 768) {
        if (chatId) {
            document.getElementById('sidebar').classList.add('hide');
            document.getElementById('chat-area').classList.add('show');
        } else {
            document.getElementById('sidebar').classList.remove('hide');
            document.getElementById('chat-area').classList.remove('show');
        }
    }

    initializeFirebaseAuth().then(() => {
        loadConversations();
        if (chatId) {
            loadChat(chatId);
        }
        setupMessageInput();
        setupSearch();
        // Ask browser for notification permission early so we can show incoming alerts
        requestNotificationPermission();
    });
});

function initializeFirebaseAuth() {
    return new Promise((resolve, reject) => {
        if (typeof firebase === 'undefined') {
            reject(new Error('Firebase SDK not loaded'));
            return;
        }

        const auth = firebase.auth();
        if (auth.currentUser) {
            resolve();
            return;
        }

        auth.signInAnonymously().then(() => {
            // Map Firebase auth UID to our PHP userId so rules can check membership
            const uid = auth.currentUser && auth.currentUser.uid;
            if (uid) {
                firebase.database().ref('sessions/' + uid).set(userId)
                    .catch(err => console.warn('Session mapping failed:', err));
            }
            resolve();
        }).catch(error => {
            console.warn('Anonymous auth failed:', error);
            resolve(); // Continue anyway
        });
    });
}

function setupMessageInput() {
    const input = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');

    if (!input || !sendButton) return;

    input.addEventListener('input', function() {
        sendButton.disabled = !this.value.trim();
        handleTyping();
    });

    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    sendButton.addEventListener('click', sendMessage);

    // Ensure viewport & keyboard handlers are attached once to handle mobile keyboard
    ensureViewportHandlers();
}

// Viewport / keyboard helpers to prevent virtual keyboard from covering messages
let _viewportHandlersSetup = false;
function ensureViewportHandlers() {
    if (_viewportHandlersSetup) return;
    _viewportHandlersSetup = true;

    const container = document.getElementById('messages-container');
    const inputArea = document.querySelector('.message-input-area');
    const input = document.getElementById('message-input');

    function updateForViewport() {
        if (!container || !inputArea) return;

        let extra = 0;
        if (window.visualViewport) {
            const v = window.visualViewport;
            // keyboard height approx = window.innerHeight - visualViewport.height - visualViewport.offsetTop
            const kb = Math.max(0, window.innerHeight - v.height - (v.offsetTop || 0));
            extra = kb;
        }

        const inputHeight = inputArea.offsetHeight || 64;
        // Add some safe padding so last message is visible above input + keyboard
        container.style.paddingBottom = (inputHeight + extra + 16) + 'px';

        // toggle a class so floating buttons can move out of the way
        document.body.classList.toggle('keyboard-open', extra > 50);

        // keep view scrolled to bottom when keyboard opens or when focused
        scrollToBottom();
    }

    // Prefer visualViewport events when available for more accurate keyboard detection
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', updateForViewport);
        window.visualViewport.addEventListener('scroll', updateForViewport);
    }

    // Fallbacks
    window.addEventListener('resize', updateForViewport);
    window.addEventListener('orientationchange', () => setTimeout(updateForViewport, 250));

    if (input) {
        input.addEventListener('focus', () => {
            setTimeout(() => {
                updateForViewport();
                try { document.querySelector('.message-input-area').scrollIntoView({behavior:'smooth', block:'end'}); } catch(e){}
                try { input.scrollIntoView({behavior:'smooth', block:'center'}); } catch(e){}
            }, 80);
        });
        input.addEventListener('blur', () => {
            // Delay to allow virtual keyboard to hide
            setTimeout(updateForViewport, 200);
        });
    }

    // Also run once now to set initial padding
    setTimeout(updateForViewport, 120);
}

// Compute and set messages container height so the chat occupies full screen on mobile
function adjustChatLayout() {
    const container = document.getElementById('messages-container');
    const inputArea = document.querySelector('.message-input-area');
    const header = document.querySelector('.chat-header');
    if (!container) return;

    const vh = window.innerHeight;
    const headerH = header ? header.getBoundingClientRect().height : 0;
    const inputH = inputArea ? inputArea.getBoundingClientRect().height : 56;
    const safeBottom = typeof window.visualViewport !== 'undefined' ? Math.max(0, window.innerHeight - window.visualViewport.height - (window.visualViewport.offsetTop || 0)) : 0;

    const available = Math.max(100, vh - headerH - inputH - safeBottom - 8);
    container.style.height = available + 'px';
    container.style.overflowY = 'auto';
    container.style.webkitOverflowScrolling = 'touch';
}

// Recalculate on viewport resize / orientation change
if (window.visualViewport) {
    window.visualViewport.addEventListener('resize', () => setTimeout(adjustChatLayout, 60));
}
window.addEventListener('resize', () => setTimeout(adjustChatLayout, 60));
window.addEventListener('orientationchange', () => setTimeout(adjustChatLayout, 200));

function setupSearch() {
    const searchInput = document.getElementById('search-input');
    const clearSearchBtn = document.getElementById('clear-search');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query && clearSearchBtn) {
                clearSearchBtn.style.display = 'block';
            } else if (clearSearchBtn) {
                clearSearchBtn.style.display = 'none';
            }
            filterConversations(query);
        });
    }

    // Setup new chat modal
    const userSearchInput = document.getElementById('user-search');
    if (userSearchInput) {
        userSearchInput.addEventListener('input', function() {
            searchUsers(this.value);
        });
    }
}

// Notification helpers
function requestNotificationPermission() {
    if (!('Notification' in window)) return;
    if (Notification.permission === 'default') {
        try {
            Notification.requestPermission().then(permission => {
                console.log('Notification permission:', permission);
            });
        } catch (e) {
            // some browsers may not return a promise
            console.warn('Notification permission request failed', e);
        }
    }
}

function sendBrowserNotification(title, body, tag, onClick) {
    if (!('Notification' in window)) return;
    if (Notification.permission !== 'granted') return;

    const n = new Notification(title, {
        body: body,
        tag: tag,
        renotify: true,
        silent: false
    });
    n.onclick = function(e) {
        e.preventDefault();
        window.focus();
        if (typeof onClick === 'function') onClick();
        n.close();
    };
}

function showInPageNotification(senderName, text, chatId) {
    const existing = document.querySelector('.inpage-notification');
    if (existing) existing.remove();

    const el = document.createElement('div');
    el.className = 'inpage-notification';
    el.innerHTML = `
        <div class="np-avatar">${senderName.split(' ').map(s=>s.charAt(0)).join('').toUpperCase().slice(0,2)}</div>
        <div class="np-body">
            <div class="np-title">${escapeHtml(senderName)}</div>
            <div class="np-text">${escapeHtml(text.slice(0,80))}</div>
        </div>
    `;

    el.addEventListener('click', () => {
        window.focus();
        if (chatId) loadChat(chatId);
        el.remove();
    });

    document.body.appendChild(el);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        el.remove();
    }, 5000);
}

function handleIncomingMessageNotification(msg) {
    // Only notify for messages from others
    if (!msg || msg.sender_id === userId) return;

    const sender = msg.sender_name || msg.sender_id || 'New message';
    const preview = msg.text || '';

    // If user is not viewing this chat or page is hidden, show browser notification
    const shouldShowBrowser = (document.hidden || currentChatId !== msg.chat_id);
    if (shouldShowBrowser) {
        sendBrowserNotification(sender, preview, 'msg-' + msg.id, () => {
            if (msg.chat_id) loadChat(msg.chat_id);
        });
    }

    // Always show small in-page popup so user notices in tab
    showInPageNotification(sender, preview, msg.chat_id);
}

function markConversationNew(chatId) {
    try {
        const item = document.querySelector(`[data-chat-id="${chatId}"]`);
        if (!item) return;
        item.classList.add('conversation-new');
        // remove highlight after 6 seconds or when user opens the chat
        const removeFn = () => item.classList.remove('conversation-new');
        setTimeout(removeFn, 6000);
    } catch (e) {
        console.warn('Mark conversation new error:', e);
    }
}

function clearSearch() {
    const searchInput = document.getElementById('search-input');
    const clearSearchBtn = document.getElementById('clear-search');
    
    if (searchInput) searchInput.value = '';
    if (clearSearchBtn) clearSearchBtn.style.display = 'none';
    filterConversations('');
}

function openNewChatModal() {
    // This function is called from the mobile floating button
    const modal = new bootstrap.Modal(document.getElementById('newChatModal'));
    modal.show();
}

function searchInChat() {
    // Implement search in current chat
    alert('Search in chat feature coming soon!');
}

function showChatOptions() {
    // Show chat options menu
    const options = [
        { icon: 'fas fa-user', text: 'View Profile', action: 'viewProfile' },
        { icon: 'fas fa-volume-up', text: 'Notifications', action: 'toggleNotifications' },
        { icon: 'fas fa-trash', text: 'Delete Chat', action: 'deleteChat' },
        { icon: 'fas fa-ban', text: 'Block User', action: 'blockUser' }
    ];

    let optionsHtml = '<div class="list-group">';
    options.forEach(option => {
        optionsHtml += `
            <button class="list-group-item list-group-item-action d-flex align-items-center" onclick="${option.action}()">
                <i class="${option.icon} me-3"></i>
                <span>${option.text}</span>
            </button>
        `;
    });
    optionsHtml += '</div>';

    // Create modal for options
    const modalHtml = `
        <div class="modal fade" id="chatOptionsModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Chat Options</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        ${optionsHtml}
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('chatOptionsModal');
    if (existingModal) existingModal.remove();

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('chatOptionsModal'));
    modal.show();
}

function deleteChat() {
    if (confirm('Are you sure you want to delete this conversation? This action cannot be undone.')) {
        deleteConversation();
    }
}

function viewProfile() {
    // Implement view profile
    alert('View profile feature coming soon!');
}

function toggleNotifications() {
    // Implement toggle notifications
    alert('Notification settings coming soon!');
}

function blockUser() {
    // Implement block user
    alert('Block user feature coming soon!');
}

function loadConversations() {
    const convoDiv = document.getElementById('conversations-list');
    convoDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading conversations...</span></div></div>';

    firebase.database().ref('chats').orderByChild('last_message_time').once('value').then(snapshot => {
        const conversations = [];
        snapshot.forEach(childSnapshot => {
            const chat = childSnapshot.val();
            const chatKey = childSnapshot.key;

            // Check if current user is part of this chat
            if ((chat.buyer_id === userId || chat.seller_id === userId) ||
                (chat.user1_id === userId || chat.user2_id === userId)) {
                chat.id = chatKey;
                conversations.push(chat);
            }
        });

        // Sort by last message time (most recent first)
        conversations.sort((a, b) => (b.last_message_time || 0) - (a.last_message_time || 0));

        // Load user names for conversations
        loadUserNamesForConversations(conversations);
    }).catch(error => {
        console.error('Error loading conversations:', error);
        convoDiv.innerHTML = '<div class="text-center py-4 text-muted">Error loading conversations</div>';
    });
}

function loadUserNamesForConversations(conversations) {
    if (conversations.length === 0) {
        displayConversations([]);
        return;
    }

    // Get all unique user IDs we need to look up
    const userIds = new Set();
    conversations.forEach(conv => {
        const otherUserId = getOtherUserId(conv);
        if (otherUserId) {
            userIds.add(otherUserId);
        }
    });

    // Fetch user data
    fetch('get_user_data.php?user_ids=' + Array.from(userIds).join(','))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add user names to conversations
                conversations.forEach(conv => {
                    const otherUserId = getOtherUserId(conv);
                    if (otherUserId && data.users[otherUserId]) {
                        conv.other_user_name = data.users[otherUserId].name;
                        conv.other_user_email = data.users[otherUserId].email;
                    }
                });
            }
            displayConversations(conversations);
        })
        .catch(error => {
            console.error('Error loading user names:', error);
            displayConversations(conversations);
        });
}

function displayConversations(conversations) {
    const convoDiv = document.getElementById('conversations-list');

    if (conversations.length === 0) {
        convoDiv.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h5>No conversations yet</h5>
                <p>Start a new conversation to begin messaging</p>
            </div>
        `;
        return;
    }

    let html = '';
    conversations.forEach(conv => {
        const otherUserId = getOtherUserId(conv);
        const otherUserName = conv.other_user_name || getOtherUserName(conv);
        const isActive = conv.id === currentChatId;
        const lastMessage = conv.last_message || 'No messages yet';
        const lastMessageTime = conv.last_message_time ? formatMessageTime(conv.last_message_time) : '';
        // Unread count for current user (support unread_counts map)
        const unread = (conv.unread_counts && conv.unread_counts[userId]) || conv.unread_count || 0;

        html += `
            <div class="conversation-item ${isActive ? 'active' : ''}" onclick="selectConversation('${conv.id}')" data-chat-id="${conv.id}">
                <div class="conversation-avatar">${getInitials(otherUserName)}</div>
                <div class="conversation-info">
                    <div class="conversation-name">${otherUserName}</div>
                    <div class="conversation-last-message">
                        ${lastMessage}
                        ${unread && unread > 0 ? `<span class="badge bg-success rounded-pill ms-2">${unread}</span>` : ''}
                    </div>
                    <div class="conversation-time">${lastMessageTime}</div>
                </div>
            </div>
        `;
    });

    convoDiv.innerHTML = html;
}

function getOtherUserId(chat) {
    if (chat.buyer_id && chat.seller_id) {
        return chat.buyer_id === userId ? chat.seller_id : chat.buyer_id;
    }
    if (chat.user1_id && chat.user2_id) {
        return chat.user1_id === userId ? chat.user2_id : chat.user1_id;
    }
    return null;
}

function getOtherUserName(chat) {
    // Use the loaded user name if available
    if (chat.other_user_name) {
        return chat.other_user_name;
    }

    // Try to get from chat data first
    if (chat.buyer_id === userId && chat.seller_name) return chat.seller_name;
    if (chat.seller_id === userId && chat.buyer_name) return chat.buyer_name;
    if (chat.user1_id === userId && chat.user2_name) return chat.user2_name;
    if (chat.user2_id === userId && chat.user1_name) return chat.user1_name;
    if (chat.other_user_email) return chat.other_user_email;

    // Try to get from user database if we have an ID
    const otherUserId = getOtherUserId(chat);
    if (otherUserId) {
        // Try to load the name synchronously if possible
        fetch('get_user_data.php?user_ids=' + otherUserId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.users[otherUserId]) {
                    chat.other_user_name = data.users[otherUserId].name;
                    // Update the display if this chat is currently active
                    const nameElement = document.getElementById('chat-name');
                    if (nameElement && nameElement.textContent === 'Loading...') {
                        nameElement.textContent = chat.other_user_name;
                        const avatarElement = document.getElementById('chat-avatar');
                        if (avatarElement) {
                            avatarElement.textContent = getInitials(chat.other_user_name);
                        }
                    }
                    // Also update in conversation list
                    updateConversationDisplay(chat.id, chat.other_user_name);
                }
            })
            .catch(error => {
                console.error('Error loading user name:', error);
            });
        return 'Loading...';
    }

    // Fallback to a more descriptive name
    return 'Chat Partner';
}

function updateConversationDisplay(chatId, userName) {
    const conversationItem = document.querySelector(`[data-chat-id="${chatId}"]`);
    if (conversationItem) {
        const nameElement = conversationItem.querySelector('.conversation-name');
        const avatarElement = conversationItem.querySelector('.conversation-avatar');
        if (nameElement && nameElement.textContent === 'Loading...' || nameElement.textContent === 'User' || nameElement.textContent === 'Chat Partner') {
            nameElement.textContent = userName;
        }
        if (avatarElement && (avatarElement.textContent === '?' || avatarElement.textContent === 'U' || avatarElement.textContent === 'C')) {
            avatarElement.textContent = getInitials(userName);
        }
    }
}

function getInitials(name) {
    if (!name || name === 'User' || name === 'Buyer' || name === 'Seller' || name === 'Chat Partner' || name === 'Loading...') {
        return '?';
    }
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}

function selectConversation(chatId) {
    currentChatId = chatId;

    // On mobile, hide sidebar and show chat
    if (window.innerWidth <= 768) {
        document.getElementById('sidebar').classList.add('hide');
        document.getElementById('chat-area').classList.add('show');
    }

    loadChat(chatId);
}

function showConversationList() {
    document.getElementById('sidebar').classList.remove('hide');
    document.getElementById('chat-area').classList.remove('show');
    currentChatId = null;
}

function showEmptyState(message) {
    const chatArea = document.getElementById('chat-area');
    chatArea.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <h5>${message}</h5>
            <p>Please try again or contact support</p>
        </div>
    `;
}

function loadChat(chatId) {
    firebase.database().ref('chats/' + chatId).once('value').then(snapshot => {
        const chat = snapshot.val();
        if (!chat) {
            showEmptyState('Chat not found');
            return;
        }

        // Save current chat metadata
        currentChatData = chat;

        // Populate chat area
        const chatArea = document.getElementById('chat-area');
        chatArea.innerHTML = `
            <div class="chat-header">
                <button class="back-button d-md-none" onclick="showConversationList()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="chat-avatar" id="chat-avatar">?</div>
                <div class="chat-info">
                    <h5 id="chat-name">Loading...</h5>
                    <p id="chat-status">Online</p>
                </div>
                <div class="chat-actions">
                    <button class="chat-action-btn" title="Search in chat" onclick="searchInChat()">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="chat-action-btn" title="More options" onclick="showChatOptions()">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </div>
            <div class="messages-container" id="messages-container">
                <div class="typing-indicator" id="typing-indicator">
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
            <div class="message-input-area">
                <div class="message-input-container">
                    <input type="text" class="message-input" id="message-input" placeholder="Type a message...">
                    <i class="fas fa-smile emoji-btn" title="Emoji"></i>
                    <i class="fas fa-paperclip attach-btn" title="Attach file"></i>
                </div>
                <button class="send-button" id="send-button" disabled>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        `;

        // Update header
        updateChatHeader(chat);

        // Ensure layout fits the viewport (mobile): size messages area so input stays visible
        try { adjustChatLayout(); } catch (e) { console.warn('adjustChatLayout call failed', e); }

        // Load messages
        loadMessages(chatId);

        // Mark messages as read
        markMessagesAsRead(chatId);

        // Reset unread count for this user in chat metadata
        try {
            const unreadPath = 'chats/' + chatId + '/unread_counts/' + userId;
            firebase.database().ref(unreadPath).set(0).catch(err => console.warn('Failed to reset unread count:', err));
        } catch (e) {
            console.warn('Reset unread count error:', e);
        }

        // Setup input after DOM is ready
        setTimeout(() => {
            setupMessageInput();
        }, 100);
    }).catch(error => {
        console.error('Error loading chat:', error);
        showEmptyState('Error loading chat');
    });
}

function updateChatHeader(chat) {
    const nameElement = document.getElementById('chat-name');
    const statusElement = document.getElementById('chat-status');
    const avatarElement = document.getElementById('chat-avatar');
    const backButton = document.querySelector('.back-button');

    if (nameElement && statusElement && avatarElement) {
        const otherUserName = getOtherUserName(chat);
        nameElement.textContent = otherUserName;
        statusElement.textContent = 'Online'; // Could be enhanced with real online status
        avatarElement.textContent = getInitials(otherUserName);
    }

    // Show back button on mobile
    if (backButton) {
        if (window.innerWidth <= 768) {
            backButton.classList.remove('d-none');
        } else {
            backButton.classList.add('d-none');
        }
    }
}

function loadMessages(chatId) {
    // Show loading state
    const container = document.getElementById('messages-container');
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    // Remove previous listener (if attached to a query)
    if (messageListener && messageListener.ref && messageListener.callback) {
        try {
            messageListener.ref.off('child_added', messageListener.callback);
        } catch (e) {
            console.warn('Failed to remove previous message listener:', e);
        }
        messageListener.ref = null;
        messageListener.callback = null;
    }

    // Load last 50 messages initially for better performance
    firebase.database().ref('messages')
        .orderByChild('chat_id')
        .equalTo(chatId)
        .limitToLast(50)
        .once('value').then(snapshot => {
            const messages = [];

            snapshot.forEach(childSnapshot => {
                const m = childSnapshot.val();
                m.id = childSnapshot.key;
                messages.push(m);
            });

            // Sort messages by timestamp
            messages.sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));

            displayMessages(messages);

            // Set up real-time listener for new messages only, using the same query ref
            const q = firebase.database().ref('messages').orderByChild('chat_id').equalTo(chatId);
            const cb = (snapshot) => {
                const newMessage = snapshot.val();
                newMessage.id = snapshot.key;

                // Skip if message already rendered (use message id)
                const container = document.getElementById('messages-container');
                if (container && container.querySelector(`[data-message-id="${newMessage.id}"]`)) {
                    return;
                }

                // Append and mark as new so we can highlight it
                appendMessage(newMessage, true);

                // If not viewing this chat, update conversation item badge and last message
                if (newMessage.chat_id && newMessage.chat_id !== currentChatId) {
                    // Update conversation preview text/time
                    const convEl = document.querySelector(`[data-chat-id="${newMessage.chat_id}"]`);
                    if (convEl) {
                        const lastMsgEl = convEl.querySelector('.conversation-last-message');
                        if (lastMsgEl) lastMsgEl.childNodes[0].nodeValue = newMessage.text;
                        const timeEl = convEl.querySelector('.conversation-time');
                        if (timeEl) timeEl.textContent = formatMessageTime(newMessage.timestamp || Date.now());

                        // Update or create badge
                        let badge = convEl.querySelector('.conversation-last-message .badge');
                        if (badge) {
                            const n = parseInt(badge.textContent || '0', 10) || 0;
                            badge.textContent = n + 1;
                        } else {
                            const span = document.createElement('span');
                            span.className = 'badge bg-success rounded-pill ms-2';
                            span.textContent = '1';
                            if (lastMsgEl) lastMsgEl.appendChild(span);
                        }
                    }
                }

                // Show notifications for incoming messages from others
                if (newMessage.sender_id && newMessage.sender_id !== userId) {
                    try {
                        handleIncomingMessageNotification(newMessage);
                    } catch (e) {
                        console.warn('Notification handler error:', e);
                    }
                }
            };

            q.on('child_added', cb);
            // Listen for status updates / edits on existing messages
            q.on('child_changed', (snapshot) => {
                const updated = snapshot.val();
                updated.id = snapshot.key;
                // Update DOM status icon
                updateMessageStatus(updated);
            });
            messageListener.ref = q;
            messageListener.callback = cb;
        }).catch(error => {
            console.error('Error loading messages:', error);
            container.innerHTML = '<div class="text-center py-4 text-muted">Error loading messages</div>';
        });
}

function displayMessages(messages) {
    const container = document.getElementById('messages-container');

    if (messages.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h5>No messages yet</h5>
                <p>Start the conversation!</p>
            </div>
        `;
        return;
    }

    let html = '';
    messages.forEach(msg => {
        html += createMessageHTML(msg);
    });

    container.innerHTML = html;
    scrollToBottom();
}

function appendMessage(message, isNew = false) {
    const container = document.getElementById('messages-container');
    const messageHTML = createMessageHTML(message);
    container.insertAdjacentHTML('beforeend', messageHTML);

    // Find the inserted element
    const el = container.querySelector(`[data-message-id="${message.id || ''}"]`);
    if (el && isNew) {
        el.classList.add('message-new');
        // Remove highlight after a few seconds but keep status icons
        setTimeout(() => {
            if (el) el.classList.remove('message-new');
        }, 4000);
    }

    scrollToBottom();
}

function scrollToBottom() {
    const container = document.getElementById('messages-container');
    if (container) {
        setTimeout(() => {
            try {
                const items = container.querySelectorAll('[data-message-id]');
                if (items && items.length) {
                    const last = items[items.length - 1];
                    last.scrollIntoView({ behavior: 'smooth', block: 'end' });
                } else {
                    container.scrollTop = container.scrollHeight;
                }
            } catch (e) {
                container.scrollTop = container.scrollHeight;
            }
        }, 150);
    }
}

function createMessageHTML(msg) {
    const isSender = msg.sender_id === userId;
    const timestamp = formatMessageTime(msg.timestamp);
    const status = isSender ? getStatusIcon(msg.status) : '';
    if (isSender) {
        return `
            <div class="message-bubble message-sent" data-message-id="${msg.id || ''}">
                <div class="message-text">${escapeHtml(msg.text)}</div>
                <div class="message-time">${timestamp} ${status}</div>
            </div>
        `;
    } else {
        return `
            <div class="message-bubble message-received" data-message-id="${msg.id || ''}">
                <div class="message-text">${escapeHtml(msg.text)}</div>
                <div class="message-time">${timestamp}</div>
            </div>
        `;
    }
}

function getStatusIcon(status) {
    // status: sent, delivered, read
    if (!status || status === 'sent') {
        return '<i class="fas fa-check message-status" title="Sent"></i>';
    }
    if (status === 'delivered') {
        return '<i class="fas fa-check-double message-status" title="Delivered"></i>';
    }
    if (status === 'read') {
        return '<i class="fas fa-check-double message-status" title="Read" style="color:#0b84ff"></i>';
    }
    return '';
}

function updateMessageStatus(msg) {
    if (!msg || !msg.id) return;
    const el = document.querySelector(`[data-message-id="${msg.id}"]`);
    if (!el) return;
    const timeEl = el.querySelector('.message-time');
    const statusEl = el.querySelector('.message-status');

    // Update status icon
    if (statusEl) {
        statusEl.outerHTML = getStatusIcon(msg.status) || '';
    } else if (msg.sender_id === userId) {
        // If no status element exists but this is a sent message, append it
        if (timeEl) {
            timeEl.insertAdjacentHTML('beforeend', ' ' + getStatusIcon(msg.status));
        }
    }
}

function formatMessageTime(timestamp) {
    if (!timestamp) return '';

    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;

    if (diff < 60000) { // Less than 1 minute
        return 'Just now';
    } else if (diff < 3600000) { // Less than 1 hour
        const minutes = Math.floor(diff / 60000);
        return `${minutes} min ago`;
    } else if (diff < 86400000) { // Less than 1 day
        return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    } else if (diff < 604800000) { // Less than 1 week
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        return days[date.getDay()];
    } else {
        return date.toLocaleDateString();
    }
}

function formatMessageTime(timestamp) {
    if (!timestamp) return '';

    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;

    if (diff < 60000) { // Less than 1 minute
        return 'Just now';
    } else if (diff < 3600000) { // Less than 1 hour
        const minutes = Math.floor(diff / 60000);
        return `${minutes} min ago`;
    } else if (diff < 86400000) { // Less than 1 day
        return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    } else if (diff < 604800000) { // Less than 1 week
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        return days[date.getDay()];
    } else {
        return date.toLocaleDateString();
    }
}

function sendMessage() {
    if (!currentChatId) {
        alert('Please select a conversation first');
        return;
    }

    const input = document.getElementById('message-input');
    const messageText = input.value.trim();
    if (!messageText) {
        return;
    }

    // Clear input immediately
    input.value = '';

    // Send message
    firebase.database().ref('messages').push({
        chat_id: currentChatId,
        sender_id: userId,
        sender_name: userName,
        text: messageText,
        timestamp: Date.now(),
        status: 'sent'
    }).then(() => {
        // Update chat's last message
        firebase.database().ref('chats/' + currentChatId).update({
            last_message: messageText,
            last_message_time: Date.now()
        });

        // Increment unread count for the other participant (if known)
        try {
            const otherId = getOtherUserId(currentChatData || {});
            if (otherId && otherId !== userId) {
                const unreadRef = firebase.database().ref('chats/' + currentChatId + '/unread_counts/' + otherId);
                unreadRef.transaction(count => {
                    return (count || 0) + 1;
                }).catch(err => console.warn('Unread increment failed:', err));
            }
        } catch (e) {
            console.warn('Unread increment exception:', e);
        }

        // Reload conversations to update last message and badges
        loadConversations();
    }).catch(error => {
        console.error('Error sending message:', error);
        alert('Error sending message: ' + error.message);
        // Restore message if sending failed
        input.value = messageText;
    });
}

function handleTyping() {
    if (!currentChatId) return;

    // Send typing indicator
    firebase.database().ref('typing/' + currentChatId + '/' + userId).set({
        timestamp: Date.now()
    });

    // Clear typing after 3 seconds
    clearTimeout(typingTimeouts[currentChatId]);
    typingTimeouts[currentChatId] = setTimeout(() => {
        firebase.database().ref('typing/' + currentChatId + '/' + userId).remove();
    }, 3000);
}

function markMessagesAsRead(chatId) {
    // Mark messages as read (could be enhanced)
    firebase.database().ref('messages')
        .orderByChild('chat_id').equalTo(chatId)
        .once('value').then(snapshot => {
            snapshot.forEach(childSnapshot => {
                const msg = childSnapshot.val();
                if (msg.sender_id !== userId && msg.status !== 'read') {
                    childSnapshot.ref.update({status: 'read'});
                }
            });
        });
}

function filterConversations(searchTerm) {
    const items = document.querySelectorAll('.conversation-item');
    const term = searchTerm.toLowerCase();

    items.forEach(item => {
        const name = item.querySelector('.conversation-name').textContent.toLowerCase();
        const message = item.querySelector('.conversation-last-message').textContent.toLowerCase();

        if (name.includes(term) || message.includes(term)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function openNewChatModal() {
    const modal = new bootstrap.Modal(document.getElementById('newChatModal'));
    modal.show();

    // Clear previous search
    document.getElementById('user-search').value = '';
    document.getElementById('user-search-results').innerHTML = '';

    // Focus on search input
    setTimeout(() => {
        document.getElementById('user-search').focus();
    }, 500);
}

// User search functionality (attach only when element exists)
const __userSearchEl = document.getElementById('user-search');
if (__userSearchEl) {
    __userSearchEl.addEventListener('input', function() {
        const query = this.value.trim();
        const resultsDiv = document.getElementById('user-search-results');

        if (!resultsDiv) return;

        if (query.length < 2) {
            resultsDiv.innerHTML = '';
            return;
        }

        // Show loading
        resultsDiv.innerHTML = '<div class="text-center"><small>Searching...</small></div>';

        // Search users
        fetch(`search_users.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.users);
                } else {
                    resultsDiv.innerHTML = '<div class="text-center text-danger"><small>Error searching users</small></div>';
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                resultsDiv.innerHTML = '<div class="text-center text-danger"><small>Error searching users</small></div>';
            });
    });
}

function displaySearchResults(users) {
    const resultsDiv = document.getElementById('user-search-results');

    if (users.length === 0) {
        resultsDiv.innerHTML = '<div class="text-center text-muted"><small>No users found</small></div>';
        return;
    }

    let html = '';
    users.forEach(user => {
        html += `
            <div class="user-search-item" onclick="startConversation('${user.id}', '${user.name}', '${user.email}')">
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-3">
                        ${user.name.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${user.name}</div>
                        <small class="text-muted">${user.email}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">${user.coins} coins</small>
                    </div>
                </div>
            </div>
        `;
    });

    resultsDiv.innerHTML = html;
}

function startConversation(userId, userName, userEmail) {
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('newChatModal'));
    modal.hide();

    // Check if conversation already exists
    const existingChat = Object.keys(currentChatUsers).find(chatId => {
        return currentChatUsers[chatId].includes(userId);
    });

    if (existingChat) {
        // Load existing conversation
        loadChat(existingChat);
        return;
    }

    // Create new conversation
    messageUser(userId, userName, userEmail);
}

function messageUser(userId, userName, userEmail) {
    // Create new chat
    const chatRef = firebase.database().ref('chats').push();
    chatRef.set({
        user1_id: '<?php echo $_SESSION['user_id']; ?>',
        user2_id: userId,
        user1_name: userNameFromSession(),
        user2_name: userName,
        user1_email: '<?php echo $_SESSION['user_email']; ?>',
        user2_email: userEmail,
        created_at: Date.now(),
        last_message: '',
        last_message_time: Date.now(),
        message_count: 0
    }).then(() => {
        // Redirect to the new chat
        window.location.href = 'messages.php?chat_id=' + chatRef.key;
    }).catch(error => {
        console.error('Error creating chat:', error);
        alert('Error starting conversation: ' + error.message);
    });
}

function userNameFromSession() {
    return '<?php echo addslashes($_SESSION['user_name']); ?>';
}

function deleteConversation() {
    if (!currentChatId) {
        alert('No chat selected');
        return;
    }
    if (!confirm('Delete this chat and all its messages?')) return;

    const db = firebase.database();
    const updates = {};

    // Remove all messages for this chat
    db.ref('messages').orderByChild('chat_id').equalTo(currentChatId).once('value')
        .then(snapshot => {
            snapshot.forEach(child => {
                updates['/messages/' + child.key] = null;
            });
            // Remove chat and typing indicator
            updates['/chats/' + currentChatId] = null;
            updates['/typing/' + currentChatId] = null;
            return db.ref().update(updates);
        })
        .then(() => {
            // Redirect to list view
            window.location.href = 'messages.php';
        })
        .catch(error => {
            console.error('Error deleting chat:', error);
            alert('Error deleting chat: ' + error.message);
        });
}

function scrollToBottom() {
    const container = document.getElementById('messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showEmptyState(message) {
    const chatArea = document.getElementById('chat-area');
    chatArea.innerHTML = `
        <div class="empty-state">
            <i class="fas fa-comments"></i>
            <h5>${message}</h5>
        </div>
    `;
}

// Handle window resize for mobile responsiveness
window.addEventListener('resize', function() {
    // Could add mobile-specific logic here
});
</script>

<!-- New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>New Conversation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="user-search" placeholder="Search users by name...">
                    </div>
                </div>
                <div id="user-search-results" style="max-height: 300px; overflow-y: auto;">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p>Start typing to search for users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
