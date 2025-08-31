<?php
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

// Handle alert resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_alert'])) {
    $sql = "UPDATE alerts SET is_resolved = 1 WHERE id = ?";
    $db->update($sql, [$_POST['alert_id']]);
    $success = "Alert resolved successfully!";
}

// Get all alerts with filtering
$type_filter = $_GET['type'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_conditions = [];
$params = [];

if ($type_filter) {
    $where_conditions[] = "type = ?";
    $params[] = $type_filter;
}

if ($priority_filter) {
    $where_conditions[] = "priority = ?";
    $params[] = $priority_filter;
}

if ($status_filter === 'resolved') {
    $where_conditions[] = "is_resolved = 1";
} elseif ($status_filter === 'pending') {
    $where_conditions[] = "is_resolved = 0";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$sql = "SELECT * FROM alerts $where_clause ORDER BY created_at DESC";
$alerts = $db->select($sql, $params);

// Get unique types and priorities for filters
$types = $db->select("SELECT DISTINCT type FROM alerts ORDER BY type");
$priorities = $db->select("SELECT DISTINCT priority FROM alerts ORDER BY priority");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts Management - PHELS</title>
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
        .alert-item {
            border-left: 4px solid #dc3545;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .alert-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .alert-item.expiry { border-left-color: #ffc107; }
        .alert-item.storage { border-left-color: #17a2b8; }
        .alert-item.shipment { border-left-color: #6f42c1; }
        .alert-item.system { border-left-color: #6c757d; }
        .priority-urgent { border-left-width: 8px; }
        .priority-high { border-left-width: 6px; }
        .priority-medium { border-left-width: 4px; }
        .priority-low { border-left-width: 2px; }
        .resolved {
            opacity: 0.6;
            background: #f8f9fa;
        }
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
                        <a class="nav-link active" href="alerts.php">
                            <i class="fas fa-exclamation-triangle"></i> Alerts
                        </a>
                        <a class="nav-link" href="operations.php">
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
                            <span class="navbar-brand">Alerts Management</span>
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

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>System Alerts</h2>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary" onclick="refreshAlerts()">
                                    <i class="fas fa-sync-alt me-2"></i>Refresh
                                </button>
                                <button class="btn btn-success" onclick="generateTestAlerts()">
                                    <i class="fas fa-plus me-2"></i>Generate Test Alerts
                                </button>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <select class="form-select" name="type">
                                            <option value="">All Types</option>
                                            <?php foreach ($types as $type): ?>
                                                <option value="<?php echo htmlspecialchars($type['type']); ?>" 
                                                        <?php echo $type_filter === $type['type'] ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($type['type']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="priority">
                                            <option value="">All Priorities</option>
                                            <?php foreach ($priorities as $priority): ?>
                                                <option value="<?php echo htmlspecialchars($priority['priority']); ?>" 
                                                        <?php echo $priority_filter === $priority['priority'] ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($priority['priority']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="status">
                                            <option value="">All Status</option>
                                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-filter me-2"></i>Apply Filters
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Alerts List -->
                        <div class="card">
                            <div class="card-body">
                                <?php if (empty($alerts)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <h4>No Alerts Found</h4>
                                        <p class="text-muted">All systems are operating normally</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($alerts as $alert): ?>
                                        <div class="alert-item <?php echo $alert['type']; ?> priority-<?php echo $alert['priority']; ?> <?php echo $alert['is_resolved'] ? 'resolved' : ''; ?>">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="d-flex align-items-start mb-2">
                                                        <div class="me-3">
                                                            <?php
                                                            $icon = 'exclamation-triangle';
                                                            $color = 'text-warning';
                                                            if ($alert['type'] === 'expiry') {
                                                                $icon = 'clock';
                                                                $color = 'text-danger';
                                                            } elseif ($alert['type'] === 'storage') {
                                                                $icon = 'thermometer-half';
                                                                $color = 'text-info';
                                                            } elseif ($alert['type'] === 'shipment') {
                                                                $icon = 'truck';
                                                                $color = 'text-primary';
                                                            }
                                                            ?>
                                                            <i class="fas fa-<?php echo $icon; ?> fa-2x <?php echo $color; ?>"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h5 class="mb-1"><?php echo htmlspecialchars($alert['title']); ?></h5>
                                                            <p class="mb-2"><?php echo htmlspecialchars($alert['description']); ?></p>
                                                            <div class="d-flex gap-3">
                                                                <span class="badge bg-<?php echo $alert['priority'] === 'urgent' ? 'danger' : ($alert['priority'] === 'high' ? 'warning' : ($alert['priority'] === 'medium' ? 'info' : 'secondary')); ?>">
                                                                    <?php echo ucfirst($alert['priority']); ?> Priority
                                                                </span>
                                                                <span class="badge bg-secondary"><?php echo ucfirst($alert['type']); ?></span>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-clock me-1"></i>
                                                                    <?php echo date('M j, Y H:i', strtotime($alert['created_at'])); ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <?php if (!$alert['is_resolved']): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="resolve_alert" value="1">
                                                            <input type="hidden" name="alert_id" value="<?php echo $alert['id']; ?>">
                                                            <button type="submit" class="btn btn-success btn-sm">
                                                                <i class="fas fa-check me-2"></i>Resolve
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Resolved
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function refreshAlerts() {
            location.reload();
        }

        function generateTestAlerts() {
            // This would call an AJAX endpoint to generate test alerts
            alert('Test alert generation would happen here');
        }

        // Auto-refresh alerts every 30 seconds
        setInterval(() => {
            console.log('Refreshing alerts...');
        }, 30000);
    </script>
</body>
</html>
