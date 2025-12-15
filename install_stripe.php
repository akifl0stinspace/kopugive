<?php
/**
 * Stripe Integration Installation Script
 * KopuGive - MRSM Kota Putra Donation System
 * 
 * Run this script once to set up Stripe integration
 * URL: http://localhost/kopugive/install_stripe.php
 */

require_once 'config/database.php';

$errors = [];
$success = [];
$warnings = [];

// Check if already run
$checkFile = __DIR__ . '/.stripe_installed';
if (file_exists($checkFile)) {
    $warnings[] = "Stripe integration appears to be already installed. Delete .stripe_installed file to run again.";
}

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = "PHP 7.4 or higher is required. Current version: " . PHP_VERSION;
} else {
    $success[] = "PHP version check passed: " . PHP_VERSION;
}

// Check if composer.json exists
if (!file_exists(__DIR__ . '/composer.json')) {
    $errors[] = "composer.json not found. Stripe integration files may be missing.";
} else {
    $success[] = "composer.json found";
}

// Check if vendor directory exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    $warnings[] = "Stripe PHP SDK not installed. Run: composer install";
} else {
    $success[] = "Stripe PHP SDK installed";
}

// Check database connection
try {
    $db = (new Database())->getConnection();
    $success[] = "Database connection successful";
    
    // Check if donations table exists
    $stmt = $db->query("SHOW TABLES LIKE 'donations'");
    if ($stmt->rowCount() === 0) {
        $errors[] = "Donations table not found. Please run database schema first.";
    } else {
        $success[] = "Donations table exists";
        
        // Check if Stripe fields exist
        $stmt = $db->query("SHOW COLUMNS FROM donations LIKE 'stripe_payment_intent_id'");
        if ($stmt->rowCount() === 0) {
            $warnings[] = "Stripe fields not found in donations table. Migration needed.";
            
            // Attempt to run migration
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
                try {
                    $db->exec("
                        ALTER TABLE donations 
                        ADD COLUMN stripe_payment_intent_id VARCHAR(255) NULL AFTER transaction_id,
                        ADD COLUMN stripe_checkout_session_id VARCHAR(255) NULL AFTER stripe_payment_intent_id,
                        ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending' AFTER status,
                        ADD INDEX idx_stripe_payment_intent (stripe_payment_intent_id),
                        ADD INDEX idx_stripe_checkout_session (stripe_checkout_session_id)
                    ");
                    
                    $db->exec("UPDATE donations SET payment_status = 'pending' WHERE payment_status IS NULL");
                    
                    $success[] = "✅ Database migration completed successfully!";
                    
                    // Mark as installed
                    file_put_contents($checkFile, date('Y-m-d H:i:s'));
                    
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                        $success[] = "Stripe fields already exist in database";
                    } else {
                        $errors[] = "Migration failed: " . $e->getMessage();
                    }
                }
            }
        } else {
            $success[] = "Stripe fields exist in donations table";
        }
    }
    
} catch (Exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
}

// Check config file
$configFile = __DIR__ . '/config/config.php';
if (file_exists($configFile)) {
    $configContent = file_get_contents($configFile);
    
    if (strpos($configContent, 'STRIPE_PUBLISHABLE_KEY') === false) {
        $errors[] = "STRIPE_PUBLISHABLE_KEY not found in config.php";
    } else {
        $success[] = "Stripe configuration found in config.php";
        
        if (strpos($configContent, 'YOUR_PUBLISHABLE_KEY_HERE') !== false) {
            $warnings[] = "Stripe API keys not configured. Update config/config.php with your keys.";
        }
    }
} else {
    $errors[] = "config/config.php not found";
}

// Check payment files
$paymentFiles = [
    'payment/stripe_checkout.php',
    'payment/stripe_success.php',
    'payment/stripe_cancel.php',
    'payment/stripe_webhook.php'
];

foreach ($paymentFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $success[] = basename($file) . " exists";
    } else {
        $errors[] = basename($file) . " not found";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Integration Setup - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .setup-card { max-width: 800px; margin: 50px auto; }
        .check-item { padding: 10px; margin: 5px 0; border-radius: 5px; }
        .check-success { background: #d4edda; color: #155724; }
        .check-warning { background: #fff3cd; color: #856404; }
        .check-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-card">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Stripe Integration Setup
                    </h3>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Success Messages -->
                    <?php if (!empty($success)): ?>
                        <div class="mb-4">
                            <h5 class="text-success">
                                <i class="fas fa-check-circle me-2"></i>Checks Passed
                            </h5>
                            <?php foreach ($success as $msg): ?>
                                <div class="check-item check-success">
                                    <i class="fas fa-check me-2"></i><?= htmlspecialchars($msg) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Warnings -->
                    <?php if (!empty($warnings)): ?>
                        <div class="mb-4">
                            <h5 class="text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>Warnings
                            </h5>
                            <?php foreach ($warnings as $msg): ?>
                                <div class="check-item check-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($msg) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Errors -->
                    <?php if (!empty($errors)): ?>
                        <div class="mb-4">
                            <h5 class="text-danger">
                                <i class="fas fa-times-circle me-2"></i>Errors
                            </h5>
                            <?php foreach ($errors as $msg): ?>
                                <div class="check-item check-error">
                                    <i class="fas fa-times me-2"></i><?= htmlspecialchars($msg) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Migration Button -->
                    <?php if (!empty($warnings) && strpos(implode(' ', $warnings), 'Migration needed') !== false): ?>
                        <div class="alert alert-info">
                            <h5><i class="fas fa-database me-2"></i>Database Migration Required</h5>
                            <p>Click the button below to add Stripe fields to your database.</p>
                            <form method="POST">
                                <button type="submit" name="run_migration" class="btn btn-primary">
                                    <i class="fas fa-play me-2"></i>Run Database Migration
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Next Steps -->
                    <div class="card bg-light mt-4">
                        <div class="card-body">
                            <h5><i class="fas fa-list-ol me-2"></i>Next Steps</h5>
                            <ol class="mb-0">
                                <li class="mb-2">
                                    <strong>Install Stripe PHP SDK:</strong>
                                    <code class="bg-dark text-white p-2 d-block mt-1">composer install</code>
                                </li>
                                <li class="mb-2">
                                    <strong>Get Stripe API Keys:</strong>
                                    <a href="https://dashboard.stripe.com/register" target="_blank">Sign up for Stripe</a>
                                    and get your test keys from the dashboard.
                                </li>
                                <li class="mb-2">
                                    <strong>Update Configuration:</strong>
                                    Edit <code>config/config.php</code> and add your Stripe keys.
                                </li>
                                <li class="mb-2">
                                    <strong>Run Migration:</strong>
                                    Click the "Run Database Migration" button above (if shown).
                                </li>
                                <li>
                                    <strong>Test Payment:</strong>
                                    Make a test donation using card <code>4242 4242 4242 4242</code>
                                </li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- Documentation Links -->
                    <div class="mt-4 text-center">
                        <a href="STRIPE_QUICK_START.md" class="btn btn-outline-primary me-2">
                            <i class="fas fa-rocket me-2"></i>Quick Start Guide
                        </a>
                        <a href="STRIPE_INTEGRATION_GUIDE.md" class="btn btn-outline-secondary">
                            <i class="fas fa-book me-2"></i>Full Documentation
                        </a>
                    </div>
                    
                    <!-- Status Summary -->
                    <div class="mt-4 p-3 border rounded text-center">
                        <?php if (empty($errors) && empty($warnings)): ?>
                            <h4 class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </h4>
                            <h5 class="text-success">Setup Complete! 🎉</h5>
                            <p class="mb-0">Your Stripe integration is ready to use.</p>
                            <a href="index.php" class="btn btn-success mt-3">
                                <i class="fas fa-home me-2"></i>Go to Homepage
                            </a>
                        <?php elseif (empty($errors)): ?>
                            <h4 class="text-warning mb-2">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </h4>
                            <h5 class="text-warning">Almost There!</h5>
                            <p class="mb-0">Complete the steps above to finish setup.</p>
                        <?php else: ?>
                            <h4 class="text-danger mb-2">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </h4>
                            <h5 class="text-danger">Setup Incomplete</h5>
                            <p class="mb-0">Please fix the errors above before proceeding.</p>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
            
            <!-- Help Card -->
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-body">
                    <h6><i class="fas fa-question-circle me-2"></i>Need Help?</h6>
                    <p class="mb-2 small">
                        <strong>Composer not installed?</strong><br>
                        Download from: <a href="https://getcomposer.org/download/" target="_blank">getcomposer.org</a>
                    </p>
                    <p class="mb-0 small">
                        <strong>Stripe Account?</strong><br>
                        Sign up at: <a href="https://dashboard.stripe.com/register" target="_blank">dashboard.stripe.com/register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

