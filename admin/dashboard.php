<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check authentication and admin role
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Get statistics
$stats = [];

// Total campaigns
$stmt = $db->query("SELECT COUNT(*) as total FROM campaigns");
$stats['total_campaigns'] = $stmt->fetch()['total'];

// Active campaigns
$stmt = $db->query("SELECT COUNT(*) as total FROM campaigns WHERE status = 'active'");
$stats['active_campaigns'] = $stmt->fetch()['total'];

// Total donations
$stmt = $db->query("SELECT COUNT(*) as total, SUM(amount) as sum FROM donations WHERE status = 'verified'");
$result = $stmt->fetch();
$stats['total_donations'] = $result['total'] ?? 0;
$stats['total_amount'] = $result['sum'] ?? 0;

// Pending donations
$stmt = $db->query("SELECT COUNT(*) as total FROM donations WHERE status = 'pending'");
$stats['pending_donations'] = $stmt->fetch()['total'];

// Total donors
$stmt = $db->query("SELECT COUNT(DISTINCT donor_id) as total FROM donations WHERE donor_id IS NOT NULL");
$stats['total_donors'] = $stmt->fetch()['total'];

// Recent donations
$stmt = $db->query("
    SELECT d.*, c.campaign_name, u.full_name as donor_full_name
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    LEFT JOIN users u ON d.donor_id = u.user_id
    ORDER BY d.created_at DESC
    LIMIT 5
");
$recentDonations = $stmt->fetchAll();

// Top campaigns
$stmt = $db->query("
    SELECT c.*, 
           COUNT(d.donation_id) as donation_count,
           SUM(d.amount) as total_raised
    FROM campaigns c
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id AND d.status = 'verified'
    WHERE c.status = 'active'
    GROUP BY c.campaign_id
    ORDER BY total_raised DESC
    LIMIT 5
");
$topCampaigns = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin: 0.2rem 0;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-chart-line me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="campaigns.php">
                            <i class="fas fa-bullhorn me-2"></i> Campaigns
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donations.php">
                            <i class="fas fa-hand-holding-usd me-2"></i> Donations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donors.php">
                            <i class="fas fa-users me-2"></i> Donors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-file-alt me-2"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                    </li>
                </ul>
                
                <hr class="text-white mt-auto">
                
                <div class="mt-auto">
                    <div class="mb-2">
                        <i class="fas fa-user-circle me-2"></i>
                        <small><?= htmlspecialchars($_SESSION['full_name']) ?></small>
                    </div>
                    <a href="../auth/logout.php" class="btn btn-outline-light btn-sm w-100">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <div>
                        <span class="text-muted"><?= date('l, d F Y') ?></span>
                    </div>
                </div>
                
                <?php if ($flashMessage): ?>
                    <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flashMessage['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card border-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Campaigns</h6>
                                        <h3 class="mb-0"><?= $stats['total_campaigns'] ?></h3>
                                        <small class="text-success"><?= $stats['active_campaigns'] ?> active</small>
                                    </div>
                                    <div class="text-primary">
                                        <i class="fas fa-bullhorn fa-3x opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card border-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Raised</h6>
                                        <h3 class="mb-0"><?= formatCurrency($stats['total_amount']) ?></h3>
                                        <small class="text-success"><?= $stats['total_donations'] ?> donations</small>
                                    </div>
                                    <div class="text-success">
                                        <i class="fas fa-money-bill-wave fa-3x opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Pending</h6>
                                        <h3 class="mb-0"><?= $stats['pending_donations'] ?></h3>
                                        <small class="text-warning">Awaiting verification</small>
                                    </div>
                                    <div class="text-warning">
                                        <i class="fas fa-clock fa-3x opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card border-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Donors</h6>
                                        <h3 class="mb-0"><?= $stats['total_donors'] ?></h3>
                                        <small class="text-info">Registered</small>
                                    </div>
                                    <div class="text-info">
                                        <i class="fas fa-users fa-3x opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Donations</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Donor</th>
                                                <th>Campaign</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentDonations as $donation): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($donation['donor_full_name'] ?? $donation['donor_name'] ?? 'Anonymous') ?></td>
                                                    <td><?= htmlspecialchars($donation['campaign_name']) ?></td>
                                                    <td><strong><?= formatCurrency($donation['amount']) ?></strong></td>
                                                    <td>
                                                        <?php
                                                        $badges = [
                                                            'pending' => 'warning',
                                                            'verified' => 'success',
                                                            'rejected' => 'danger'
                                                        ];
                                                        $badge = $badges[$donation['status']] ?? 'secondary';
                                                        ?>
                                                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($donation['status']) ?></span>
                                                    </td>
                                                    <td><?= timeAgo($donation['created_at']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white text-center">
                                <a href="donations.php" class="text-decoration-none">View All Donations <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Campaigns</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($topCampaigns as $campaign): ?>
                                    <?php 
                                    $percentage = calculatePercentage($campaign['total_raised'] ?? 0, $campaign['target_amount']);
                                    ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="fw-bold"><?= htmlspecialchars($campaign['campaign_name']) ?></small>
                                            <small class="text-muted"><?= $percentage ?>%</small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: <?= $percentage ?>%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-success"><?= formatCurrency($campaign['total_raised'] ?? 0) ?></small>
                                            <small class="text-muted">of <?= formatCurrency($campaign['target_amount']) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="card-footer bg-white text-center">
                                <a href="campaigns.php" class="text-decoration-none">View All Campaigns <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

