<?php
// Add Payment System Tables to PHELS Database
// This script adds tables for hospital-pharma ordering and payment system

echo "<h2>Adding Payment System to PHELS Database</h2>";

try {
    require_once 'config.php';
    require_once 'includes/Database.php';
    
    $db = Database::getInstance();
    
    echo "<p>✓ Connected to database successfully</p>";
    
    // Create orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hospital_id INT NOT NULL,
        pharma_id INT NOT NULL,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        delivery_date DATE,
        notes TEXT,
        FOREIGN KEY (hospital_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (pharma_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $db->query($sql);
    echo "<p>✓ Orders table created</p>";
    
    // Create order_items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        medical_item_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10, 2) NOT NULL,
        total_price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (medical_item_id) REFERENCES medical_items(id) ON DELETE CASCADE
    )";
    $db->query($sql);
    echo "<p>✓ Order items table created</p>";
    
    // Create payments table
    $sql = "CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        payment_method ENUM('bank_transfer', 'bkash', 'nagad', 'cash') NOT NULL,
        transaction_id VARCHAR(100),
        payment_status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        bank_name VARCHAR(100),
        account_number VARCHAR(50),
        mobile_number VARCHAR(20),
        notes TEXT,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    $db->query($sql);
    echo "<p>✓ Payments table created</p>";
    
    // Create bank_accounts table for hospitals
    $sql = "CREATE TABLE IF NOT EXISTS bank_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        bank_name VARCHAR(100) NOT NULL,
        account_number VARCHAR(50) NOT NULL,
        account_holder VARCHAR(100) NOT NULL,
        branch_name VARCHAR(100),
        swift_code VARCHAR(20),
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $db->query($sql);
    echo "<p>✓ Bank accounts table created</p>";
    
    // Create mobile_payment_accounts table
    $sql = "CREATE TABLE IF NOT EXISTS mobile_payment_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        payment_type ENUM('bkash', 'nagad') NOT NULL,
        account_number VARCHAR(20) NOT NULL,
        account_holder VARCHAR(100) NOT NULL,
        is_default BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $db->query($sql);
    echo "<p>✓ Mobile payment accounts table created</p>";
    
    // Create invoices table
    $sql = "CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        invoice_number VARCHAR(50) UNIQUE NOT NULL,
        invoice_date DATE NOT NULL,
        due_date DATE NOT NULL,
        subtotal DECIMAL(10, 2) NOT NULL,
        tax_amount DECIMAL(10, 2) DEFAULT 0.00,
        discount_amount DECIMAL(10, 2) DEFAULT 0.00,
        total_amount DECIMAL(10, 2) NOT NULL,
        status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    $db->query($sql);
    echo "<p>✓ Invoices table created</p>";
    
    // Insert sample bank accounts for hospitals
    $hospitals = $db->query("SELECT id FROM users WHERE role = 'hospital_staff'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($hospitals)) {
        $sampleBanks = [
            ['Brac Bank', '1234567890', 'Manila General Hospital', 'Manila Central', 'BRACBDDH'],
            ['Sonali Bank', '0987654321', 'Quezon Medical Center', 'Quezon City', 'BSONBDDH'],
            ['City Bank', '1122334455', 'Makati Medical Center', 'Makati City', 'CITIBDDH']
        ];
        
        $stmt = $db->prepare("INSERT INTO bank_accounts (user_id, bank_name, account_number, account_holder, branch_name, swift_code) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($hospitals as $index => $hospitalId) {
            if (isset($sampleBanks[$index])) {
                $bank = $sampleBanks[$index];
                $stmt->execute([$hospitalId, $bank[0], $bank[1], $bank[2], $bank[3], $bank[4]]);
            }
        }
        echo "<p>✓ Sample bank accounts created for hospitals</p>";
    }
    
    // Insert sample mobile payment accounts
    if (!empty($hospitals)) {
        $sampleMobile = [
            ['bkash', '01712345678', 'Manila General Hospital'],
            ['nagad', '01812345678', 'Quezon Medical Center'],
            ['bkash', '01912345678', 'Makati Medical Center']
        ];
        
        $stmt = $db->prepare("INSERT INTO mobile_payment_accounts (user_id, payment_type, account_number, account_holder) VALUES (?, ?, ?, ?)");
        
        foreach ($hospitals as $index => $hospitalId) {
            if (isset($sampleMobile[$index])) {
                $mobile = $sampleMobile[$index];
                $stmt->execute([$hospitalId, $mobile[0], $mobile[1], $mobile[2]]);
            }
        }
        echo "<p>✓ Sample mobile payment accounts created for hospitals</p>";
    }
    
    echo "<h3>✅ Payment system tables added successfully!</h3>";
    echo "<p>The following features are now available:</p>";
    echo "<ul>";
    echo "<li><strong>Hospital Ordering:</strong> Hospitals can place orders with pharmaceutical companies</li>";
    echo "<li><strong>Payment Methods:</strong> Bank transfer, bKash, Nagad, and cash payments</li>";
    echo "<li><strong>Bank Accounts:</strong> Hospitals can manage multiple bank accounts</li>";
    echo "<li><strong>Mobile Payments:</strong> bKash and Nagad account management</li>";
    echo "<li><strong>Invoicing:</strong> Automated invoice generation and tracking</li>";
    echo "<li><strong>Order Tracking:</strong> Complete order lifecycle management</li>";
    echo "</ul>";
    echo "<p>You can now <a href='index.php'>go back to login</a> and access the new payment features.</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error adding payment system:</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
