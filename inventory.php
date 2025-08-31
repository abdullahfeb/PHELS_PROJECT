<?php
require_once 'includes/Auth.php';
require_once 'includes/Database.php';

$auth = new Auth();
$auth->requireAuth();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $sql = "INSERT INTO medical_items (name, type, targeted_disease, quantity, dose, expiry_date, batch_no, manufactur                document.getElementById('edit_batch_number').value = item.batch_no;r) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $db->insert($sql, [
                    $_POST['name'],
                    $_POST['type'],
                    $_POST['disease'],
                    $_POST['quantity'],
                    $_POST['dose'],
                    $_POST['expiry_date'],
                    $_POST['batch_number'],
                    $_POST['manufacturer']
                ]);
                $success = "Item added successfully!";
                break;
                
            case 'update':
                $sql = "UPDATE medical_items SET name=?, type=?, targeted_disease=?, quantity=?, dose=?, expiry_date=?, batch_no=?, manufacturer=? WHERE id=?";
                $db->update($sql, [
                    $_POST['name'],
                    $_POST['type'],
                    $_POST['disease'],
                    $_POST['quantity'],
                    $_POST['dose'],
                    $_POST['expiry_date'],
                    $_POST['batch_no'],
                    $_POST['manufacturer'],
                    $_POST['id']
                ]);
                $success = "Item updated successfully!";
                break;
                
            case 'delete':
                $sql = "DELETE FROM medical_items WHERE id=?";
                $db->delete($sql, [$_POST['id']]);
                $success = "Item deleted successfully!";
                break;
        }
    }
}

// Get inventory items with search and filtering
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$disease_filter = $_GET['disease'] ?? '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(name LIKE ? OR batch_number LIKE ? OR manufacturer LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($type_filter) {
    $where_conditions[] = "type = ?";
    $params[] = $type_filter;
}

if ($disease_filter) {
    $where_conditions[] = "targeted_disease LIKE ?";
    $params[] = "%$disease_filter%";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$sql = "SELECT * FROM medical_items $where_clause ORDER BY created_at DESC";
$inventory_items = $db->select($sql, $params);

// Get unique types and diseases for filters
$types = $db->select("SELECT DISTINCT type FROM medical_items ORDER BY type");
$diseases = $db->select("SELECT DISTINCT targeted_disease FROM medical_items WHERE targeted_disease IS NOT NULL AND targeted_disease != '' ORDER BY targeted_disease");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - PHELS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
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
        .expiry-warning {
            color: #dc3545;
            font-weight: bold;
        }
        .expiry-soon {
            color: #ffc107;
            font-weight: bold;
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
                        <a class="nav-link active" href="inventory.php">
                            <i class="fas fa-pills"></i> Inventory
                        </a>
                        <a class="nav-link" href="storage.php">
                            <i class="fas fa-warehouse"></i> Storage Monitoring
                        </a>
                        <a class="nav-link" href="shipments.php">
                            <i class="fas fa-truck"></i> Shipments
                        </a>
                        <a class="nav-link" href="alerts.php">
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
                            <span class="navbar-brand">Inventory Management</span>
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

                        <!-- Header with Add Button -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Medical Inventory</h2>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                                <i class="fas fa-plus me-2"></i>Add New Item
                            </button>
                        </div>

                        <!-- Search and Filters -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="search" placeholder="Search items..." 
                                               value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                    <div class="col-md-2">
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
                                    <div class="col-md-2">
                                        <select class="form-select" name="disease">
                                            <option value="">All Diseases</option>
                                            <?php foreach ($diseases as $disease): ?>
                                                <option value="<?php echo htmlspecialchars($disease['disease']); ?>" 
                                                        <?php echo $disease_filter === $disease['disease'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($disease['disease']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-search me-2"></i>Filter
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="inventory.php" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-times me-2"></i>Clear
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Inventory Table -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Type</th>
                                                <th>Disease</th>
                                                <th>Quantity</th>
                                                <th>Dose</th>
                                                <th>Expiry Date</th>
                                                <th>Batch Number</th>
                                                <th>Manufacturer</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($inventory_items)): ?>
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-4">
                                                        <i class="fas fa-inbox fa-2x mb-3"></i><br>
                                                        No inventory items found
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($inventory_items as $item): ?>
                                                    <?php
                                                    $days_until_expiry = (strtotime($item['expiry_date']) - time()) / (60 * 60 * 24);
                                                    $expiry_class = '';
                                                    if ($days_until_expiry < 0) {
                                                        $expiry_class = 'expiry-warning';
                                                    } elseif ($days_until_expiry <= 30) {
                                                        $expiry_class = 'expiry-soon';
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                                        <td><span class="badge bg-info"><?php echo ucfirst($item['type']); ?></span></td>
                                                        <td><?php echo htmlspecialchars($item['disease'] ?: 'N/A'); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $item['quantity'] > 100 ? 'success' : ($item['quantity'] > 50 ? 'warning' : 'danger'); ?>">
                                                                <?php echo number_format($item['quantity']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($item['dose'] ?: 'N/A'); ?></td>
                                                        <td class="<?php echo $expiry_class; ?>">
                                                            <?php echo date('M j, Y', strtotime($item['expiry_date'])); ?>
                                                            <?php if ($days_until_expiry <= 30): ?>
                                                                <br><small class="text-muted">
                                                                    <?php echo $days_until_expiry < 0 ? 'Expired' : $days_until_expiry . ' days left'; ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><code><?php echo htmlspecialchars($item['batch_no']); ?></code></td>
                                                        <td><?php echo htmlspecialchars($item['manufacturer'] ?: 'N/A'); ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary" onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger" onclick="deleteItem(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type *</label>
                                <select class="form-select" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="vaccine">Vaccine</option>
                                    <option value="medicine">Medicine</option>
                                    <option value="equipment">Equipment</option>
                                    <option value="supplies">Supplies</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Targeted Disease</label>
                                <input type="text" class="form-control" name="disease" placeholder="e.g., COVID-19">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity *</label>
                                <input type="number" class="form-control" name="quantity" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dose</label>
                                <input type="text" class="form-control" name="dose" placeholder="e.g., 500mg, 0.5ml">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date *</label>
                                <input type="date" class="form-control" name="expiry_date" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batch Number *</label>
                                <input type="text" class="form-control" name="batch_no" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Manufacturer</label>
                                <input type="text" class="form-control" name="manufacturer" placeholder="e.g., Moderna, Pfizer">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Name *</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type *</label>
                                <select class="form-select" name="type" id="edit_type" required>
                                    <option value="vaccine">Vaccine</option>
                                    <option value="medicine">Medicine</option>
                                    <option value="equipment">Equipment</option>
                                    <option value="supplies">Supplies</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Targeted Disease</label>
                                <input type="text" class="form-control" name="disease" id="edit_disease">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quantity *</label>
                                <input type="number" class="form-control" name="quantity" id="edit_quantity" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dose</label>
                                <input type="text" class="form-control" name="dose" id="edit_dose">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date *</label>
                                <input type="date" class="form-control" name="expiry_date" id="edit_expiry_date" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batch Number *</label>
                                <input type="text" class="form-control" name="batch_no" id="edit_batch_number" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Manufacturer</label>
                                <input type="text" class="form-control" name="manufacturer" id="edit_manufacturer">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="delete_item_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_item_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editItem(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_name').value = item.name;
            document.getElementById('edit_type').value = item.type;
            document.getElementById('edit_disease').value = item.disease || '';
            document.getElementById('edit_quantity').value = item.quantity;
            document.getElementById('edit_dose').value = item.dose || '';
            document.getElementById('edit_expiry_date').value = item.expiry_date;
            document.getElementById('edit_batch_number').value = item.batch_number;
            document.getElementById('edit_manufacturer').value = item.manufacturer || '';
            
            new bootstrap.Modal(document.getElementById('editItemModal')).show();
        }

        function deleteItem(id, name) {
            document.getElementById('delete_item_id').value = id;
            document.getElementById('delete_item_name').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteItemModal')).show();
        }
    </script>
</body>
</html>
