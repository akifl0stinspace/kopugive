<?php
/**
 * KopuGive Setup Script
 * Run this after cloning the repository to set up database and directories
 */
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>KopuGive Setup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    <style>
        body { background: #f8f9fa; padding: 50px 0; }
        .container { max-width: 800px; }
        .card { box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .step { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .step i { margin-right: 10px; }
    </style>
</head>
<body>
<div class='container'>
    <div class='card'>
        <div class='card-header bg-primary text-white'>
            <h1 class='mb-0'><i class='fas fa-rocket me-2'></i>KopuGive Setup</h1>
        </div>
        <div class='card-body'>";

try {
    $db = (new Database())->getConnection();
    echo "<div class='step alert alert-success'>
            <i class='fas fa-check-circle'></i>
            <strong>Database Connection:</strong> Successful!
          </div>";
    
    $migrationRan = false;
    
    // Check and create campaign_documents table
    echo "<h5 class='mt-4'><i class='fas fa-database me-2'></i>Checking Database Tables...</h5>";
    
    $tables = $db->query("SHOW TABLES LIKE 'campaign_documents'")->fetchAll();
    if (empty($tables)) {
        echo "<div class='step alert alert-warning'>
                <i class='fas fa-exclamation-triangle'></i>
                campaign_documents table not found. Running migration...
              </div>";
        
        $sql = file_get_contents(__DIR__ . '/database/migrations/001_add_campaign_documents.sql');
        $db->exec($sql);
        
        echo "<div class='step alert alert-success'>
                <i class='fas fa-check'></i>
                <strong>Migration 001:</strong> campaign_documents table created!
              </div>";
        $migrationRan = true;
    } else {
        echo "<div class='step alert alert-info'>
                <i class='fas fa-info-circle'></i>
                campaign_documents table already exists
              </div>";
    }
    
    // Check and add approval fields
    $columns = $db->query("SHOW COLUMNS FROM campaigns LIKE 'approved_by'")->fetchAll();
    if (empty($columns)) {
        echo "<div class='step alert alert-warning'>
                <i class='fas fa-exclamation-triangle'></i>
                Approval fields not found. Running migration...
              </div>";
        
        $sql = file_get_contents(__DIR__ . '/database/migrations/002_add_campaign_approval.sql');
        $db->exec($sql);
        
        echo "<div class='step alert alert-success'>
                <i class='fas fa-check'></i>
                <strong>Migration 002:</strong> Campaign approval system added!
              </div>";
        $migrationRan = true;
    } else {
        echo "<div class='step alert alert-info'>
                <i class='fas fa-info-circle'></i>
                Campaign approval system already exists
              </div>";
    }
    
    // Create upload directories
    echo "<h5 class='mt-4'><i class='fas fa-folder me-2'></i>Checking Directories...</h5>";
    
    $dirs = [
        'uploads/campaigns' => 'Campaign banner images',
        'uploads/receipts' => 'Donation receipts',
        'uploads/documents' => 'Campaign documents (approval letters, budgets)',
        'logs' => 'Application logs'
    ];
    
    foreach ($dirs as $dir => $description) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "<div class='step alert alert-success'>
                    <i class='fas fa-folder-plus'></i>
                    Created: <code>$dir</code> - $description
                  </div>";
        } else {
            echo "<div class='step alert alert-info'>
                    <i class='fas fa-folder-open'></i>
                    Exists: <code>$dir</code> - $description
                  </div>";
        }
    }
    
    // Final summary
    echo "<div class='card mt-4 border-success'>
            <div class='card-header bg-success text-white'>
                <h4 class='mb-0'><i class='fas fa-check-circle me-2'></i>Setup Complete!</h4>
            </div>
            <div class='card-body'>";
    
    if ($migrationRan) {
        echo "<p class='mb-3'><i class='fas fa-database me-2'></i>All database migrations have been applied successfully.</p>";
    } else {
        echo "<p class='mb-3'><i class='fas fa-check me-2'></i>Your database is already up to date. No migrations needed.</p>";
    }
    
    echo "<h5 class='mt-3'>What's New:</h5>
          <ul>
            <li><i class='fas fa-file-upload me-2 text-primary'></i>Campaign document uploads (approval letters, budgets)</li>
            <li><i class='fas fa-check-circle me-2 text-success'></i>Admin approval workflow for campaigns</li>
            <li><i class='fas fa-times-circle me-2 text-danger'></i>Approve/reject campaigns with reasons</li>
            <li><i class='fas fa-eye me-2 text-info'></i>Document review interface for admins</li>
          </ul>
          
          <h5 class='mt-3'>Next Steps:</h5>
          <ol>
            <li><a href='admin/dashboard.php' class='text-primary'><i class='fas fa-tachometer-alt me-1'></i>Go to Admin Dashboard</a></li>
            <li><a href='index.php' class='text-primary'><i class='fas fa-home me-1'></i>Go to Homepage</a></li>
            <li><a href='admin/campaign_add.php' class='text-primary'><i class='fas fa-plus me-1'></i>Create a Test Campaign</a></li>
          </ol>
          
          <div class='alert alert-danger mt-3'>
            <i class='fas fa-exclamation-triangle me-2'></i>
            <strong>Important:</strong> Delete this file (<code>setup.php</code>) for security after setup is complete!
          </div>
          
          <h5 class='mt-3'>Default Login:</h5>
          <ul class='list-unstyled'>
            <li><strong>Email:</strong> admin@mrsmkp.edu.my</li>
            <li><strong>Password:</strong> admin123</li>
          </ul>
          
          <h5 class='mt-3'>Documentation:</h5>
          <ul>
            <li><a href='CAMPAIGN_APPROVAL_SYSTEM.md' target='_blank'>Campaign Approval System Guide</a></li>
            <li><a href='CAMPAIGN_DOCUMENTS_FEATURE.md' target='_blank'>Document Upload Feature</a></li>
            <li><a href='README.md' target='_blank'>Main README</a></li>
          </ul>
          
          </div>
        </div>";
    
} catch (Exception $e) {
    echo "<div class='card mt-4 border-danger'>
            <div class='card-header bg-danger text-white'>
                <h4 class='mb-0'><i class='fas fa-times-circle me-2'></i>Setup Error</h4>
            </div>
            <div class='card-body'>
                <p class='text-danger'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                
                <h5 class='mt-3'>Troubleshooting Steps:</h5>
                <ol>
                    <li><strong>Check MySQL is running</strong> in XAMPP Control Panel</li>
                    <li><strong>Verify database exists:</strong>
                        <ul>
                            <li>Open phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>
                            <li>Create database named: <code>kopugive</code></li>
                            <li>Import: <code>database/schema.sql</code></li>
                        </ul>
                    </li>
                    <li><strong>Check database credentials</strong> in <code>config/database.php</code></li>
                    <li><strong>Ensure Apache is running</strong> in XAMPP</li>
                </ol>
                
                <div class='alert alert-info mt-3'>
                    <strong>Need Help?</strong> Check the <code>logs/php_errors.log</code> file for detailed error information.
                </div>
            </div>
          </div>";
}

echo "    </div>
    </div>
</div>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>

