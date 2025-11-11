<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check authentication and admin role
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    try {
        $settings = [
            'site_name' => sanitize($_POST['site_name'] ?? ''),
            'site_email' => sanitize($_POST['site_email'] ?? ''),
            'site_phone' => sanitize($_POST['site_phone'] ?? ''),
            'school_name' => sanitize($_POST['school_name'] ?? ''),
            'currency' => sanitize($_POST['currency'] ?? 'MYR'),
            'timezone' => sanitize($_POST['timezone'] ?? 'Asia/Kuala_Lumpur')
        ];
        
        // Update each setting
        $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($settings as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        
        logActivity($db, $_SESSION['user_id'], 'Updated system settings', 'settings', null);
        $success = 'Settings updated successfully!';
    } catch (Exception $e) {
        $error = 'Failed to update settings: ' . $e->getMessage();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All password fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        try {
            // Verify current password
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $error = 'Current password is incorrect';
            } else {
                // Update password
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt->execute([$newHash, $_SESSION['user_id']]);
                
                logActivity($db, $_SESSION['user_id'], 'Changed password', 'user', $_SESSION['user_id']);
                $success = 'Password changed successfully!';
            }
        } catch (Exception $e) {
            $error = 'Failed to change password: ' . $e->getMessage();
        }
    }
}

// Get current settings
$stmt = $db->query("SELECT setting_key, setting_value FROM settings");
$currentSettings = [];
while ($row = $stmt->fetch()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}

// Get system info
$systemInfo = [
    'php_version' => phpversion(),
    'mysql_version' => $db->query("SELECT VERSION()")->fetchColumn(),
    'upload_max_size' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit')
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - KopuGive Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <!-- Main content -->
    <main class="col-md-10 ms-sm-auto px-md-4 py-4" style="margin-left: 16.666667%;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-cog me-2"></i>System Settings</h2>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Settings Tabs -->
        <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
                    <i class="fas fa-sliders-h me-2"></i>General Settings
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">
                    <i class="fas fa-lock me-2"></i>Security
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button">
                    <i class="fas fa-server me-2"></i>System Info
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="settingsTabContent">
            <!-- General Settings Tab -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>General Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" name="site_name" class="form-control" 
                                           value="<?= htmlspecialchars($currentSettings['site_name'] ?? 'KopuGive') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">School Name</label>
                                    <input type="text" name="school_name" class="form-control" 
                                           value="<?= htmlspecialchars($currentSettings['school_name'] ?? 'MRSM Kota Putra') ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" name="site_email" class="form-control" 
                                           value="<?= htmlspecialchars($currentSettings['site_email'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="text" name="site_phone" class="form-control" 
                                           value="<?= htmlspecialchars($currentSettings['site_phone'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Currency</label>
                                    <select name="currency" class="form-select">
                                        <option value="MYR" <?= ($currentSettings['currency'] ?? 'MYR') == 'MYR' ? 'selected' : '' ?>>MYR (Malaysian Ringgit)</option>
                                        <option value="USD" <?= ($currentSettings['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD (US Dollar)</option>
                                        <option value="SGD" <?= ($currentSettings['currency'] ?? '') == 'SGD' ? 'selected' : '' ?>>SGD (Singapore Dollar)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Timezone</label>
                                    <select name="timezone" class="form-select">
                                        <option value="Asia/Kuala_Lumpur" <?= ($currentSettings['timezone'] ?? 'Asia/Kuala_Lumpur') == 'Asia/Kuala_Lumpur' ? 'selected' : '' ?>>Asia/Kuala_Lumpur</option>
                                        <option value="Asia/Singapore" <?= ($currentSettings['timezone'] ?? '') == 'Asia/Singapore' ? 'selected' : '' ?>>Asia/Singapore</option>
                                        <option value="Asia/Jakarta" <?= ($currentSettings['timezone'] ?? '') == 'Asia/Jakarta' ? 'selected' : '' ?>>Asia/Jakarta</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" name="update_settings" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Security Tab -->
            <div class="tab-pane fade" id="security" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" 
                                               minlength="8" required>
                                        <small class="text-muted">Minimum 8 characters</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" 
                                               minlength="8" required>
                                    </div>
                                    
                                    <button type="submit" name="change_password" class="btn btn-primary">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>Password Requirements</h6>
                                        <ul class="mb-0">
                                            <li>Minimum 8 characters long</li>
                                            <li>Use a mix of letters, numbers, and symbols</li>
                                            <li>Avoid common words or patterns</li>
                                            <li>Don't reuse old passwords</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $db->prepare("
                            SELECT action, created_at, ip_address 
                            FROM activity_logs 
                            WHERE user_id = ? 
                            ORDER BY created_at DESC 
                            LIMIT 10
                        ");
                        $stmt->execute([$_SESSION['user_id']]);
                        $activities = $stmt->fetchAll();
                        ?>
                        
                        <?php if (count($activities) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>Date & Time</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activities as $activity): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($activity['action']) ?></td>
                                                <td><?= formatDate($activity['created_at'], 'd M Y H:i') ?></td>
                                                <td><code><?= htmlspecialchars($activity['ip_address']) ?></code></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No recent activity</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- System Info Tab -->
            <div class="tab-pane fade" id="system" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-server me-2"></i>System Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Server Environment</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>PHP Version</strong></td>
                                        <td><?= $systemInfo['php_version'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>MySQL Version</strong></td>
                                        <td><?= $systemInfo['mysql_version'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Server Software</strong></td>
                                        <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>PHP Configuration</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Upload Max Size</strong></td>
                                        <td><?= $systemInfo['upload_max_size'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Post Max Size</strong></td>
                                        <td><?= $systemInfo['post_max_size'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Memory Limit</strong></td>
                                        <td><?= $systemInfo['memory_limit'] ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Database Statistics</h6>
                                <?php
                                $dbStats = [
                                    'Users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                                    'Campaigns' => $db->query("SELECT COUNT(*) FROM campaigns")->fetchColumn(),
                                    'Donations' => $db->query("SELECT COUNT(*) FROM donations")->fetchColumn(),
                                    'Activity Logs' => $db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn()
                                ];
                                ?>
                                <table class="table table-sm">
                                    <?php foreach ($dbStats as $label => $count): ?>
                                        <tr>
                                            <td><strong><?= $label ?></strong></td>
                                            <td><?= number_format($count) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Directory Status</h6>
                                <?php
                                $directories = [
                                    'uploads/campaigns/' => is_writable('../uploads/campaigns/'),
                                    'uploads/receipts/' => is_writable('../uploads/receipts/'),
                                    'logs/' => is_writable('../logs/')
                                ];
                                ?>
                                <table class="table table-sm">
                                    <?php foreach ($directories as $dir => $writable): ?>
                                        <tr>
                                            <td><code><?= $dir ?></code></td>
                                            <td>
                                                <?php if ($writable): ?>
                                                    <span class="badge bg-success">Writable</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Not Writable</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="alert alert-info mb-0">
                            <h6><i class="fas fa-info-circle me-2"></i>Application Info</h6>
                            <p class="mb-1"><strong>Version:</strong> <?= APP_VERSION ?></p>
                            <p class="mb-1"><strong>Environment:</strong> <?= PAYMENT_MODE ?></p>
                            <p class="mb-0"><strong>Timezone:</strong> <?= APP_TIMEZONE ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

