<!-- Field Worker Dashboard - Focused on Field Operations -->

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary me-3">
                    🚚
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['my_shipments']); ?></h6>
                    <small class="text-muted">My Active Shipments</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success me-3">
                    📅
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['today_deliveries']); ?></h6>
                    <small class="text-muted">Today's Deliveries</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning me-3">
                    ⏳
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['pending_tasks']); ?></h6>
                    <small class="text-muted">Pending Tasks</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info me-3">
                    ✅
                </div>
                <div>
                    <h6 class="mb-0"><?php echo number_format($stats['completed_today']); ?></h6>
                    <small class="text-muted">Completed Today</small>
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
                <h5 class="mb-0">🚀 Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-primary text-white" onclick="window.location.href='shipments.php'">
                            <h4>📋</h4>
                            <strong>View All Shipments</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-success text-white" onclick="window.location.href='operations.php'">
                            <h4>📝</h4>
                            <strong>Report Issue</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-info text-white" onclick="window.location.href='alerts.php'">
                            <h4>⚠️</h4>
                            <strong>Check Alerts</strong>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="quick-action-btn bg-warning text-white" onclick="updateStatus()">
                            <h4>🔄</h4>
                            <strong>Update Status</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Today's Schedule -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📅 Today's Schedule</h5>
            </div>
            <div class="card-body">
                <?php if (empty($todaySchedule)): ?>
                    <p class="text-muted text-center">No deliveries scheduled for today</p>
                <?php else: ?>
                    <?php foreach ($todaySchedule as $shipment): ?>
                        <div class="p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($shipment['origin']); ?> → <?php echo htmlspecialchars($shipment['destination']); ?></h6>
                                    <p class="mb-1 small">
                                        <strong>ETA:</strong> <?php echo date('g:i A', strtotime($shipment['estimated_delivery'])); ?>
                                    </p>
                                    <p class="mb-1 small">
                                        <strong>Status:</strong> <?php echo ucfirst($shipment['status']); ?>
                                    </p>
                                </div>
                                <span class="badge bg-<?php echo $shipment['status'] === 'in_transit' ? 'warning' : 'secondary'; ?>">
                                    <?php echo ucfirst($shipment['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- My Active Shipments -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🚚 My Active Shipments</h5>
            </div>
            <div class="card-body">
                <?php if (empty($myShipments)): ?>
                    <p class="text-muted text-center">No active shipments assigned</p>
                <?php else: ?>
                    <?php foreach ($myShipments as $shipment): ?>
                        <div class="p-3 mb-2 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($shipment['origin']); ?> → <?php echo htmlspecialchars($shipment['destination']); ?></h6>
                                    <p class="mb-1 small">
                                        <strong>ETA:</strong> <?php echo date('M j, Y g:i A', strtotime($shipment['estimated_delivery'])); ?>
                                    </p>
                                    <p class="mb-1 small">
                                        <strong>Status:</strong> <?php echo ucfirst($shipment['status']); ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php echo $shipment['status'] === 'in_transit' ? 'warning' : 'secondary'; ?> mb-2">
                                        <?php echo ucfirst($shipment['status']); ?>
                                    </span>
                                    <br>
                                    <button class="btn btn-sm btn-outline-primary" onclick="updateShipmentStatus(<?php echo $shipment['id']; ?>)">
                                        Update
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

<!-- Emergency Contacts -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📞 Emergency Contacts</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>🚨</h4>
                            <strong>Dispatch</strong><br>
                            <small class="text-muted">24/7 Support</small><br>
                            <span class="badge bg-danger">Emergency</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>👨‍💼</h4>
                            <strong>Supervisor</strong><br>
                            <small class="text-muted">Field Operations</small><br>
                            <span class="badge bg-primary">Available</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>🏥</h4>
                            <strong>Medical Support</strong><br>
                            <small class="text-muted">Health & Safety</small><br>
                            <span class="badge bg-success">24/7</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4>🔧</h4>
                            <strong>Technical Support</strong><br>
                            <small class="text-muted">System Issues</small><br>
                            <span class="badge bg-info">8AM-6PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus() {
    // Redirect to shipments page for status updates
    window.location.href = 'shipments.php';
}

function updateShipmentStatus(shipmentId) {
    // Redirect to shipments page with specific shipment
    window.location.href = 'shipments.php?shipment_id=' + shipmentId;
}

// Add any field worker specific functionality here
document.addEventListener('DOMContentLoaded', function() {
    console.log('Field Worker Dashboard loaded');
});
</script>
