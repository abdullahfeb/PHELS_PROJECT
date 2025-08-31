<?php
require_once 'config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

// Get storage units with enhanced dummy data
$storageUnits = $db->select("SELECT * FROM storage_units ORDER BY name");

// Generate realistic dummy sensor data
function generateDummySensorData() {
    $data = [];
    
    // Central Warehouse - Refrigerated storage
    $data[] = [
        'id' => 1,
        'name' => 'Central Warehouse',
        'location' => 'Manila Central District',
        'latitude' => 14.5995,
        'longitude' => 120.9842,
        'temperature' => rand(2, 8), // Refrigerated range
        'humidity' => rand(45, 65),
        'status' => 'normal',
        'last_update' => date('Y-m-d H:i:s'),
        'capacity' => '85%',
        'items_stored' => 'COVID-19 Vaccines, Insulin, Blood Products',
        'maintenance_due' => '2024-02-15'
    ];
    
    // North Storage - Climate controlled
    $data[] = [
        'id' => 2,
        'name' => 'North Storage',
        'location' => 'Quezon City',
        'latitude' => 14.5547,
        'longitude' => 121.0244,
        'temperature' => rand(18, 25), // Room temperature
        'humidity' => rand(50, 70),
        'status' => 'normal',
        'last_update' => date('Y-m-d H:i:s'),
        'capacity' => '72%',
        'items_stored' => 'PPE, Medical Supplies, Medications',
        'maintenance_due' => '2024-01-30'
    ];
    
    // South Facility - Freezer storage
    $data[] = [
        'id' => 3,
        'name' => 'South Facility',
        'location' => 'Makati City',
        'latitude' => 14.6347,
        'longitude' => 120.9708,
        'temperature' => rand(-25, -15), // Freezer range
        'humidity' => rand(20, 40),
        'status' => 'warning', // Simulate a warning
        'last_update' => date('Y-m-d H:i:s'),
        'capacity' => '95%',
        'items_stored' => 'Frozen Vaccines, Plasma, Special Medications',
        'maintenance_due' => '2024-02-01'
    ];
    
    // Emergency Response Center
    $data[] = [
        'id' => 4,
        'name' => 'Emergency Response Center',
        'location' => 'Pasig City',
        'latitude' => 14.5764,
        'longitude' => 121.0851,
        'temperature' => rand(20, 28),
        'humidity' => rand(55, 75),
        'status' => 'normal',
        'last_update' => date('Y-m-d H:i:s'),
        'capacity' => '45%',
        'items_stored' => 'Emergency Kits, First Aid, Trauma Supplies',
        'maintenance_due' => '2024-03-01'
    ];
    
    // Regional Distribution Hub
    $data[] = [
        'id' => 5,
        'name' => 'Regional Distribution Hub',
        'location' => 'Caloocan City',
        'latitude' => 14.6546,
        'longitude' => 120.9842,
        'temperature' => rand(22, 26),
        'humidity' => rand(60, 80),
        'status' => 'critical', // Simulate critical alert
        'last_update' => date('Y-m-d H:i:s'),
        'capacity' => '98%',
        'items_stored' => 'Bulk Medications, Surgical Supplies, Equipment',
        'maintenance_due' => '2024-01-20'
    ];
    
    return $data;
}

$sensorData = generateDummySensorData();

// Get recent storage logs
$recentLogs = $db->select("SELECT sl.*, su.name as unit_name FROM storage_logs sl 
                          JOIN storage_units su ON sl.storage_unit_id = su.id 
                          ORDER BY sl.created_at DESC LIMIT 20");

// Generate additional dummy logs if needed
if (count($recentLogs) < 20) {
    $dummyLogs = [];
    foreach ($sensorData as $unit) {
        for ($i = 0; $i < 4; $i++) {
            $dummyLogs[] = [
                'unit_name' => $unit['name'],
                'temperature' => $unit['temperature'] + rand(-2, 2),
                'humidity' => $unit['humidity'] + rand(-5, 5),
                'status' => $unit['status'],
                'created_at' => date('Y-m-d H:i:s', time() - ($i * 3600))
            ];
        }
    }
    $recentLogs = array_merge($recentLogs, $dummyLogs);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Monitoring - PHELS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .storage-card {
            transition: all 0.3s ease;
            border-left: 4px solid #28a745;
        }
        .storage-card.warning {
            border-left-color: #ffc107;
        }
        .storage-card.critical {
            border-left-color: #dc3545;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        .sensor-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .map-container {
            height: 400px;
            border-radius: 1rem;
            overflow: hidden;
        }
        .log-item {
            border-left: 3px solid #dee2e6;
            padding-left: 1rem;
            margin-bottom: 0.5rem;
        }
        .log-item.warning { border-left-color: #ffc107; }
        .log-item.critical { border-left-color: #dc3545; }
        .log-item.normal { border-left-color: #28a745; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">🏥 PHELS</h4>
                        <small class="text-white-50">Storage Monitoring</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="dashboard.php">
                                📊 Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="inventory.php">
                                📦 Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="storage.php">
                                🏭 Storage
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="shipments.php">
                                🚚 Shipments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="alerts.php">
                                ⚠️ Alerts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="operations.php">
                                🚨 Operations
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">🏭 Storage Monitoring Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshData()">
                                🔄 Refresh Data
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Storage Units Overview -->
                <div class="row mb-4">
                    <?php foreach ($sensorData as $unit): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card storage-card <?php echo $unit['status']; ?> h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($unit['name']); ?></h6>
                                        <span class="badge bg-<?php echo $unit['status'] === 'normal' ? 'success' : ($unit['status'] === 'warning' ? 'warning' : 'danger'); ?> status-badge">
                                            <?php echo ucfirst($unit['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($unit['location']); ?></p>
                                    
                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <div class="sensor-value text-primary">
                                                <?php echo $unit['temperature']; ?>°C
                                            </div>
                                            <small class="text-muted">Temperature</small>
                                        </div>
                                        <div class="col-6">
                                            <div class="sensor-value text-info">
                                                <?php echo $unit['humidity']; ?>%
                                            </div>
                                            <small class="text-muted">Humidity</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Capacity: <strong><?php echo $unit['capacity']; ?></strong></small>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Items: <?php echo htmlspecialchars($unit['items_stored']); ?></small>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Maintenance: <?php echo $unit['maintenance_due']; ?></small>
                                    </div>
                                    
                                    <div class="text-end">
                                        <small class="text-muted">Last Update: <?php echo date('H:i', strtotime($unit['last_update'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Map and Logs Row -->
                <div class="row">
                    <!-- Storage Locations Map -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📍 Storage Facility Locations</h5>
                            </div>
                            <div class="card-body">
                                <div id="storageMap" class="map-container"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Logs -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📊 Recent Activity Logs</h5>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <?php foreach (array_slice($recentLogs, 0, 15) as $log): ?>
                                    <div class="log-item <?php echo $log['status'] ?? 'normal'; ?>">
                                        <div class="d-flex justify-content-between">
                                            <strong><?php echo htmlspecialchars($log['unit_name'] ?? 'Unknown Unit'); ?></strong>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($log['created_at'])); ?></small>
                                        </div>
                                        <div class="small">
                                            Temp: <?php echo $log['temperature']; ?>°C | 
                                            Humidity: <?php echo $log['humidity']; ?>% |
                                            Status: <span class="badge bg-<?php echo ($log['status'] ?? 'normal') === 'normal' ? 'success' : (($log['status'] ?? 'normal') === 'warning' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($log['status'] ?? 'normal'); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Storage Statistics -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📈 Storage Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-success"><?php echo count($sensorData); ?></h3>
                                            <p class="mb-0">Active Units</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-warning">2</h3>
                                            <p class="mb-0">Warning Alerts</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-danger">1</h3>
                                            <p class="mb-0">Critical Alerts</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3">
                                            <h3 class="text-info">79%</h3>
                                            <p class="mb-0">Average Capacity</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Initialize storage map
        const map = L.map('storageMap').setView([14.5995, 120.9842], 10);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add storage unit markers
        const storageUnits = <?php echo json_encode($sensorData); ?>;
        
        storageUnits.forEach(unit => {
            const markerColor = unit.status === 'normal' ? 'green' : (unit.status === 'warning' ? 'orange' : 'red');
            
            const marker = L.marker([unit.latitude, unit.longitude])
                .addTo(map)
                .bindPopup(`
                    <strong>${unit.name}</strong><br>
                    Status: ${unit.status}<br>
                    Temperature: ${unit.temperature}°C<br>
                    Humidity: ${unit.humidity}%<br>
                    Capacity: ${unit.capacity}
                `);
        });

        // Auto-refresh data every 30 seconds
        function refreshData() {
            location.reload();
        }

        // Simulate real-time updates
        setInterval(() => {
            // Update timestamps
            document.querySelectorAll('.text-muted:last-child').forEach(el => {
                if (el.textContent.includes('Last Update:')) {
                    const now = new Date();
                    el.textContent = `Last Update: ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
                }
            });
        }, 30000);
    </script>
</body>
</html>
