<?php
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

// Handle new action log
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_action'])) {
    $sql = "INSERT INTO action_logs (user_id, type, priority, title, details) VALUES (?, ?, ?, ?, ?)";
    $db->insert($sql, [
        $currentUser['id'],
        $_POST['type'],
        $_POST['priority'],
        $_POST['title'],
        $_POST['details']
    ]);
    $success = "Action logged successfully!";
}

// Get recent action logs
$action_logs = $db->select("SELECT al.*, u.full_name FROM action_logs al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 20");

// Get recent chat messages
$chat_messages = $db->select("SELECT cm.*, u.full_name FROM chat_messages cm JOIN users u ON cm.user_id = u.id ORDER BY cm.created_at DESC LIMIT 50");
$chat_messages = array_reverse($chat_messages); // Show oldest first
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Operations Center - PHELS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .action-log {
            border-left: 4px solid #007bff;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
        }
        .action-log.emergency { border-left-color: #dc3545; }
        .action-log.maintenance { border-left-color: #ffc107; }
        .action-log.shipment { border-left-color: #28a745; }
        .action-log.inventory { border-left-color: #17a2b8; }
        .chat-container {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background: #fff;
        }
        .chat-message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .chat-message.own {
            background: #007bff;
            color: white;
            margin-left: 20%;
        }
        .chat-message.other {
            background: #e9ecef;
            margin-right: 20%;
        }
        .priority-urgent { border-left-width: 8px; }
        .priority-high { border-left-width: 6px; }
        .priority-medium { border-left-width: 4px; }
        .priority-low { border-left-width: 2px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-shield-alt fa-2x mb-2"></i>
                        <h5>PHELS</h5>
                        <small class="text-muted">Emergency Logistics</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="inventory.php">
                            <i class="fas fa-pills"></i> Inventory
                        </a>
                        <a class="nav-link" href="storage.php">
                            <i class="fas fa-warehouse"></i> Storage Monitoring
                        </a>
                        <a class="nav-link" href="shipments.php">
                            <i class="fas fa-truck"></i> Shipments
                        </a>
                        <a class="nav-link" href="alerts.php">
                            <i class="fas fa-exclamation-triangle"></i> Alerts
                        </a>
                        <a class="nav-link active" href="operations.php">
                            <i class="fas fa-bullhorn"></i> Operations Center
                        </a>
                        <a class="nav-link" href="chat.php">
                            <i class="fas fa-comments"></i> Chat
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-0">
                <div class="main-content">
                    <!-- Top Navbar -->
                    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                        <div class="container-fluid">
                            <span class="navbar-brand">Emergency Operations Center</span>
                            <div class="navbar-nav ms-auto">
                                <div class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($currentUser['full_name']); ?>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </nav>

                    <!-- Content -->
                    <div class="p-4">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Action Logging -->
                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-clipboard-list me-2"></i>Log New Action/Event
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <input type="hidden" name="log_action" value="1">
                                            <div class="mb-3">
                                                <label class="form-label">Action Type *</label>
                                                <select class="form-select" name="type" required>
                                                    <option value="">Select Type</option>
                                                    <option value="emergency">Emergency</option>
                                                    <option value="maintenance">Maintenance</option>
                                                    <option value="shipment">Shipment</option>
                                                    <option value="inventory">Inventory</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Priority *</label>
                                                <select class="form-select" name="priority" required>
                                                    <option value="">Select Priority</option>
                                                    <option value="urgent">Urgent</option>
                                                    <option value="high">High</option>
                                                    <option value="medium">Medium</option>
                                                    <option value="low">Low</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Title *</label>
                                                <input type="text" class="form-control" name="title" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Details</label>
                                                <textarea class="form-control" name="details" rows="3" placeholder="Describe the action or event..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-2"></i>Log Action
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Live Action Feed -->
                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-rss me-2"></i>Live Action Feed
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="action-feed" style="max-height: 400px; overflow-y: auto;">
                                            <?php if (empty($action_logs)): ?>
                                                <p class="text-muted text-center py-3">No actions logged yet</p>
                                            <?php else: ?>
                                                <?php foreach ($action_logs as $log): ?>
                                                    <div class="action-log <?php echo $log['type']; ?> priority-<?php echo $log['priority']; ?>">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($log['title']); ?></h6>
                                                            <small class="text-muted"><?php echo date('M j H:i', strtotime($log['created_at'])); ?></small>
                                                        </div>
                                                        <p class="mb-2"><?php echo htmlspecialchars($log['details'] ?: 'No details provided'); ?></p>
                                                        <div class="d-flex gap-2">
                                                            <span class="badge bg-<?php echo $log['priority'] === 'urgent' ? 'danger' : ($log['priority'] === 'high' ? 'warning' : ($log['priority'] === 'medium' ? 'info' : 'secondary')); ?>">
                                                                <?php echo ucfirst($log['priority']); ?>
                                                            </span>
                                                            <span class="badge bg-secondary"><?php echo ucfirst($log['type']); ?></span>
                                                            <small class="text-muted">by <?php echo htmlspecialchars($log['full_name']); ?></small>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat System -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-comments me-2"></i>Emergency Coordination Chat
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chat-container" id="chatContainer">
                                            <?php if (empty($chat_messages)): ?>
                                                <p class="text-muted text-center py-3">No messages yet. Start the conversation!</p>
                                            <?php else: ?>
                                                <?php foreach ($chat_messages as $message): ?>
                                                    <div class="chat-message <?php echo $message['user_id'] == $currentUser['id'] ? 'own' : 'other'; ?>">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <strong><?php echo htmlspecialchars($message['full_name']); ?></strong>
                                                            <small class="<?php echo $message['user_id'] == $currentUser['id'] ? 'text-white-50' : 'text-muted'; ?>">
                                                                <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                                            </small>
                                                        </div>
                                                        <p class="mb-0"><?php echo htmlspecialchars($message['message']); ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-3">
                                            <form id="chatForm" class="d-flex gap-2">
                                                <input type="text" class="form-control" id="chatMessage" placeholder="Type your message..." required>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-scroll chat to bottom
        const chatContainer = document.getElementById('chatContainer');
        chatContainer.scrollTop = chatContainer.scrollHeight;

        // Handle chat form submission
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const messageInput = document.getElementById('chatMessage');
            const message = messageInput.value.trim();
            
            if (message) {
                // This would send the message via AJAX
                console.log('Sending message:', message);
                messageInput.value = '';
                
                // For demo purposes, add message to chat
                const chatContainer = document.getElementById('chatContainer');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'chat-message own';
                messageDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <strong><?php echo htmlspecialchars($currentUser['full_name']); ?></strong>
                        <small class="text-white-50">${new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'})}</small>
                    </div>
                    <p class="mb-0">${message}</p>
                `;
                chatContainer.appendChild(messageDiv);
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });

        // Auto-refresh action feed every 30 seconds
        setInterval(() => {
            console.log('Refreshing action feed...');
        }, 30000);

        // Auto-refresh chat every 10 seconds
        setInterval(() => {
            console.log('Refreshing chat...');
        }, 10000);
    </script>
</body>
</html>
