<?php
session_start();
require_once 'config.php';
require_once 'includes/Database.php';

// Check if user is logged in and is hospital staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital_staff') {
    header('Location: index.php');
    exit();
}

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Handle order submission
if (isset($_POST['action']) && $_POST['action'] === 'place_order') {
    try {
        $db->beginTransaction();
        
        // Generate order number
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
        
        // Create order
        $order_sql = "INSERT INTO orders (hospital_id, pharma_id, order_number, total_amount, delivery_date, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $order_params = [
            $user_id,
            $_POST['pharma_id'],
            $order_number,
            $_POST['total_amount'],
            $_POST['delivery_date'],
            $_POST['notes']
        ];
        
        $order_id = $db->insert($order_sql, $order_params);
        
        // Add order items
        $items = json_decode($_POST['cart_items'], true);
        foreach ($items as $item) {
            $item_sql = "INSERT INTO order_items (order_id, medical_item_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)";
            $db->insert($item_sql, [$order_id, $item['id'], $item['quantity'], $item['price'], $item['total']]);
        }
        
        // Create invoice
        $invoice_number = 'INV-' . date('Ymd') . '-' . strtoupper(uniqid());
        $invoice_sql = "INSERT INTO invoices (order_id, invoice_number, invoice_date, due_date, subtotal, total_amount) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?, ?)";
        $db->insert($invoice_sql, [$order_id, $invoice_number, $_POST['total_amount'], $_POST['total_amount']]);
        
        $db->commit();
        $success_message = "Order placed successfully! Order #: " . $order_number;
        
    } catch (Exception $e) {
        $db->rollback();
        $error_message = "Error placing order: " . $e->getMessage();
    }
}

// Get available pharmaceutical companies
$pharma_companies = $db->query("SELECT id, full_name FROM users WHERE role = 'pharma_company'")->fetchAll(PDO::FETCH_ASSOC);

// Get medical items from all pharma companies
// First check if pharma_id column exists, if not, use a fallback query
try {
    $medical_items = $db->query("SELECT mi.*, u.full_name as pharma_name, u.id as pharma_id FROM medical_items mi JOIN users u ON mi.pharma_id = u.id WHERE u.role = 'pharma_company' AND mi.quantity > 0")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If pharma_id column doesn't exist, show message to run setup first
    $medical_items = [];
    $setup_required = true;
}

// Get user's bank accounts
try {
    $bank_accounts = $db->query("SELECT * FROM bank_accounts WHERE user_id = ?", [$user_id])->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bank_accounts = [];
}

// Get user's mobile payment accounts
try {
    $mobile_accounts = $db->query("SELECT * FROM mobile_payment_accounts WHERE user_id = ?", [$user_id])->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $mobile_accounts = [];
}

// Get user's orders
try {
    $orders = $db->query("SELECT o.*, u.full_name as pharma_name FROM orders o JOIN users u ON o.pharma_id = u.id WHERE o.hospital_id = ? ORDER BY o.order_date DESC", [$user_id])->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Orders - PHELS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .cart-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .payment-method {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        .payment-method.selected {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-hospital me-2"></i>PHELS - Hospital Orders
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard_hospital.php">
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

        <?php if (isset($setup_required)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Setup Required!</strong> The payment system needs to be configured first. 
                Please run the setup scripts in this order:
                <ol class="mb-0 mt-2">
                    <li><a href="add_payment_system.php" class="alert-link">add_payment_system.php</a> - Creates payment tables</li>
                    <li><a href="add_sample_products.php" class="alert-link">add_sample_products.php</a> - Adds sample products with prices</li>
                </ol>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Product Catalog -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-pills me-2"></i>Available Medical Products
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($setup_required)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Setup Required</h5>
                                <p class="text-muted">Please run the setup scripts to enable product ordering.</p>
                                <a href="add_payment_system.php" class="btn btn-primary">Run Setup</a>
                            </div>
                        <?php elseif (empty($medical_items)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Products Available</h5>
                                <p class="text-muted">No medical products are currently available for ordering.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($medical_items as $item): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card product-card h-100" onclick="addToCart(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <p class="card-text text-muted small">
                                                    <strong>Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $item['type'])); ?><br>
                                                    <strong>Disease:</strong> <?php echo htmlspecialchars($item['targeted_disease'] ?: 'N/A'); ?><br>
                                                    <strong>Dose:</strong> <?php echo htmlspecialchars($item['dose']); ?><br>
                                                    <strong>Expiry:</strong> <?php echo date('M Y', strtotime($item['expiry_date'])); ?><br>
                                                    <strong>Available:</strong> <?php echo $item['quantity']; ?> units
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-primary fw-bold">₱<?php echo number_format($item['price'] ?? 100, 2); ?></span>
                                                    <span class="badge bg-success"><?php echo htmlspecialchars($item['pharma_name']); ?></span>
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

            <!-- Shopping Cart -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($setup_required)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-lock fa-2x text-muted mb-2"></i>
                                <p class="text-muted small">Cart will be available after setup</p>
                            </div>
                        <?php else: ?>
                            <div id="cart-items">
                                <p class="text-muted">No items in cart</p>
                            </div>
                            
                            <div id="cart-summary" style="display: none;">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong id="cart-total">₱0.00</strong>
                                </div>
                                
                                <button class="btn btn-primary w-100 mt-3" onclick="showCheckout()">
                                    <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Modal -->
        <div class="modal fade" id="checkoutModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Complete Your Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="orderForm" method="POST">
                            <input type="hidden" name="action" value="place_order">
                            <input type="hidden" name="pharma_id" id="selectedPharmaId">
                            <input type="hidden" name="total_amount" id="orderTotalAmount">
                            <input type="hidden" name="cart_items" id="cartItemsData">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Order Details</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Delivery Date</label>
                                        <input type="date" class="form-control" name="delivery_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Special instructions..."></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>Payment Method</h6>
                                    <div id="payment-methods">
                                        <!-- Payment methods will be populated here -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <h6>Order Summary</h6>
                                <div id="order-summary">
                                    <!-- Order summary will be populated here -->
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" form="orderForm" class="btn btn-primary">
                            <i class="fas fa-check me-2"></i>Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <?php if (!isset($setup_required)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Order History
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No orders yet</p>
                                <small class="text-muted">Your order history will appear here</small>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Pharma Company</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Order Date</th>
                                            <th>Delivery Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($order['pharma_name']); ?></td>
                                                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusColor($order['status']); ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                <td><?php echo $order['delivery_date'] ? date('M d, Y', strtotime($order['delivery_date'])) : 'TBD'; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
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
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = [];
        let selectedPaymentMethod = '';

        function addToCart(item) {
            const existingItem = cart.find(cartItem => cartItem.id === item.id);
            
            if (existingItem) {
                existingItem.quantity += 1;
                existingItem.total = existingItem.quantity * existingItem.price;
            } else {
                const cartItem = {
                    ...item,
                    quantity: 1,
                    price: item.price || 100,
                    total: item.price || 100
                };
                cart.push(cartItem);
            }
            
            updateCartDisplay();
        }

        function updateCartDisplay() {
            const cartContainer = document.getElementById('cart-items');
            const cartSummary = document.getElementById('cart-summary');
            
            if (cart.length === 0) {
                cartContainer.innerHTML = '<p class="text-muted">No items in cart</p>';
                cartSummary.style.display = 'none';
                return;
            }
            
            let cartHTML = '';
            let total = 0;
            
            cart.forEach((item, index) => {
                cartHTML += `
                    <div class="cart-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${item.name}</h6>
                                <small class="text-muted">Qty: ${item.quantity} × ₱${item.price}</small>
                            </div>
                            <div class="text-end">
                                <strong>₱${item.total}</strong>
                                <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                total += item.total;
            });
            
            cartContainer.innerHTML = cartHTML;
            document.getElementById('cart-total').textContent = '₱' + total.toFixed(2);
            cartSummary.style.display = 'block';
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
        }

        function showCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            // Set form data
            document.getElementById('selectedPharmaId').value = cart[0].pharma_id;
            document.getElementById('orderTotalAmount').value = cart.reduce((sum, item) => sum + item.total, 0);
            document.getElementById('cartItemsData').value = JSON.stringify(cart);
            
            // Populate payment methods
            populatePaymentMethods();
            
            // Populate order summary
            populateOrderSummary();
            
            // Show modal
            new bootstrap.Modal(document.getElementById('checkoutModal')).show();
        }

        function populatePaymentMethods() {
            const paymentMethods = document.getElementById('payment-methods');
            const bankAccounts = <?php echo json_encode($bank_accounts); ?>;
            const mobileAccounts = <?php echo json_encode($mobile_accounts); ?>;
            
            let html = '';
            
            // Bank transfer options
            bankAccounts.forEach(account => {
                html += `
                    <div class="payment-method" onclick="selectPaymentMethod('bank_${account.id}')" data-method="bank_${account.id}">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="bank_${account.id}">
                            <label class="form-check-label">
                                <i class="fas fa-university me-2"></i>Bank Transfer - ${account.bank_name}
                                <br><small class="text-muted">${account.account_number}</small>
                            </label>
                        </div>
                    </div>
                `;
            });
            
            // Mobile payment options
            mobileAccounts.forEach(account => {
                html += `
                    <div class="payment-method" onclick="selectPaymentMethod('mobile_${account.id}')" data-method="mobile_${account.id}">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="mobile_${account.id}">
                            <label class="form-check-label">
                                <i class="fas fa-mobile-alt me-2"></i>${account.payment_type.toUpperCase()}
                                <br><small class="text-muted">${account.account_number}</small>
                            </label>
                        </div>
                    </div>
                `;
            });
            
            // Cash option
            html += `
                <div class="payment-method" onclick="selectPaymentMethod('cash')" data-method="cash">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" value="cash">
                        <label class="form-check-label">
                            <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                        </label>
                    </div>
                </div>
            `;
            
            paymentMethods.innerHTML = html;
        }

        function selectPaymentMethod(method) {
            // Remove previous selection
            document.querySelectorAll('.payment-method').forEach(pm => pm.classList.remove('selected'));
            
            // Select new method
            const selectedElement = document.querySelector(`[data-method="${method}"]`);
            if (selectedElement) {
                selectedElement.classList.add('selected');
                selectedElement.querySelector('input[type="radio"]').checked = true;
            }
            
            selectedPaymentMethod = method;
        }

        function populateOrderSummary() {
            const orderSummary = document.getElementById('order-summary');
            
            let html = '';
            cart.forEach(item => {
                html += `
                    <div class="d-flex justify-content-between mb-2">
                        <span>${item.name} × ${item.quantity}</span>
                        <span>₱${item.total}</span>
                    </div>
                `;
            });
            
            const total = cart.reduce((sum, item) => sum + item.total, 0);
            html += `
                <hr>
                <div class="d-flex justify-content-between">
                    <strong>Total:</strong>
                    <strong>₱${total.toFixed(2)}</strong>
                </div>
            `;
            
            orderSummary.innerHTML = html;
        }

        function viewOrderDetails(orderId) {
            // This would open a modal with detailed order information
            alert('Order details for order #' + orderId + ' would be displayed here.');
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
