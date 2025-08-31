<?php
require_once 'config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $message = trim($_POST['message'] ?? '');
    if (!empty($message)) {
        $sql = "INSERT INTO chat_messages (user_id, message) VALUES (?, ?)";
        $db->insert($sql, [$currentUser['id'], $message]);
        
        // Return JSON response for AJAX
        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

// Get recent messages
$recentMessages = $db->select("
    SELECT cm.*, u.username, u.full_name, u.role 
    FROM chat_messages cm 
    JOIN users u ON cm.user_id = u.id 
    ORDER BY cm.created_at DESC 
    LIMIT 50
");

// Reverse to show oldest first
$recentMessages = array_reverse($recentMessages);

// Get online users (simulated)
$onlineUsers = [
    ['id' => 1, 'name' => 'Emergency Manager', 'role' => 'emergency_manager', 'status' => 'online'],
    ['id' => 2, 'name' => 'Field Worker 1', 'role' => 'field_worker', 'status' => 'online'],
    ['id' => 3, 'name' => 'Hospital Staff', 'role' => 'hospital_staff', 'status' => 'away'],
    ['id' => 4, 'name' => 'Pharma Company', 'role' => 'pharma_company', 'status' => 'online'],
    ['id' => 5, 'name' => 'Field Worker 2', 'role' => 'field_worker', 'status' => 'offline']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Chat - PHELS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .chat-container {
            height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .message {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 1rem;
            max-width: 80%;
            word-wrap: break-word;
        }
        
        .message.own {
            background: #007bff;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 0.25rem;
        }
        
        .message.other {
            background: white;
            border: 1px solid #dee2e6;
            margin-right: auto;
            border-bottom-left-radius: 0.25rem;
        }
        
        .message-header {
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
            opacity: 0.8;
        }
        
        .message-content {
            margin-bottom: 0.25rem;
        }
        
        .message-time {
            font-size: 0.7rem;
            opacity: 0.6;
        }
        
        .chat-input {
            border-top: 1px solid #dee2e6;
            padding: 1rem;
            background: white;
            border-radius: 0.5rem;
        }
        
        .online-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
        }
        
        .online-indicator.online { background: #28a745; }
        .online-indicator.away { background: #ffc107; }
        .online-indicator.offline { background: #6c757d; }
        
        .typing-indicator {
            font-style: italic;
            color: #6c757d;
            font-size: 0.9rem;
            padding: 0.5rem;
            display: none;
        }
        
        .chat-sidebar {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
        }
        
        .user-item {
            padding: 0.5rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .user-item:hover {
            background: #e9ecef;
        }
        
        .user-item.active {
            background: #007bff;
            color: white;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .message.new {
            animation: messageSlideIn 0.3s ease-out;
        }
        
        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .emoji-picker {
            position: absolute;
            bottom: 100%;
            right: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            display: none;
            z-index: 1000;
        }
        
        .emoji-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            padding: 0.25rem;
            cursor: pointer;
            border-radius: 0.25rem;
        }
        
        .emoji-btn:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">🏥 PHELS</h4>
                        <small class="text-white-50">Real-Time Chat</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="dashboard.php">
                                📊 Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="inventory.php">
                                📦 Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="storage.php">
                                🏭 Storage
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="shipments.php">
                                🚚 Shipments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="alerts.php">
                                ⚠️ Alerts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="operations.php">
                                🚨 Operations
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-warning" href="logout.php">
                                🚪 Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">💬 Real-Time Emergency Communication</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearChat()">
                                🗑️ Clear Chat
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportChat()">
                                📥 Export Chat
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Chat Area -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    🚨 Emergency Operations Chat
                                    <span class="badge bg-success ms-2">Live</span>
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="chat-container">
                                    <div class="chat-messages" id="chatMessages">
                                        <?php foreach ($recentMessages as $msg): ?>
                                            <div class="message <?php echo $msg['user_id'] == $currentUser['id'] ? 'own' : 'other'; ?>">
                                                <div class="message-header">
                                                    <strong><?php echo htmlspecialchars($msg['full_name']); ?></strong>
                                                    <span class="badge bg-secondary ms-2"><?php echo ucfirst(str_replace('_', ' ', $msg['role'])); ?></span>
                                                </div>
                                                <div class="message-content">
                                                    <?php echo htmlspecialchars($msg['message']); ?>
                                                </div>
                                                <div class="message-time">
                                                    <?php echo date('M j, H:i', strtotime($msg['created_at'])); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="typing-indicator" id="typingIndicator">
                                        Someone is typing...
                                    </div>
                                    
                                    <div class="chat-input">
                                        <form id="chatForm" class="d-flex gap-2">
                                            <div class="position-relative flex-grow-1">
                                                <input type="text" class="form-control" id="messageInput" 
                                                       placeholder="Type your emergency message..." 
                                                       autocomplete="off" required>
                                                <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-0 h-100" 
                                                        onclick="toggleEmojiPicker()" style="border: none;">
                                                    😊
                                                </button>
                                                <div class="emoji-picker" id="emojiPicker">
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('🚨')">🚨</button>
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('⚠️')">⚠️</button>
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('🆘')">🆘</button>
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('🏥')">🏥</button>
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('🚑')">🚑</button>
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('📦')">📦</button>
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('✅')">✅</button>
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('❌')">❌</button>
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('⏰')">⏰</button>
                                                        <button type="button" class="emoji-btn" onclick="addEmoji('📍')">📍</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane"></i> Send
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Online Users & Quick Actions -->
                    <div class="col-lg-4">
                        <div class="row">
                            <!-- Online Users -->
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">👥 Online Users</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="chat-sidebar">
                                            <?php foreach ($onlineUsers as $user): ?>
                                                <div class="user-item d-flex align-items-center">
                                                    <span class="online-indicator <?php echo $user['status']; ?>"></span>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                                        <small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></small>
                                                    </div>
                                                    <span class="badge bg-<?php echo $user['status'] === 'online' ? 'success' : ($user['status'] === 'away' ? 'warning' : 'secondary'); ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">⚡ Quick Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-danger" onclick="sendEmergencyAlert()">
                                                🚨 Emergency Alert
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="sendStatusUpdate()">
                                                📊 Status Update
                                            </button>
                                            <button class="btn btn-outline-info" onclick="sendLocationShare()">
                                                📍 Share Location
                                            </button>
                                            <button class="btn btn-outline-success" onclick="sendConfirmation()">
                                                ✅ Confirmation
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Statistics -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">📈 Chat Statistics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <h4 class="text-primary"><?php echo count($recentMessages); ?></h4>
                                                <small class="text-muted">Messages</small>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="text-success"><?php echo count(array_filter($onlineUsers, fn($u) => $u['status'] === 'online')); ?></h4>
                                                <small class="text-muted">Online</small>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                Last activity: <?php echo date('H:i'); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let lastMessageId = <?php echo count($recentMessages) > 0 ? $recentMessages[count($recentMessages)-1]['id'] : 0; ?>;
        let typingTimeout;
        let isTyping = false;

        // Initialize chat
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
            startRealTimeUpdates();
        });

        // Handle form submission
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });

        // Handle typing indicator
        document.getElementById('messageInput').addEventListener('input', function() {
            if (!isTyping) {
                isTyping = true;
                showTypingIndicator();
            }
            
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                isTyping = false;
                hideTypingIndicator();
            }, 1000);
        });

        // Send message function
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;
            
            // Add message to chat immediately (optimistic update)
            addMessageToChat({
                user_id: <?php echo $currentUser['id']; ?>,
                full_name: '<?php echo addslashes($currentUser['full_name']); ?>',
                role: '<?php echo addslashes($currentUser['role']); ?>',
                message: message,
                created_at: new Date().toISOString()
            }, true);
            
            // Clear input
            input.value = '';
            
            // Send to server
            fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_message&message=${encodeURIComponent(message)}&ajax=1`
            });
        }

        // Add message to chat
        function addMessageToChat(messageData, isOwn = false) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            
            messageDiv.className = `message ${isOwn ? 'own' : 'other'} new`;
            messageDiv.innerHTML = `
                <div class="message-header">
                    <strong>${messageData.full_name}</strong>
                    <span class="badge bg-secondary ms-2">${messageData.role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                </div>
                <div class="message-content">
                    ${messageData.message}
                </div>
                <div class="message-time">
                    ${new Date(messageData.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
            
            // Remove animation class after animation
            setTimeout(() => {
                messageDiv.classList.remove('new');
            }, 300);
        }

        // Real-time updates using Server-Sent Events
        function startRealTimeUpdates() {
            const eventSource = new EventSource('chat_sse.php');
            
            eventSource.onmessage = function(event) {
                const data = JSON.parse(event.data);
                
                if (data.type === 'new_message' && data.message.id > lastMessageId) {
                    addMessageToChat(data.message);
                    lastMessageId = data.message.id;
                    showNotification(data.message.full_name, data.message.message);
                }
                
                if (data.type === 'user_typing') {
                    showTypingIndicator(data.user_name);
                }
                
                if (data.type === 'user_stopped_typing') {
                    hideTypingIndicator();
                }
            };
            
            eventSource.onerror = function() {
                console.log('SSE connection error, retrying...');
                setTimeout(startRealTimeUpdates, 5000);
            };
        }

        // Typing indicators
        function showTypingIndicator(userName = 'Someone') {
            const indicator = document.getElementById('typingIndicator');
            indicator.textContent = `${userName} is typing...`;
            indicator.style.display = 'block';
        }

        function hideTypingIndicator() {
            document.getElementById('typingIndicator').style.display = 'none';
        }

        // Utility functions
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showNotification(userName, message) {
            if (Notification.permission === 'granted') {
                new Notification(`New message from ${userName}`, {
                    body: message,
                    icon: '/favicon.ico'
                });
            }
        }

        function toggleEmojiPicker() {
            const picker = document.getElementById('emojiPicker');
            picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
        }

        function addEmoji(emoji) {
            const input = document.getElementById('messageInput');
            input.value += emoji;
            input.focus();
            toggleEmojiPicker();
        }

        // Quick action functions
        function sendEmergencyAlert() {
            const input = document.getElementById('messageInput');
            input.value = '🚨 EMERGENCY ALERT: ';
            input.focus();
        }

        function sendStatusUpdate() {
            const input = document.getElementById('messageInput');
            input.value = '📊 STATUS UPDATE: ';
            input.focus();
        }

        function sendLocationShare() {
            const input = document.getElementById('messageInput');
            input.value = '📍 LOCATION SHARE: ';
            input.focus();
        }

        function sendConfirmation() {
            const input = document.getElementById('messageInput');
            input.value = '✅ CONFIRMATION: ';
            input.focus();
        }

        function clearChat() {
            if (confirm('Are you sure you want to clear the chat? This action cannot be undone.')) {
                document.getElementById('chatMessages').innerHTML = '';
            }
        }

        function exportChat() {
            const messages = document.querySelectorAll('.message');
            let exportText = 'PHELS Emergency Chat Export\n';
            exportText += 'Generated: ' + new Date().toLocaleString() + '\n\n';
            
            messages.forEach(msg => {
                const header = msg.querySelector('.message-header').textContent;
                const content = msg.querySelector('.message-content').textContent;
                const time = msg.querySelector('.message-time').textContent;
                exportText += `[${time}] ${header}: ${content}\n`;
            });
            
            const blob = new Blob([exportText], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'phels_chat_export.txt';
            a.click();
            URL.revokeObjectURL(url);
        }

        // Request notification permission
        if (Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Close emoji picker when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.emoji-picker') && !e.target.closest('button[onclick="toggleEmojiPicker()"]')) {
                document.getElementById('emojiPicker').style.display = 'none';
            }
        });
    </script>
</body>
</html>
