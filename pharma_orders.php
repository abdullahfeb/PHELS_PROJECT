<?php
session_start();
require_once 'config.php';
require_once 'includes/Database.php';

// Check if user is logged in and is pharma company
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharma_company') {
    header('Location: index.php');
    exit();
}

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Handle order status updates
if ($_POST['action'] === 'update_status') {
    try {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];
        
        $sql = "UPDATE orders SET status = ? WHERE id = ? AND pharma_id = ?";
        $db->query($sql, [$new_status, $order_id, $user_id]);
        
        // If order is delivered, update inventory
        if ($new_status === 'delivered') {
            $order_items = $db->query("SELECT medical_item_id, quantity FROM order_items WHERE order_id = ?", [$order_id])->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($order_items as $item) {
                $db->query("UPDATE medical_items SET quantity = quantity - ? WHERE id = ?", [$item['quantity'], $item['medical_item_id']]);
            }
        }
        
        $success_message = "Order status updated successfully!";
        
    } catch (Exception $e) {
        $error_message = "Error updating order status: " . $e->getMessage();
    }
}

// Get incoming orders
$orders = $db->query("
    SELECT o.*, u.full_name as hospital_name, u.email as hospital_email 
    FROM orders o 
    JOIN users u ON o.hospital_id = u.id 
    WHERE o.pharma_id = ? 
    ORDER BY o.order_date DESC
", [$user_id])->fetchAll(PDO::FETCH_ASSOC);

// Get order statistics
$stats = $db->query("
    SELECT 
        COUNT(*) as total_orders,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
        COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
        COUNT(CASE WHEN status = 'shipped' THEN 1 END) as shipped_orders,
        COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered_orders,
        SUM(CASE WHEN status = 'delivered' THEN total_amount ELSE 0 END) as total_revenue
    FROM orders 
    WHERE pharma_id = ?
", [$user_id])->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharma Orders - PHELS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-badge {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }
        .order-card {
            transition: all 0.3s;
            border-left: 4px solid #dee2e6;
        }
        .order-card.pending { border-left-color: #ffc107; }
        .order-card.confirmed { border-left-color: #17a2b8; }
        .order-card.processing { border-left-color: #007bff; }
        .order-card.shipped { border-left-color: #6f42c1; }
        .order-card.delivered { border-left-color: #28a745; }
        .order-card.cancelled { border-left-color: #dc3545; }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .stats-card .card-body {
            padding: 1.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-pills me-2"></i>PHELS - Pharma Orders
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard_pharma.php">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                        <h4><?php echo $stats['total_orders']; ?></h4>
                        <small>Total Orders</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h4><?php echo $stats['pending_orders']; ?></h4>
                        <small>Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <i class="fas fa-cogs fa-2x mb-2"></i>
                        <h4><?php echo $stats['processing_orders']; ?></h4>
                        <small>Processing</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <i class="fas fa-shipping-fast fa-2x mb-2"></i>
                        <h4><?php echo $stats['shipped_orders']; ?></h4>
                        <small>Shipped</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h4><?php echo $stats['delivered_orders']; ?></h4>
                        <small>Delivered</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stats-card text-center">
                    <div class="card-body">
                        <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                        <h4>₱<?php echo number_format($stats['total_revenue'], 2); ?></h4>
                        <small>Revenue</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Incoming Orders
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No orders yet</h5>
                        <p class="text-muted">Orders from hospitals will appear here</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($orders as $order): ?>
                            <div class="col-12 mb-3">
                                <div class="card order-card <?php echo $order['status']; ?>">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-3">
                                                <h6 class="mb-1">Order #<?php echo htmlspecialchars($order['order_number']); ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-hospital me-1"></i>
                                                    <?php echo htmlspecialchars($order['hospital_name']); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge status-badge bg-<?php echo getStatusColor($order['status']); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </div>
                                            <div class="col-md-2">
                                                <strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong>
                                            </div>
                                            <div class="col-md-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" onclick="showStatusUpdate(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="showInvoice(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-file-invoice"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusUpdateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="statusUpdateForm" method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="order_id" id="statusOrderId">
                        
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <select class="form-select" name="new_status" required>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Add any notes about this status change..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="statusUpdateForm" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Order details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showStatusUpdate(orderId, currentStatus) {
            document.getElementById('statusOrderId').value = orderId;
            document.querySelector('select[name="new_status"]').value = currentStatus;
            new bootstrap.Modal(document.getElementById('statusUpdateModal')).show();
        }

        function viewOrderDetails(orderId) {
            // This would load order details via AJAX
            // For now, show a simple message
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Loading order details...</p>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
            
            // Simulate loading order details
            setTimeout(() => {
                document.getElementById('orderDetailsContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Order ID:</strong> ${orderId}</p>
                            <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                            <p><strong>Status:</strong> <span class="badge bg-warning">Pending</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p><strong>Hospital:</strong> Sample Hospital</p>
                            <p><strong>Email:</strong> hospital@example.com</p>
                            <p><strong>Phone:</strong> +1234567890</p>
                        </div>
                    </div>
                    <hr>
                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>COVID-19 Vaccine</td>
                                    <td>100</td>
                                    <td>₱50.00</td>
                                    <td>₱5,000.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
            }, 1000);
        }

        function showInvoice(orderId) {
            // This would generate and show an invoice
            alert('Invoice for order #' + orderId + ' would be generated and displayed here.');
        }
    </script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'confirmed': return 'info';
        case 'processing': return 'primary';
        case 'shipped': return 'info';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>
