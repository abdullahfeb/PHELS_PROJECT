-- PHELS Database Schema
-- Public Health Emergency Logistics System

-- Create database
CREATE DATABASE IF NOT EXISTS phels_db;
USE phels_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('emergency_manager', 'field_worker', 'hospital_staff', 'pharma_company') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Medical items table
CREATE TABLE medical_items (
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
);

-- Storage units table
CREATE TABLE storage_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    temperature_threshold DECIMAL(5, 2),
    humidity_threshold DECIMAL(5, 2),
    status ENUM('normal', 'warning', 'critical') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Storage logs table
CREATE TABLE storage_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    storage_unit_id INT NOT NULL,
    temperature DECIMAL(5, 2),
    humidity DECIMAL(5, 2),
    status ENUM('normal', 'warning', 'critical') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (storage_unit_id) REFERENCES storage_units(id) ON DELETE CASCADE
);

-- Shipments table
CREATE TABLE shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin VARCHAR(200) NOT NULL,
    destination VARCHAR(200) NOT NULL,
    status ENUM('pending', 'preparing', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending',
    estimated_delivery DATETIME NOT NULL,
    assigned_to VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Shipment items table
CREATE TABLE shipment_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES medical_items(id) ON DELETE CASCADE
);

-- Alerts table
CREATE TABLE alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    type ENUM('expiry', 'low_stock', 'temperature', 'humidity', 'system') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    is_resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Action logs table
CREATE TABLE action_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    user_id INT NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Chat messages table
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data in correct order (parents first, then children)

-- Sample users with correct password hashes
INSERT INTO users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$YourHashHere123', 'admin@phels.com', 'Emergency Manager', 'emergency_manager'),
('fieldworker', '$2y$10$YourHashHere123', 'field@phels.com', 'Field Worker', 'field_worker'),
('hospital', '$2y$10$YourHashHere123', 'hospital@phels.com', 'Hospital Staff', 'hospital_staff'),
('pharma', '$2y$10$YourHashHere123', 'pharma@phels.com', 'Pharmaceutical Company', 'pharma_company');

-- Sample medical items
INSERT INTO medical_items (name, type, targeted_disease, quantity, dose, expiry_date, batch_no, manufacturer) VALUES
('COVID-19 Vaccine', 'vaccine', 'COVID-19', 1000, '0.5ml', '2024-12-31', 'BATCH001', 'Moderna'),
('Paracetamol 500mg', 'medication', 'Fever/Pain', 5000, '500mg', '2025-06-30', 'BATCH002', 'Generic Pharma'),
('N95 Masks', 'ppe', 'Respiratory Protection', 2000, 'N/A', '2026-01-31', 'BATCH003', 'Safety Gear Inc'),
('Syringes 5ml', 'medical_supply', 'N/A', 3000, '5ml', '2027-03-31', 'BATCH004', 'Medical Supplies Co'),
('Hand Sanitizer', 'ppe', 'Hygiene', 1500, '500ml', '2025-09-30', 'BATCH005', 'Clean Hands Ltd');

-- Sample storage units
INSERT INTO storage_units (name, location, latitude, longitude, temperature_threshold, humidity_threshold) VALUES
('Central Warehouse', 'Manila Central District', 14.5995, 120.9842, 25.0, 60.0),
('North Storage', 'Quezon City', 14.5547, 121.0244, 22.0, 55.0),
('South Facility', 'Makati City', 14.6347, 120.9708, 24.0, 58.0);

-- Sample shipments
INSERT INTO shipments (origin, destination, status, estimated_delivery, assigned_to) VALUES
('Central Warehouse', 'Manila General Hospital', 'in_transit', '2024-01-15 14:00:00', 'Field Worker'),
('North Storage', 'Quezon Medical Center', 'preparing', '2024-01-16 10:00:00', 'Field Worker'),
('South Facility', 'Makati Medical Center', 'pending', '2024-01-17 16:00:00', 'Field Worker');

-- Sample shipment items (now the parent tables exist)
INSERT INTO shipment_items (shipment_id, item_id, quantity) VALUES
(1, 1, 100),
(1, 5, 200),
(2, 3, 500);

-- Sample storage logs
INSERT INTO storage_logs (storage_unit_id, temperature, humidity, status) VALUES
(1, 24.5, 58.0, 'normal'),
(1, 26.8, 65.0, 'warning'),
(2, 22.1, 54.0, 'normal'),
(3, 27.2, 70.0, 'critical');

-- Sample alerts
INSERT INTO alerts (title, description, type, priority) VALUES
('Low Stock Alert', 'COVID-19 Vaccine stock is below threshold', 'low_stock', 'high'),
('Temperature Warning', 'South Facility temperature above threshold', 'temperature', 'medium'),
('Expiry Alert', 'Paracetamol batch expiring in 30 days', 'expiry', 'high');

-- Sample action logs
INSERT INTO action_logs (title, description, user_id, priority) VALUES
('Shipment Created', 'New shipment created for Manila General Hospital', 1, 'medium'),
('Storage Check', 'Routine storage unit inspection completed', 1, 'low'),
('Alert Resolved', 'Temperature issue resolved at South Facility', 1, 'medium');

-- Sample chat messages
INSERT INTO chat_messages (user_id, message) VALUES
(1, 'System is running smoothly today'),
(2, 'Shipment delivered successfully'),
(3, 'Inventory check completed');

-- Create indexes for better performance
CREATE INDEX idx_medical_items_expiry ON medical_items(expiry_date);
CREATE INDEX idx_storage_logs_unit_time ON storage_logs(storage_unit_id, created_at);
CREATE INDEX idx_shipments_status ON shipments(status);
CREATE INDEX idx_alerts_type_priority ON alerts(type, priority);
CREATE INDEX idx_action_logs_user_time ON action_logs(user_id, created_at);
CREATE INDEX idx_chat_messages_user_time ON chat_messages(user_id, created_at);
