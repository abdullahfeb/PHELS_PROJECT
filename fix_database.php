<?php
// Database Setup Script for PHELS
// This will create the database and all required tables

echo "<h2>PHELS Database Setup</h2>";

try {
    // Connect to MySQL without specifying a database
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Connected to MySQL successfully</p>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS phels_db");
    echo "<p>✓ Database 'phels_db' created/verified</p>";
    
    // Select the database
    $pdo->exec("USE phels_db");
    echo "<p>✓ Selected database 'phels_db'</p>";
    
    // Drop existing tables to ensure clean setup (child tables first, then parent tables)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0"); // Temporarily disable foreign key checks
    
    // Drop tables in reverse dependency order
    $tables = [
        'chat_messages',
        'action_logs', 
        'shipment_items',
        'storage_logs',
        'alerts',
        'shipments',
        'storage_units',
        'medical_items',
        'users'
    ];
    
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); // Re-enable foreign key checks
    echo "<p>✓ Dropped existing tables for clean setup</p>";
    
    // Create users table
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('emergency_manager', 'field_worker', 'hospital_staff', 'pharma_company') NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p>✓ Users table created</p>";
    
    // Verify table structure
    $stmt = $pdo->query("DESCRIBE users");
    echo "<p>Users table structure:</p><ul>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>{$row['Field']} - {$row['Type']}</li>";
    }
    echo "</ul>";
    
    // Create medical_items table
    $sql = "CREATE TABLE medical_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        type ENUM('vaccine', 'medication', 'medical_supply', 'ppe') NOT NULL,
        targeted_disease VARCHAR(100),
        quantity INT NOT NULL DEFAULT 0,
        dose VARCHAR(50),
        expiry_date DATE NOT NULL,
        batch_no VARCHAR(50) NOT NULL,
        manufacturer VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p>✓ Medical items table created</p>";
    
    // Create storage_units table
    $sql = "CREATE TABLE storage_units (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        location VARCHAR(200) NOT NULL,
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        temperature_threshold DECIMAL(5, 2),
        humidity_threshold DECIMAL(5, 2),
        status ENUM('normal', 'warning', 'critical') DEFAULT 'normal',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p>✓ Storage units table created</p>";
    
    // Create shipments table
    $sql = "CREATE TABLE shipments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        origin VARCHAR(200) NOT NULL,
        destination VARCHAR(200) NOT NULL,
        status ENUM('pending', 'preparing', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending',
        estimated_delivery DATETIME NOT NULL,
        assigned_to VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p>✓ Shipments table created</p>";
    
    // Create alerts table
    $sql = "CREATE TABLE alerts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        type ENUM('expiry', 'low_stock', 'temperature', 'humidity', 'system') NOT NULL,
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        is_resolved BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p>✓ Alerts table created</p>";
    
    // Create storage_logs table
    $sql = "CREATE TABLE storage_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        storage_unit_id INT NOT NULL,
        temperature DECIMAL(5, 2),
        humidity DECIMAL(5, 2),
        status ENUM('normal', 'warning', 'critical') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (storage_unit_id) REFERENCES storage_units(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p>✓ Storage logs table created</p>";
    
    // Create shipment_items table
    $sql = "CREATE TABLE shipment_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        shipment_id INT NOT NULL,
        item_id INT NOT NULL,
        quantity INT NOT NULL,
        FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES medical_items(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p>✓ Shipment items table created</p>";
    
    // Create action_logs table
    $sql = "CREATE TABLE action_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        user_id INT NOT NULL,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p>✓ Action logs table created</p>";
    
    // Create chat_messages table
    $sql = "CREATE TABLE chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p>✓ Chat messages table created</p>";
    
    // Check if users table has data
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    
    if ($userCount == 0) {
        echo "<p>Creating default users...</p>";
        
        // Insert default users
        $users = [
            ['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin@phels.com', 'Emergency Manager', 'emergency_manager'],
            ['fieldworker', password_hash('field123', PASSWORD_DEFAULT), 'field@phels.com', 'Field Worker', 'field_worker'],
            ['hospital', password_hash('hospital123', PASSWORD_DEFAULT), 'hospital@phels.com', 'Hospital Staff', 'hospital_staff'],
            ['pharma', password_hash('pharma123', PASSWORD_DEFAULT), 'pharma@phels.com', 'Pharmaceutical Company', 'pharma_company']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        foreach ($users as $user) {
            $stmt->execute($user);
        }
        
        echo "<p>✓ Default users created</p>";
        echo "<h3>Login Credentials:</h3>";
        echo "<ul>";
        echo "<li><strong>admin</strong> / <strong>admin123</strong> (Emergency Manager)</li>";
        echo "<li><strong>fieldworker</strong> / <strong>field123</strong> (Field Worker)</li>";
        echo "<li><strong>hospital</strong> / <strong>hospital123</strong> (Hospital Staff)</li>";
        echo "<li><strong>pharma</strong> / <strong>pharma123</strong> (Pharmaceutical Company)</li>";
        echo "</ul>";
    } else {
        echo "<p>✓ Users table already has data</p>";
    }
    
    echo "<h3>✅ Database setup completed successfully!</h3>";
    echo "<p>You can now <a href='index.php'>go back to login</a> and use the system.</p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Error setting up database:</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Make sure XAMPP MySQL service is running.</p>";
}
?>
