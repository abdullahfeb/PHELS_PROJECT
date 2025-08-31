<!-- Emergency Manager Dashboard - Full System Overview -->

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary me-3">
                    📦
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['total_items']); ?></h6>
                    <small class="text-muted">Total Inventory</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success me-3">
                    🚚
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['active_shipments']); ?></h6>
                    <small class="text-muted">Active Shipments</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info me-3">
                    🏭
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['total_storage']); ?></h6>
                    <small class="text-muted">Storage Units</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning me-3">
                    ⚠️
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['pending_alerts']); ?></h6>
                    <small class="text-muted">Pending Alerts</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Map and Charts -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Storage Units Location</h5>
            </div>
            <div class="card-body">
                <div id="map" class="map-container"></div>
            </div>
        </div>
    </div>

    <!-- Inventory Chart -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Inventory Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="inventoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Alerts -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Alerts</h5>
                <a href="alerts.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentAlerts)): ?>
                    <p class="text-muted text-center">No recent alerts</p>
                <?php else: ?>
                    <?php foreach (array_slice($recentAlerts, 0, 5) as $alert): ?>
                        <div class="alert-item p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($alert['title']); ?></h6>
                                    <p class="mb-1 small"><?php echo htmlspecialchars($alert['description']); ?></p>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y g:i A', strtotime($alert['created_at'])); ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?php echo $alert['priority'] === 'urgent' ? 'danger' : ($alert['priority'] === 'warning' ? 'warning' : 'info'); ?>">
                                    <?php echo ucfirst($alert['priority']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Shipments -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Shipments</h5>
                <a href="shipments.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentShipments)): ?>
                    <p class="text-muted text-center">No recent shipments</p>
                <?php else: ?>
                    <?php foreach (array_slice($recentShipments, 0, 5) as $shipment): ?>
                        <div class="p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($shipment['origin']); ?> → <?php echo htmlspecialchars($shipment['destination']); ?></h6>
                                    <p class="mb-1 small">Status: <?php echo ucfirst($shipment['status']); ?></p>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y g:i A', strtotime($shipment['created_at'])); ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?php echo $shipment['status'] === 'in_transit' ? 'warning' : ($shipment['status'] === 'delivered' ? 'success' : 'secondary'); ?>">
                                    <?php echo ucfirst($shipment['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Actions -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Actions</h5>
                <a href="operations.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentActions)): ?>
                    <p class="text-muted text-center">No recent actions</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>User</th>
                                    <th>Priority</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentActions, 0, 5) as $action): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($action['title']); ?></td>
                                        <td><?php echo htmlspecialchars($action['full_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $action['priority'] === 'high' ? 'danger' : ($action['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                                <?php echo ucfirst($action['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($action['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Leaflet Map with OpenStreetMap for Emergency Manager
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map').setView([14.5995, 120.9842], 10); // Manila coordinates
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add sample storage unit markers (you can replace with real data)
    const sampleUnits = [
        { lat: 14.5995, lng: 120.9842, name: 'Central Warehouse', status: 'normal' },
        { lat: 14.5547, lng: 121.0244, name: 'North Storage', status: 'warning' },
        { lat: 14.6347, lng: 120.9708, name: 'South Facility', status: 'critical' }
    ];

    sampleUnits.forEach(unit => {
        const color = unit.status === 'normal' ? 'green' : (unit.status === 'warning' ? 'orange' : 'red');
        const marker = L.marker([unit.lat, unit.lng])
            .addTo(map)
            .bindPopup(`<b>${unit.name}</b><br>Status: ${unit.status}`);
        
        // Custom marker icon based on status
        marker.setIcon(L.divIcon({
            className: 'custom-marker',
            html: `<div style="background-color: ${color}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white;"></div>`,
            iconSize: [20, 20]
        }));
    });
});
</script>
