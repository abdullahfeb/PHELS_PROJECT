<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Prevent output buffering
if (ob_get_level()) ob_end_clean();

require_once 'config.php';
require_once 'includes/Database.php';

$db = Database::getInstance();

// Function to send SSE data
function sendSSE($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Keep connection alive and send updates
$lastMessageId = 0;
$lastCheck = time();

while (true) {
    // Check for new messages every 2 seconds
    if (time() - $lastCheck >= 2) {
        $lastCheck = time();
        
        // Get new messages since last check
        $newMessages = $db->select("
            SELECT cm.*, u.username, u.full_name, u.role 
            FROM chat_messages cm 
            JOIN users u ON cm.user_id = u.id 
            WHERE cm.id > ? 
            ORDER BY cm.created_at ASC
        ", [$lastMessageId]);
        
        // Send new messages
        foreach ($newMessages as $message) {
            sendSSE([
                'type' => 'new_message',
                'message' => $message
            ]);
            $lastMessageId = $message['id'];
        }
        
        // Send heartbeat to keep connection alive
        sendSSE([
            'type' => 'heartbeat',
            'timestamp' => time()
        ]);
    }
    
    // Check if client is still connected
    if (connection_aborted()) {
        break;
    }
    
    // Small delay to prevent excessive CPU usage
    usleep(500000); // 0.5 seconds
}
?>
