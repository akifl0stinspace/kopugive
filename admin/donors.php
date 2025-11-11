<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Only admins can access this page
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Fetch donors
$stmt = $db->query("SELECT user_id, full_name, email, phone, is_active, created_at FROM users WHERE role = 'donor' ORDER BY created_at DESC");
$donors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donors - KopuGive Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%); }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 1rem 1.5rem; border-radius: 8px; margin: 0.2rem 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.2); color: white; }
    </style>
    </head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar text-white p-3">
                <div class="text-center mb-4">
                    <h4><i class="fas fa-hand-holding-heart"></i> KopuGive</h4>
                    <small>Admin Panel</small>
                </div>
                <hr class="text-white">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-chart-line me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="campaigns.php"><i class="fas fa-bullhorn me-2"></i> Campaigns</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donations.php"><i class="fas fa-hand-holding-usd me-2"></i> Donations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="donors.php"><i class="fas fa-users me-2"></i> Donors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php"><i class="fas fa-file-alt me-2"></i> Reports</a>
                    </li>
                </ul>
                <hr class="text-white mt-auto">
                <div class="mt-auto">
                    <div class="mb-2">
                        <i class="fas fa-user-circle me-2"></i>
                        <small><?= htmlspecialchars($_SESSION['full_name']) ?></small>
                    </div>
                    <a href="../auth/logout.php" class="btn btn-outline-light btn-sm w-100"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4 py-4" style="margin-left: 16.666667%;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Donors</h2>
                    <span class="text-muted"><?= count($donors) ?> total</span>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($donors)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No donors found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($donors as $index => $donor): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($donor['full_name']) ?></td>
                                                <td><?= htmlspecialchars($donor['email']) ?></td>
                                                <td><?= htmlspecialchars($donor['phone'] ?? '-') ?></td>
                                                <td>
                                                    <?php if ($donor['is_active']): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d M Y, h:i A', strtotime($donor['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


