<?php
require_once 'config.php';
require_once 'includes/Database.php';

// This script creates the user accounts with proper password hashes
// Run this once after importing the database schema

$db = Database::getInstance();

// Delete existing users first
$db->query("DELETE FROM users");

// Create users with proper password hashes
$users = [
    ['username' => 'admin', 'password' => 'admin123', 'email' => 'admin@phels.com', 'full_name' => 'Emergency Manager', 'role' => 'emergency_manager'],
    ['username' => 'fieldworker', 'password' => 'field123', 'email' => 'field@phels.com', 'full_name' => 'Field Worker', 'role' => 'field_worker'],
    ['username' => 'hospital', 'password' => 'hospital123', 'email' => 'hospital@phels.com', 'full_name' => 'Hospital Staff', 'role' => 'hospital_staff'],
    ['username' => 'pharma', 'password' => 'pharma123', 'email' => 'pharma@phels.com', 'full_name' => 'Pharmaceutical Company', 'role' => 'pharma_company']
];

foreach ($users as $user) {
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)";
    $db->insert($sql, [$user['username'], $hashedPassword, $user['email'], $user['full_name'], $user['role']]);
    
    echo "Created user: {$user['username']} with password: {$user['password']}\n";
}

echo "\nAll users created successfully!\n";
echo "You can now login with any of these accounts:\n";
echo "- admin / admin123\n";
echo "- fieldworker / field123\n";
echo "- hospital / hospital123\n";
echo "- pharma / pharma123\n";
?>
