<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();
$userId = $_SESSION['user_id'];

// Get donor statistics
$stmt = $db->prepare("SELECT COUNT(*) as total, SUM(amount) as total_amount FROM donations WHERE donor_id = ? AND status IN ('pending', 'verified')");
$stmt->execute([$userId]);
$donorStats = $stmt->fetch();

// Get recent donations
$stmt = $db->prepare("
    SELECT d.*, c.campaign_name
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    WHERE d.donor_id = ?
    ORDER BY d.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentDonations = $stmt->fetchAll();

// Get active campaigns (sorted by end date - campaigns ending soon first)
$stmt = $db->query("
    SELECT c.*, 
           COUNT(DISTINCT d.donation_id) as donation_count,
           COALESCE(SUM(CASE WHEN d.status = 'verified' THEN d.amount ELSE 0 END), 0) as total_raised,
           DATEDIFF(c.end_date, CURDATE()) as days_remaining
    FROM campaigns c
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id
    WHERE c.status = 'active' AND c.end_date >= CURDATE()
    GROUP BY c.campaign_id
    ORDER BY c.end_date ASC, c.created_at DESC
    LIMIT 3
");
$activeCampaigns = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-hand-holding-heart me-2"></i>KopuGive
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="campaigns.php">
                            <i class="fas fa-bullhorn me-1"></i>Browse Campaigns
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_donations.php">
                            <i class="fas fa-history me-1"></i>My Donations
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($_SESSION['full_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container my-5">
        <!-- Welcome Banner -->
        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body p-4">
                <h3 class="mb-2">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>! 👋</h3>
                <p class="mb-0">Thank you for being part of our community. Your contributions make a difference!</p>
            </div>
        </div>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card stat-card border-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Donations</h6>
                                <h3 class="mb-0"><?= $donorStats['total'] ?? 0 ?></h3>
                                <small class="text-primary">All time</small>
                            </div>
                            <div class="text-primary">
                                <i class="fas fa-hand-holding-heart fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card stat-card border-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Contributed</h6>
                                <h3 class="mb-0"><?= formatCurrency($donorStats['total_amount'] ?? 0) ?></h3>
                                <small class="text-success">Thank you!</small>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-money-bill-wave fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Donations -->
            <div class="col-md-7 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Donations</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentDonations)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No donations yet</p>
                                <a href="campaigns.php" class="btn btn-primary">Browse Campaigns</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Campaign</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentDonations as $donation): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($donation['campaign_name']) ?></td>
                                                <td><strong class="text-success"><?= formatCurrency($donation['amount']) ?></strong></td>
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
                                                <td><?= formatDate($donation['donation_date'], 'd M Y') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-white text-center">
                                <a href="my_donations.php" class="text-decoration-none">View All <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Active Campaigns -->
            <div class="col-md-5 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Active Campaigns</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($activeCampaigns as $campaign): ?>
                            <?php $percentage = calculatePercentage($campaign['total_raised'], $campaign['target_amount']); ?>
                            <div class="mb-3">
                                <h6><?= htmlspecialchars($campaign['campaign_name']) ?></h6>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-success"><?= formatCurrency($campaign['total_raised']) ?></small>
                                    <small class="text-muted"><?= $percentage ?>%</small>
                                </div>
                                <a href="campaign_view.php?id=<?= $campaign['campaign_id'] ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                    Donate <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="campaigns.php" class="text-decoration-none">View All Campaigns <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

