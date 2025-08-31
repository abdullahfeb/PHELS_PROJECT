<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/Auth.php';

$auth = new Auth();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password)) {
        // Debug information
        error_log('Login successful for user: ' . $username);
        error_log('Session data: ' . print_r($_SESSION, true));
        
        // Make sure the output buffer is clean
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set the full path for redirection
        $redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/dashboard.php';
        header('Location: ' . $redirectUrl);
        exit();
    } else {
        error_log('Login failed for user: ' . $username);
        $error = 'Invalid username or password';
    }
}

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHELS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .logo {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card p-5 text-center">
                    <div class="logo">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h2 class="mb-4 text-dark">PHELS</h2>
                    <p class="text-muted mb-4">Public Health Emergency Logistics System</p>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-user text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" name="username" 
                                       placeholder="Username" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" class="form-control border-start-0" name="password" 
                                       placeholder="Password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-login btn-lg text-white w-100">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <small class="text-muted">
                            <strong>Demo Credentials:</strong><br>
                            Username: <code>admin</code> | Password: <code>admin123</code><br>
                            Username: <code>fieldworker</code> | Password: <code>field123</code><br>
                            Username: <code>hospital</code> | Password: <code>hospital123</code><br>
                            Username: <code>pharma</code> | Password: <code>pharma123</code>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
