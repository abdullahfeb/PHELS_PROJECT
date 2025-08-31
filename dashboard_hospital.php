<!-- Hospital Staff Dashboard - Focused on Medical Supplies -->

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger me-3">
                    ⚠️
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['low_stock']); ?></h6>
                    <small class="text-muted">Low Stock Items</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning me-3">
                    📅
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['expiring_soon']); ?></h6>
                    <small class="text-muted">Expiring Soon</small>
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
                    <h6 class="mb-0"><?php echo number_format($stats['incoming_shipments']); ?></h6>
                    <small class="text-muted">Incoming Shipments</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info me-3">
                    📦
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['total_inventory']); ?></h6>
                    <small class="text-muted">Total Inventory</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🏥 Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-primary text-white" onclick="window.location.href='inventory.php'">
                            <h4>📋</h4>
                            <strong>Check Inventory</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-success text-white" onclick="window.location.href='alerts.php'">
                            <h4>⚠️</h4>
                            <strong>View Alerts</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-info text-white" onclick="window.location.href='shipments.php'">
                            <h4>🚚</h4>
                            <strong>Track Shipments</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-warning text-white" onclick="requestSupplies()">
                            <h4>📝</h4>
                            <strong>Request Supplies</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn text-white" style="background-color: #6f42c1 !important;" onclick="window.location.href='hospital_orders.php'">
                            <h4>🛒</h4>
                            <strong>Order Products</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Low Stock Items -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">⚠️ Low Stock Items</h5>
                <a href="inventory.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($lowStockItems)): ?>
                    <p class="text-muted text-center">All items are well stocked</p>
                <?php else: ?>
                    <?php foreach ($lowStockItems as $item): ?>
                        <div class="p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="mb-1 small">
                                        <strong>Current Stock:</strong> <?php echo $item['quantity']; ?> units
                                    </p>
                                    <p class="mb-1 small">
                                        <strong>Type:</strong> <?php echo htmlspecialchars($item['type']); ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger mb-2">Low Stock</span>
                                    <br>
                                    <button class="btn btn-sm btn-outline-warning" onclick="requestItem(<?php echo $item['id']; ?>)">
                                        Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Expiring Items -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📅 Expiring Soon</h5>
                <a href="inventory.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($expiringItems)): ?>
                    <p class="text-muted text-center">No items expiring soon</p>
                <?php else: ?>
                    <?php foreach ($expiringItems as $item): ?>
                        <div class="p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="mb-1 small">
                                        <strong>Expires:</strong> <?php echo date('M j, Y', strtotime($item['expiry_date'])); ?>
                                    </p>
                                    <p class="mb-1 small">
                                        <strong>Batch:</strong> <?php echo htmlspecialchars($item['batch_no']); ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <?php 
                                    $daysUntilExpiry = (strtotime($item['expiry_date']) - time()) / (60 * 60 * 24);
                                    $badgeClass = $daysUntilExpiry <= 7 ? 'bg-danger' : 'bg-warning';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> mb-2">
                                        <?php echo round($daysUntilExpiry); ?> days
                                    </span>
                                    <br>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewItem(<?php echo $item['id']; ?>)">
                                        Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Incoming Shipments -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">🚚 Incoming Shipments</h5>
                <a href="shipments.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($incomingShipments)): ?>
                    <p class="text-muted text-center">No incoming shipments</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Origin</th>
                                    <th>Destination</th>
                                    <th>ETA</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incomingShipments as $shipment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($shipment['origin']); ?></td>
                                        <td><?php echo htmlspecialchars($shipment['destination']); ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($shipment['estimated_delivery'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $shipment['status'] === 'in_transit' ? 'warning' : 'info'; ?>">
                                                <?php echo ucfirst($shipment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="trackShipment(<?php echo $shipment['id']; ?>)">
                                                Track
                                            </button>
                                        </td>
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

<!-- Medical Supply Categories -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🏥 Medical Supply Categories</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>💉</h4>
                            <strong>Vaccines</strong><br>
                            <small class="text-muted">Immunization supplies</small><br>
                            <span class="badge bg-primary">View</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>💊</h4>
                            <strong>Medications</strong><br>
                            <small class="text-muted">Prescription drugs</small><br>
                            <span class="badge bg-success">View</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>🩺</h4>
                            <strong>Medical Supplies</strong><br>
                            <small class="text-muted">Equipment & tools</small><br>
                            <span class="badge bg-info">View</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>🩹</h4>
                            <strong>PPE</strong><br>
                            <small class="text-muted">Protective equipment</small><br>
                            <span class="badge bg-warning">View</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function requestSupplies() {
    // Redirect to operations page to log supply request
    window.location.href = 'operations.php?action=request_supplies';
}

function requestItem(itemId) {
    // Redirect to operations page with specific item request
    window.location.href = 'operations.php?action=request_item&item_id=' + itemId;
}

function viewItem(itemId) {
    // Redirect to inventory page to view item details
    window.location.href = 'inventory.php?item_id=' + itemId;
}

function trackShipment(shipmentId) {
    // Redirect to shipments page to track specific shipment
    window.location.href = 'shipments.php?shipment_id=' + shipmentId;
}

// Add any hospital staff specific functionality here
document.addEventListener('DOMContentLoaded', function() {
    console.log('Hospital Staff Dashboard loaded');
});
</script>
