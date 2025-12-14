<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? '../admin/dashboard.php' : '../donor/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $db = (new Database())->getConnection();
            $stmt = $db->prepare("SELECT user_id, full_name, email, password_hash, role, is_active FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['is_active']) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    logActivity($db, $user['user_id'], 'User logged in', 'user', $user['user_id']);
                    
                    setFlashMessage('success', 'Welcome back, ' . $user['full_name'] . '!');
                    redirect($user['role'] === 'admin' ? '../admin/dashboard.php' : '../donor/dashboard.php');
                } else {
                    $error = 'Your account has been deactivated. Please contact administrator.';
                }
            } else {
                $error = 'Invalid email or password';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include '../includes/theme_styles.php'; ?>
    <style>
        body {
            background: var(--light-gray);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            border-top: 3px solid var(--maroon-primary);
        }
        .brand-section {
            background: var(--white);
            color: var(--maroon-primary);
            border-radius: 8px 0 0 8px;
            padding: 3rem;
            border-right: 2px solid var(--gold-primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card login-card">
                    <div class="row g-0">
                        <div class="col-md-5 brand-section d-flex flex-column justify-content-center">
                            <h2 class="fw-bold mb-4"><i class="fas fa-hand-holding-heart me-2"></i>KopuGive</h2>
                            <p class="lead">MRSM Kota Putra Donation Management System</p>
                            <p class="small">Connecting hearts, building futures together.</p>
                        </div>
                        <div class="col-md-7">
                            <div class="card-body p-5">
                                <h4 class="mb-4">Welcome Back!</h4>
                                
                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?= htmlspecialchars($email ?? '') ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember">
                                        <label class="form-check-label" for="remember">Remember me</label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                    </button>
                                    
                                    <div class="text-center">
                                        <small class="text-muted">Don't have an account? 
                                            <a href="register.php">Register here</a>
                                        </small>
                                    </div>
                                    
                                    <div class="text-center mt-2">
                                        <small><a href="../index.php">Back to Home</a></small>
                                    </div>
                                </form>
                                
                                <div class="mt-4 p-3 bg-light rounded">
                                    <small class="text-muted">
                                        <strong>Demo Accounts:</strong><br>
                                        Admin: admin@mrsmkp.edu.my / admin123<br>
                                        Donor: ahmad@example.com / admin123
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

