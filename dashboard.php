<?php
require_once 'config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize authentication
$auth = new Auth();

// Debug information
error_log('Dashboard access attempt');
error_log('Session data: ' . print_r($_SESSION, true));

// Check authentication
if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Additional security check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    session_destroy();
    header('Location: index.php?error=invalid_session');
    exit();
}

// Debug user information
$currentUser = $auth->getCurrentUser();
error_log('Current user data: ' . print_r($currentUser, true));

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$userRole = $currentUser['role'];

// Get dashboard statistics based on user role
$stats = [];

if ($userRole === 'emergency_manager') {
    // Full system statistics for emergency managers
    $stats['total_items'] = $db->selectOne("SELECT COUNT(*) as count FROM medical_items")['count'];
    $stats['active_shipments'] = $db->selectOne("SELECT COUNT(*) as count FROM shipments WHERE status IN ('preparing', 'in_transit')")['count'];
    $stats['total_storage'] = $db->selectOne("SELECT COUNT(*) as count FROM storage_units")['count'];
    $stats['pending_alerts'] = $db->selectOne("SELECT COUNT(*) as count FROM alerts WHERE is_resolved = 0")['count'];
    
    // Recent data for emergency managers
    $recentAlerts = $db->select("SELECT * FROM alerts WHERE is_resolved = 0 ORDER BY created_at DESC LIMIT 5");
    $recentShipments = $db->select("SELECT * FROM shipments ORDER BY created_at DESC LIMIT 5");
    $recentActions = $db->select("SELECT al.*, u.full_name FROM action_logs al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 5");
    
} elseif ($userRole === 'field_worker') {
    // Field worker specific statistics
    $stats['my_shipments'] = $db->selectOne("SELECT COUNT(*) as count FROM shipments WHERE assigned_to = ? AND status IN ('preparing', 'in_transit')", [$currentUser['full_name']])['count'];
    $stats['today_deliveries'] = $db->selectOne("SELECT COUNT(*) as count FROM shipments WHERE assigned_to = ? AND DATE(estimated_delivery) = CURDATE()", [$currentUser['full_name']])['count'];
    $stats['pending_tasks'] = $db->selectOne("SELECT COUNT(*) as count FROM shipments WHERE assigned_to = ? AND status = 'pending'", [$currentUser['full_name']])['count'];
    $stats['completed_today'] = $db->selectOne("SELECT COUNT(*) as count FROM shipments WHERE assigned_to = ? AND status = 'delivered' AND DATE(updated_at) = CURDATE()", [$currentUser['full_name']])['count'];
    
    // Field worker specific data
    $myShipments = $db->select("SELECT * FROM shipments WHERE assigned_to = ? ORDER BY estimated_delivery ASC LIMIT 5", [$currentUser['full_name']]);
    $todaySchedule = $db->select("SELECT * FROM shipments WHERE assigned_to = ? AND DATE(estimated_delivery) = CURDATE() ORDER BY estimated_delivery ASC", [$currentUser['full_name']]);
    
} elseif ($userRole === 'hospital_staff') {
    // Hospital staff specific statistics
    $stats['low_stock'] = $db->selectOne("SELECT COUNT(*) as count FROM medical_items WHERE quantity < 10")['count'];
    $stats['expiring_soon'] = $db->selectOne("SELECT COUNT(*) as count FROM medical_items WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")['count'];
    $stats['incoming_shipments'] = $db->selectOne("SELECT COUNT(*) as count FROM shipments WHERE destination LIKE '%hospital%' OR destination LIKE '%clinic%' AND status IN ('preparing', 'in_transit')")['count'];
    $stats['total_inventory'] = $db->selectOne("SELECT COUNT(*) as count FROM medical_items")['count'];
    
    // Hospital specific data
    $lowStockItems = $db->select("SELECT * FROM medical_items WHERE quantity < 10 ORDER BY quantity ASC LIMIT 5");
    $expiringItems = $db->select("SELECT * FROM medical_items WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY expiry_date ASC LIMIT 5");
    $incomingShipments = $db->select("SELECT * FROM shipments WHERE destination LIKE '%hospital%' OR destination LIKE '%clinic%' AND status IN ('preparing', 'in_transit') ORDER BY estimated_delivery ASC LIMIT 5");
    
} elseif ($userRole === 'pharma_company') {
    // Pharmaceutical company specific statistics
    $stats['total_products'] = $db->selectOne("SELECT COUNT(*) as count FROM medical_items")['count'];
    $stats['expiring_batches'] = $db->selectOne("SELECT COUNT(*) as count FROM medical_items WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)")['count'];
    $stats['low_stock_products'] = $db->selectOne("SELECT COUNT(*) as count FROM medical_items WHERE quantity < 20")['count'];
    $stats['active_shipments'] = $db->selectOne("SELECT COUNT(*) as count FROM shipments WHERE status IN ('preparing', 'in_transit')")['count'];
    
    // Pharma specific data
    $expiringBatches = $db->select("SELECT * FROM medical_items WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) ORDER BY expiry_date ASC LIMIT 5");
    $lowStockProducts = $db->select("SELECT * FROM medical_items WHERE quantity < 20 ORDER BY quantity ASC LIMIT 5");
    $activeShipments = $db->select("SELECT * FROM shipments WHERE status IN ('preparing', 'in_transit') ORDER BY created_at DESC LIMIT 5");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS (OpenStreetMap) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .stat-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .map-container {
            height: 400px;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .alert-item {
            border-left: 4px solid;
            transition: all 0.2s ease;
        }
        .alert-item:hover {
            transform: translateX(5px);
        }
        .alert-urgent { border-left-color: #dc3545; }
        .alert-warning { border-left-color: #ffc107; }
        .alert-info { border-left-color: #17a2b8; }
        .quick-action-btn {
            padding: 1rem;
            border-radius: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .quick-action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">🏥 PHELS</h4>
                        <small class="text-white-50">Emergency Logistics</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                📊 Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="inventory.php">
                                📦 Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="storage.php">
                                🏭 Storage
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="shipments.php">
                                🚚 Shipments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="alerts.php">
                                ⚠️ Alerts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="operations.php">
                                🚨 Operations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="chat.php">
                                💬 Real-Time Chat
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <?php if ($userRole === 'emergency_manager'): ?>
                            Emergency Manager Dashboard
                        <?php elseif ($userRole === 'field_worker'): ?>
                            Field Worker Dashboard
                        <?php elseif ($userRole === 'hospital_staff'): ?>
                            Hospital Staff Dashboard
                        <?php elseif ($userRole === 'pharma_company'): ?>
                            Pharmaceutical Dashboard
                        <?php endif; ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="btn btn-sm btn-outline-secondary">Welcome, <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($userRole === 'emergency_manager'): ?>
                    <!-- Emergency Manager Dashboard -->
                    <?php include 'dashboard_emergency_manager.php'; ?>
                    
                <?php elseif ($userRole === 'field_worker'): ?>
                    <!-- Field Worker Dashboard -->
                    <?php include 'dashboard_field_worker.php'; ?>
                    
                <?php elseif ($userRole === 'hospital_staff'): ?>
                    <!-- Hospital Staff Dashboard -->
                    <?php include 'dashboard_hospital.php'; ?>
                    
                <?php elseif ($userRole === 'pharma_company'): ?>
                    <!-- Pharmaceutical Company Dashboard -->
                    <?php include 'dashboard_pharma.php'; ?>
                    
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS (OpenStreetMap) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Initialize Chart.js for all dashboards
        const ctx = document.getElementById('inventoryChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Vaccines', 'Medications', 'Medical Supplies', 'PPE'],
                    datasets: [{
                        data: [30, 25, 20, 25],
                        backgroundColor: [
                            '#FF6384',
                            '#36A2EB',
                            '#FFCE56',
                            '#4BC0C0'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
