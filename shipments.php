<?php
require_once 'config.php';
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

// Generate realistic dummy shipment data
function generateDummyShipments() {
    $shipments = [
        [
            'id' => 1,
            'origin' => 'Central Warehouse, Manila',
            'destination' => 'Manila General Hospital',
            'status' => 'in_transit',
            'assigned_to' => 'Field Worker 1',
            'estimated_delivery' => '2024-01-15 14:00:00',
            'current_lat' => 14.5995,
            'current_lng' => 120.9842,
            'dest_lat' => 14.5895,
            'dest_lng' => 120.9742,
            'items' => ['COVID-19 Vaccines (100)', 'PPE Kits (50)', 'Medical Supplies (200)'],
            'priority' => 'high',
            'vehicle' => 'Refrigerated Van PH-1234',
            'driver' => 'Juan Dela Cruz',
            'phone' => '+63 912 345 6789',
            'eta' => '2 hours 15 min',
            'distance' => '8.5 km',
            'route' => 'Manila Central → EDSA → Hospital District'
        ],
        [
            'id' => 2,
            'origin' => 'North Storage, Quezon City',
            'destination' => 'Quezon Medical Center',
            'status' => 'preparing',
            'assigned_to' => 'Field Worker 2',
            'estimated_delivery' => '2024-01-16 10:00:00',
            'current_lat' => 14.5547,
            'current_lng' => 121.0244,
            'dest_lat' => 14.5447,
            'dest_lng' => 121.0144,
            'items' => ['N95 Masks (500)', 'Hand Sanitizers (200)', 'Syringes (1000)'],
            'priority' => 'medium',
            'vehicle' => 'Delivery Truck PH-5678',
            'driver' => 'Maria Santos',
            'phone' => '+63 923 456 7890',
            'eta' => '1 hour 45 min',
            'distance' => '6.2 km',
            'route' => 'Quezon City → Commonwealth → Medical Center'
        ],
        [
            'id' => 3,
            'origin' => 'South Facility, Makati',
            'destination' => 'Makati Medical Center',
            'status' => 'pending',
            'assigned_to' => 'Field Worker 3',
            'estimated_delivery' => '2024-01-17 16:00:00',
            'current_lat' => 14.6347,
            'current_lng' => 120.9708,
            'dest_lat' => 14.6247,
            'dest_lng' => 120.9608,
            'items' => ['Frozen Vaccines (200)', 'Plasma Units (50)', 'Surgical Equipment (100)'],
            'priority' => 'urgent',
            'vehicle' => 'Freezer Truck PH-9012',
            'driver' => 'Pedro Martinez',
            'phone' => '+63 934 567 8901',
            'eta' => '3 hours 30 min',
            'distance' => '12.8 km',
            'route' => 'Makati → SLEX → Medical District'
        ],
        [
            'id' => 4,
            'origin' => 'Emergency Response Center, Pasig',
            'destination' => 'Pasig City Hospital',
            'status' => 'in_transit',
            'assigned_to' => 'Field Worker 4',
            'estimated_delivery' => '2024-01-15 11:30:00',
            'current_lat' => 14.5764,
            'current_lng' => 121.0851,
            'dest_lat' => 14.5664,
            'dest_lng' => 121.0751,
            'items' => ['Emergency Kits (25)', 'First Aid Supplies (100)', 'Trauma Kits (50)'],
            'priority' => 'critical',
            'vehicle' => 'Emergency Van PH-3456',
            'driver' => 'Ana Reyes',
            'phone' => '+63 945 678 9012',
            'eta' => '45 min',
            'distance' => '3.1 km',
            'route' => 'Emergency Center → Ortigas → Hospital'
        ],
        [
            'id' => 5,
            'origin' => 'Regional Distribution Hub, Caloocan',
            'destination' => 'Caloocan Medical Center',
            'status' => 'delivered',
            'assigned_to' => 'Field Worker 5',
            'estimated_delivery' => '2024-01-14 09:00:00',
            'current_lat' => 14.6546,
            'current_lng' => 120.9842,
            'dest_lat' => 14.6446,
            'dest_lng' => 120.9742,
            'items' => ['Bulk Medications (1000)', 'Surgical Supplies (500)', 'Medical Equipment (200)'],
            'priority' => 'medium',
            'vehicle' => 'Large Truck PH-7890',
            'driver' => 'Carlos Lopez',
            'phone' => '+63 956 789 0123',
            'eta' => 'Delivered',
            'distance' => '5.7 km',
            'route' => 'Distribution Hub → Main Road → Medical Center'
        ]
    ];
    
    return $shipments;
}

$shipments = generateDummyShipments();

// Get active shipments for tracking
$activeShipments = array_filter($shipments, function($s) {
    return in_array($s['status'], ['preparing', 'in_transit']);
});

// Get completed shipments
$completedShipments = array_filter($shipments, function($s) {
    return $s['status'] === 'delivered';
});

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_shipment':
                // Simulate creating a new shipment
                $success = "New shipment created successfully! Tracking ID: SH" . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                break;
                
            case 'update_status':
                $shipmentId = $_POST['shipment_id'];
                $newStatus = $_POST['new_status'];
                // In a real system, this would update the database
                $success = "Shipment #$shipmentId status updated to: $newStatus";
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Tracking - PHELS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .shipment-card {
            transition: all 0.3s ease;
            border-left: 4px solid #28a745;
        }
        .shipment-card.preparing { border-left-color: #ffc107; }
        .shipment-card.in_transit { border-left-color: #17a2b8; }
        .shipment-card.delivered { border-left-color: #28a745; }
        .shipment-card.cancelled { border-left-color: #dc3545; }
        
        .priority-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        
        .map-container {
            height: 500px;
            border-radius: 1rem;
            overflow: hidden;
        }
        
        .tracking-info {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .route-info {
            background: #e9ecef;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.9rem;
        }
        
        .driver-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 0.5rem;
            padding: 0.75rem;
        }
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
                        <small class="text-white-50">Shipment Tracking</small>
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
                            <a class="nav-link text-white" href="storage.php">
                                🏭 Storage
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="shipments.php">
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
                    <h1 class="h2">🚚 Shipment Tracking & Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshTracking()">
                                🔄 Refresh Tracking
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createShipmentModal">
                                ➕ New Shipment
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Live Tracking Map -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">📍 Live Shipment Tracking</h5>
                            </div>
                            <div class="card-body">
                                <div id="trackingMap" class="map-container"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Shipments -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">🚛 Active Shipments (<?php echo count($activeShipments); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($activeShipments as $shipment): ?>
                                        <div class="col-lg-6 mb-3">
                                            <div class="card shipment-card <?php echo $shipment['status']; ?> h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">Shipment #<?php echo $shipment['id']; ?></h6>
                                                        <div>
                                                            <span class="badge bg-<?php echo $shipment['priority'] === 'urgent' ? 'danger' : ($shipment['priority'] === 'high' ? 'warning' : 'info'); ?> priority-badge me-1">
                                                                <?php echo ucfirst($shipment['priority']); ?>
                                                            </span>
                                                            <span class="badge bg-<?php echo $shipment['status'] === 'preparing' ? 'warning' : 'info'; ?> status-badge">
                                                                <?php echo ucfirst(str_replace('_', ' ', $shipment['status'])); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="tracking-info">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <strong>From:</strong><br>
                                                                <small><?php echo htmlspecialchars($shipment['origin']); ?></small>
                                                            </div>
                                                            <div class="col-6">
                                                                <strong>To:</strong><br>
                                                                <small><?php echo htmlspecialchars($shipment['destination']); ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="route-info mb-2">
                                                        <strong>Route:</strong> <?php echo htmlspecialchars($shipment['route']); ?><br>
                                                        <strong>Distance:</strong> <?php echo $shipment['distance']; ?> | 
                                                        <strong>ETA:</strong> <?php echo $shipment['eta']; ?>
                                                    </div>
                                                    
                                                    <div class="driver-info mb-2">
                                                        <strong>Driver:</strong> <?php echo htmlspecialchars($shipment['driver']); ?><br>
                                                        <strong>Vehicle:</strong> <?php echo htmlspecialchars($shipment['vehicle']); ?><br>
                                                        <strong>Contact:</strong> <?php echo htmlspecialchars($shipment['phone']); ?>
                                                    </div>
                                                    
                                                    <div class="mb-2">
                                                        <strong>Items:</strong><br>
                                                        <small><?php echo implode(', ', array_map('htmlspecialchars', $shipment['items'])); ?></small>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">Est. Delivery: <?php echo date('M j, H:i', strtotime($shipment['estimated_delivery'])); ?></small>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="trackShipment(<?php echo $shipment['id']; ?>)">
                                                            📍 Track
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Shipments -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">✅ Completed Shipments (<?php echo count($completedShipments); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Origin</th>
                                                <th>Destination</th>
                                                <th>Driver</th>
                                                <th>Items</th>
                                                <th>Completed</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($completedShipments as $shipment): ?>
                                                <tr>
                                                    <td>#<?php echo $shipment['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($shipment['origin']); ?></td>
                                                    <td><?php echo htmlspecialchars($shipment['destination']); ?></td>
                                                    <td><?php echo htmlspecialchars($shipment['driver']); ?></td>
                                                    <td>
                                                        <small><?php echo implode(', ', array_map('htmlspecialchars', array_slice($shipment['items'], 0, 2))); ?>
                                                        <?php if (count($shipment['items']) > 2): ?>...<?php endif; ?></small>
                                                    </td>
                                                    <td><?php echo date('M j, H:i', strtotime($shipment['estimated_delivery'])); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-secondary" onclick="viewShipmentDetails(<?php echo $shipment['id']; ?>)">
                                                            👁️ View
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Shipment Modal -->
    <div class="modal fade" id="createShipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Shipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_shipment">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Origin *</label>
                                <select class="form-select" name="origin" required>
                                    <option value="">Select Origin</option>
                                    <option value="Central Warehouse, Manila">Central Warehouse, Manila</option>
                                    <option value="North Storage, Quezon City">North Storage, Quezon City</option>
                                    <option value="South Facility, Makati">South Facility, Makati</option>
                                    <option value="Emergency Response Center, Pasig">Emergency Response Center, Pasig</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Destination *</label>
                                <input type="text" class="form-control" name="destination" placeholder="Hospital/Clinic Name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority *</label>
                                <select class="form-select" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimated Delivery *</label>
                                <input type="datetime-local" class="form-control" name="estimated_delivery" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Items to Ship</label>
                            <textarea class="form-control" name="items" rows="3" placeholder="List items with quantities"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Shipment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Initialize tracking map
        const map = L.map('trackingMap').setView([14.5995, 120.9842], 10);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add shipment markers and routes
        const shipments = <?php echo json_encode($activeShipments); ?>;
        const markers = {};
        const routes = {};
        
        shipments.forEach(shipment => {
            // Origin marker
            const originMarker = L.marker([shipment.current_lat, shipment.current_lng])
                .addTo(map)
                .bindPopup(`
                    <strong>Origin: ${shipment.origin}</strong><br>
                    Shipment #${shipment.id}<br>
                    Status: ${shipment.status}<br>
                    Driver: ${shipment.driver}
                `);
            
            // Destination marker
            const destMarker = L.marker([shipment.dest_lat, shipment.dest_lng])
                .addTo(map)
                .bindPopup(`
                    <strong>Destination: ${shipment.destination}</strong><br>
                    Shipment #${shipment.id}<br>
                    ETA: ${shipment.eta}<br>
                    Priority: ${shipment.priority}
                `);
            
            // Route line
            const routeLine = L.polyline([
                [shipment.current_lat, shipment.current_lng],
                [shipment.dest_lat, shipment.dest_lng]
            ], {
                color: shipment.priority === 'urgent' ? 'red' : (shipment.priority === 'high' ? 'orange' : 'blue'),
                weight: 3,
                opacity: 0.7
            }).addTo(map);
            
            markers[shipment.id] = { origin: originMarker, destination: destMarker, route: routeLine };
        });

        // Simulate real-time tracking updates
        function refreshTracking() {
            // Simulate GPS updates
            shipments.forEach(shipment => {
                if (shipment.status === 'in_transit') {
                    // Move current position closer to destination
                    const progress = Math.random() * 0.3 + 0.1; // 10-40% progress
                    const newLat = shipment.current_lat + (shipment.dest_lat - shipment.current_lat) * progress;
                    const newLng = shipment.current_lng + (shipment.dest_lng - shipment.current_lng) * progress;
                    
                    if (markers[shipment.id]) {
                        markers[shipment.id].origin.setLatLng([newLat, newLng]);
                        markers[shipment.id].route.setLatLngs([
                            [newLat, newLng],
                            [shipment.dest_lat, shipment.dest_lng]
                        ]);
                    }
                }
            });
            
            // Update ETA
            document.querySelectorAll('.route-info').forEach(el => {
                if (el.textContent.includes('ETA:')) {
                    const currentEta = el.textContent.match(/ETA: (.*?)(?=\|)/);
                    if (currentEta) {
                        const newEta = Math.max(1, Math.floor(Math.random() * 4)) + ' hours ' + Math.floor(Math.random() * 60) + ' min';
                        el.innerHTML = el.innerHTML.replace(currentEta[1], newEta);
                    }
                }
            });
        }

        function trackShipment(shipmentId) {
            // Center map on specific shipment
            const shipment = shipments.find(s => s.id === shipmentId);
            if (shipment) {
                map.setView([shipment.current_lat, shipment.current_lng], 13);
                markers[shipmentId].origin.openPopup();
            }
        }

        function viewShipmentDetails(shipmentId) {
            alert(`Viewing details for Shipment #${shipmentId}\nThis would show complete shipment history and documentation.`);
        }

        // Auto-refresh tracking every 30 seconds
        setInterval(refreshTracking, 30000);
    </script>
</body>
</html>
