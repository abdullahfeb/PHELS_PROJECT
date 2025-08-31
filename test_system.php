<?php
echo "<h1>🔧 PHELS System Test</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'config.php';
    require_once 'includes/Database.php';
    
    $db = Database::getInstance();
    echo "✅ Database connection successful<br>";
    
    // Test if users table exists and has data
    $users = $db->select("SELECT COUNT(*) as count FROM users");
    echo "✅ Users table accessible. Count: " . $users[0]['count'] . "<br>";
    
    if ($users[0]['count'] > 0) {
        $userList = $db->select("SELECT username, role FROM users LIMIT 5");
        echo "✅ Users found:<br>";
        foreach ($userList as $user) {
            echo "&nbsp;&nbsp;- " . $user['username'] . " (" . $user['role'] . ")<br>";
        }
    } else {
        echo "❌ No users found. Run create_users.php first!<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 2: File Inclusion Test
echo "<h2>2. File Inclusion Test</h2>";
$files = [
    'dashboard_emergency_manager.php',
    'dashboard_field_worker.php', 
    'dashboard_hospital.php',
    'dashboard_pharma.php',
    'inventory.php',
    'storage.php',
    'shipments.php',
    'alerts.php',
    'operations.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ " . $file . " exists<br>";
    } else {
        echo "❌ " . $file . " missing<br>";
    }
}

// Test 3: Authentication Test
echo "<h2>3. Authentication Test</h2>";
try {
    require_once 'includes/Auth.php';
    $auth = new Auth();
    echo "✅ Auth class loaded successfully<br>";
    
    // Test login with admin user
    if ($users[0]['count'] > 0) {
        $testUser = $db->selectOne("SELECT username, password FROM users WHERE username = 'admin'");
        if ($testUser) {
            echo "✅ Admin user found in database<br>";
            if (password_verify('admin123', $testUser['password'])) {
                echo "✅ Password verification working<br>";
            } else {
                echo "❌ Password verification failed<br>";
            }
        } else {
            echo "❌ Admin user not found<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Auth error: " . $e->getMessage() . "<br>";
}

echo "<h2>🎯 Next Steps</h2>";
if ($users[0]['count'] == 0) {
    echo "<p><strong>1. Run create_users.php first:</strong> <a href='create_users.php'>Create Users</a></p>";
} else {
    echo "<p><strong>1. Try logging in:</strong> <a href='index.php'>Login Page</a></p>";
}
echo "<p><strong>2. Check dashboard:</strong> <a href='dashboard.php'>Dashboard</a></p>";
echo "<p><strong>3. Test features:</strong> <a href='inventory.php'>Inventory</a> | <a href='storage.php'>Storage</a> | <a href='shipments.php'>Shipments</a></p>";

echo "<hr><p><em>If you see any ❌ errors above, fix them before proceeding.</em></p>";
?>
