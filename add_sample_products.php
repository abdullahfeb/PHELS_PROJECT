<?php
// Add Sample Medical Products with Prices and Pharma Company Associations
// This script adds sample products to the medical_items table

echo "<h2>Adding Sample Medical Products</h2>";

try {
    require_once 'config.php';
    require_once 'includes/Database.php';
    
    $db = Database::getInstance();
    
    echo "<p>✓ Connected to database successfully</p>";
    
    // First, add pharma_id column to medical_items if it doesn't exist
    try {
        $db->query("ALTER TABLE medical_items ADD COLUMN pharma_id INT");
        echo "<p>✓ Added pharma_id column to medical_items table</p>";
    } catch (Exception $e) {
        echo "<p>ℹ️ pharma_id column already exists</p>";
    }
    
    // Add foreign key constraint if it doesn't exist
    try {
        $db->query("ALTER TABLE medical_items ADD CONSTRAINT fk_medical_items_pharma FOREIGN KEY (pharma_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "<p>✓ Added foreign key constraint</p>";
    } catch (Exception $e) {
        echo "<p>ℹ️ Foreign key constraint already exists</p>";
    }
    
    // Add price column if it doesn't exist
    try {
        $db->query("ALTER TABLE medical_items ADD COLUMN price DECIMAL(10, 2) DEFAULT 100.00");
        echo "<p>✓ Added price column to medical_items table</p>";
    } catch (Exception $e) {
        echo "<p>ℹ️ Price column already exists</p>";
    }
    
    // Get pharma company IDs
    $pharma_companies = $db->query("SELECT id FROM users WHERE role = 'pharma_company'")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($pharma_companies)) {
        echo "<p>❌ No pharmaceutical companies found. Please create pharma company accounts first.</p>";
        exit;
    }
    
    echo "<p>✓ Found " . count($pharma_companies) . " pharmaceutical companies</p>";
    
    // Clear existing medical items
    $db->query("DELETE FROM medical_items");
    echo "<p>✓ Cleared existing medical items</p>";
    
    // Sample medical products with realistic prices
    $sample_products = [
        // Pharma Company 1
        [
            'name' => 'COVID-19 Vaccine (Moderna)',
            'type' => 'vaccine',
            'targeted_disease' => 'COVID-19',
            'quantity' => 5000,
            'dose' => '0.5ml',
            'expiry_date' => '2025-12-31',
            'batch_no' => 'MOD-2024-001',
            'manufacturer' => 'Moderna Inc.',
            'price' => 150.00,
            'pharma_id' => $pharma_companies[0]
        ],
        [
            'name' => 'Paracetamol 500mg Tablets',
            'type' => 'medication',
            'targeted_disease' => 'Fever/Pain',
            'quantity' => 10000,
            'dose' => '500mg',
            'expiry_date' => '2026-06-30',
            'batch_no' => 'PAR-2024-001',
            'manufacturer' => 'Generic Pharma Ltd.',
            'price' => 25.00,
            'pharma_id' => $pharma_companies[0]
        ],
        [
            'name' => 'N95 Respirator Masks',
            'type' => 'ppe',
            'targeted_disease' => 'Respiratory Protection',
            'quantity' => 8000,
            'dose' => 'N/A',
            'expiry_date' => '2027-01-31',
            'batch_no' => 'N95-2024-001',
            'manufacturer' => 'Safety Gear Inc.',
            'price' => 45.00,
            'pharma_id' => $pharma_companies[0]
        ],
        
        // Pharma Company 2
        [
            'name' => 'Insulin Glargine Injection',
            'type' => 'medication',
            'targeted_disease' => 'Diabetes',
            'quantity' => 2000,
            'dose' => '100 units/ml',
            'expiry_date' => '2025-09-30',
            'batch_no' => 'INS-2024-001',
            'manufacturer' => 'Diabetes Care Pharma',
            'price' => 850.00,
            'pharma_id' => $pharma_companies[1] ?? $pharma_companies[0]
        ],
        [
            'name' => 'Syringes 5ml (Sterile)',
            'type' => 'medical_supply',
            'targeted_disease' => 'N/A',
            'quantity' => 15000,
            'dose' => '5ml',
            'expiry_date' => '2027-03-31',
            'batch_no' => 'SYR-2024-001',
            'manufacturer' => 'Medical Supplies Co.',
            'price' => 15.00,
            'pharma_id' => $pharma_companies[1] ?? $pharma_companies[0]
        ],
        [
            'name' => 'Hand Sanitizer 500ml',
            'type' => 'ppe',
            'targeted_disease' => 'Hygiene',
            'quantity' => 3000,
            'dose' => '500ml',
            'expiry_date' => '2026-12-31',
            'batch_no' => 'SAN-2024-001',
            'manufacturer' => 'Clean Hands Ltd.',
            'price' => 120.00,
            'pharma_id' => $pharma_companies[1] ?? $pharma_companies[0]
        ],
        
        // Pharma Company 3 (if exists)
        [
            'name' => 'Amoxicillin 500mg Capsules',
            'type' => 'medication',
            'targeted_disease' => 'Bacterial Infections',
            'quantity' => 12000,
            'dose' => '500mg',
            'expiry_date' => '2026-08-31',
            'batch_no' => 'AMX-2024-001',
            'manufacturer' => 'Antibiotic Pharma',
            'price' => 35.00,
            'pharma_id' => $pharma_companies[2] ?? $pharma_companies[0]
        ],
        [
            'name' => 'Surgical Gloves (Latex Free)',
            'type' => 'ppe',
            'targeted_disease' => 'Infection Control',
            'quantity' => 25000,
            'dose' => 'N/A',
            'expiry_date' => '2027-05-31',
            'batch_no' => 'GLO-2024-001',
            'manufacturer' => 'Protective Gear Co.',
            'price' => 8.00,
            'pharma_id' => $pharma_companies[2] ?? $pharma_companies[0]
        ],
        [
            'name' => 'IV Saline Solution 0.9%',
            'type' => 'medical_supply',
            'targeted_disease' => 'Dehydration',
            'quantity' => 5000,
            'dose' => '500ml',
            'expiry_date' => '2026-11-30',
            'batch_no' => 'IVS-2024-001',
            'manufacturer' => 'Fluid Solutions Inc.',
            'price' => 75.00,
            'pharma_id' => $pharma_companies[2] ?? $pharma_companies[0]
        ]
    ];
    
    // Insert sample products
    $stmt = $db->prepare("
        INSERT INTO medical_items (name, type, targeted_disease, quantity, dose, expiry_date, batch_no, manufacturer, price, pharma_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($sample_products as $product) {
        $stmt->execute([
            $product['name'],
            $product['type'],
            $product['targeted_disease'],
            $product['quantity'],
            $product['dose'],
            $product['expiry_date'],
            $product['batch_no'],
            $product['manufacturer'],
            $product['price'],
            $product['pharma_id']
        ]);
    }
    
    echo "<p>✓ Added " . count($sample_products) . " sample medical products</p>";
    
    // Show summary of added products
    echo "<h3>Sample Products Added:</h3>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>Product</th><th>Type</th><th>Price</th><th>Quantity</th><th>Pharma Company</th></tr></thead><tbody>";
    
    foreach ($sample_products as $product) {
        $pharma_name = $db->query("SELECT full_name FROM users WHERE id = ?", [$product['pharma_id']])->fetchColumn();
        echo "<tr>";
        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
        echo "<td>" . ucfirst(str_replace('_', ' ', $product['type'])) . "</td>";
        echo "<td>₱" . number_format($product['price'], 2) . "</td>";
        echo "<td>" . number_format($product['quantity']) . "</td>";
        echo "<td>" . htmlspecialchars($pharma_name) . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
    echo "</div>";
    
    echo "<h3>✅ Sample products added successfully!</h3>";
    echo "<p>Hospitals can now browse and order these products through the ordering system.</p>";
    echo "<p>You can now <a href='index.php'>go back to login</a> and test the ordering system.</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error adding sample products:</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
