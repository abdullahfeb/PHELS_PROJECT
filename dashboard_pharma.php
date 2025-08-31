<!-- Pharmaceutical Company Dashboard - Focused on Supply Chain -->

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary me-3">
                    📦
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['total_products']); ?></h6>
                    <small class="text-muted">Total Products</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning me-3">
                    ⏰
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['expiring_batches']); ?></h6>
                    <small class="text-muted">Expiring Batches</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-danger me-3">
                    ⚠️
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['low_stock_products']); ?></h6>
                    <small class="text-muted">Low Stock Products</small>
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
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🏭 Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-primary text-white" onclick="window.location.href='inventory.php'">
                            <h4>📋</h4>
                            <strong>Manage Inventory</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-success text-white" onclick="window.location.href='shipments.php'">
                            <h4>🚚</h4>
                            <strong>Track Shipments</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-info text-white" onclick="window.location.href='alerts.php'">
                            <h4>⚠️</h4>
                            <strong>View Alerts</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-warning text-white" onclick="generateReport()">
                            <h4>📊</h4>
                            <strong>Generate Report</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn text-white" style="background-color: #6f42c1 !important;" onclick="window.location.href='pharma_orders.php'">
                            <h4>📋</h4>
                            <strong>Manage Orders</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Expiring Batches -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">⏰ Expiring Batches</h5>
                <a href="inventory.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($expiringBatches)): ?>
                    <p class="text-muted text-center">No batches expiring soon</p>
                <?php else: ?>
                    <?php foreach ($expiringBatches as $item): ?>
                        <div class="p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="mb-1 small">
                                        <strong>Batch:</strong> <?php echo htmlspecialchars($item['batch_no']); ?>
                                    </p>
                                    <p class="mb-1 small">
                                        <strong>Expires:</strong> <?php echo date('M j, Y', strtotime($item['expiry_date'])); ?>
                                    </p>
                                    <p class="mb-1 small">
                                        <strong>Quantity:</strong> <?php echo $item['quantity']; ?> units
                                    </p>
                                </div>
                                <div class="text-end">
                                    <?php 
                                    $daysUntilExpiry = (strtotime($item['expiry_date']) - time()) / (60 * 60 * 24);
                                    $badgeClass = $daysUntilExpiry <= 30 ? 'bg-danger' : ($daysUntilExpiry <= 60 ? 'bg-warning' : 'bg-info');
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> mb-2">
                                        <?php echo round($daysUntilExpiry); ?> days
                                    </span>
                                    <br>
                                    <button class="btn btn-sm btn-outline-warning" onclick="manageBatch(<?php echo $item['id']; ?>)">
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">⚠️ Low Stock Products</h5>
                <a href="inventory.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($lowStockProducts)): ?>
                    <p class="text-muted text-center">All products are well stocked</p>
                <?php else: ?>
                    <?php foreach ($lowStockProducts as $item): ?>
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
                                    <p class="mb-1 small">
                                        <strong>Manufacturer:</strong> <?php echo htmlspecialchars($item['manufacturer']); ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-danger mb-2">Low Stock</span>
                                    <br>
                                    <button class="btn btn-sm btn-outline-primary" onclick="reorderProduct(<?php echo $item['id']; ?>)">
                                        Reorder
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

<!-- Active Shipments -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">🚚 Active Shipments</h5>
                <a href="shipments.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($activeShipments)): ?>
                    <p class="text-muted text-center">No active shipments</p>
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
                                <?php foreach ($activeShipments as $shipment): ?>
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

<!-- Supply Chain Overview -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🔗 Supply Chain Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>🏭</h4>
                            <strong>Manufacturing</strong><br>
                            <small class="text-muted">Production status</small><br>
                            <span class="badge bg-success">Active</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>📦</h4>
                            <strong>Warehousing</strong><br>
                            <small class="text-muted">Storage capacity</small><br>
                            <span class="badge bg-info">85% Full</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>🚚</h4>
                            <strong>Distribution</strong><br>
                            <small class="text-muted">Delivery network</small><br>
                            <span class="badge bg-primary">24/7</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>🏥</h4>
                            <strong>End Users</strong><br>
                            <small class="text-muted">Healthcare facilities</small><br>
                            <span class="badge bg-warning">150+</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quality Control Metrics -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🔬 Quality Control Metrics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>✅</h4>
                            <strong>Quality Score</strong><br>
                            <h3 class="text-success">98.5%</h3>
                            <small class="text-muted">Above industry standard</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>📊</h4>
                            <strong>Compliance Rate</strong><br>
                            <h3 class="text-primary">99.2%</h3>
                            <small class="text-muted">Regulatory compliance</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>⏱️</h4>
                            <strong>Delivery Time</strong><br>
                            <h3 class="text-info">2.3 days</h3>
                            <small class="text-muted">Average delivery</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport() {
    // Redirect to operations page to generate business report
    window.location.href = 'operations.php?action=generate_report';
}

function manageBatch(itemId) {
    // Redirect to inventory page to manage specific batch
    window.location.href = 'inventory.php?item_id=' + itemId + '&action=manage_batch';
}

function reorderProduct(itemId) {
    // Redirect to operations page to log reorder request
    window.location.href = 'operations.php?action=reorder_product&item_id=' + itemId;
}

function trackShipment(shipmentId) {
    // Redirect to shipments page to track specific shipment
    window.location.href = 'shipments.php?shipment_id=' + shipmentId;
}

// Add any pharmaceutical company specific functionality here
document.addEventListener('DOMContentLoaded', function() {
    console.log('Pharmaceutical Company Dashboard loaded');
});
</script>
