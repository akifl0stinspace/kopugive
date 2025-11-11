<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Get filter
$category = $_GET['category'] ?? 'all';

// Fetch campaigns (sorted by end date - campaigns ending soon first)
$query = "
    SELECT c.*, 
           COUNT(DISTINCT d.donation_id) as donation_count,
           COALESCE(SUM(CASE WHEN d.status = 'verified' THEN d.amount ELSE 0 END), 0) as total_raised,
           DATEDIFF(c.end_date, CURDATE()) as days_remaining
    FROM campaigns c
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id
    WHERE c.status = 'active' AND c.end_date >= CURDATE()
";

if ($category !== 'all') {
    $query .= " AND c.category = :category";
}

$query .= " GROUP BY c.campaign_id ORDER BY c.end_date ASC, c.created_at DESC";

$stmt = $db->prepare($query);
if ($category !== 'all') {
    $stmt->bindValue(':category', $category);
}
$stmt->execute();
$campaigns = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Campaigns - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .campaign-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .campaign-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .campaign-image {
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="campaigns.php">
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
        <div class="text-center mb-4">
            <h2 class="fw-bold">Browse Active Campaigns</h2>
            <p class="text-muted">Support our ongoing initiatives for MRSM Kota Putra</p>
        </div>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="?category=all" class="btn btn-<?= $category === 'all' ? 'primary' : 'outline-primary' ?>">All</a>
                    <a href="?category=education" class="btn btn-<?= $category === 'education' ? 'primary' : 'outline-primary' ?>">Education</a>
                    <a href="?category=infrastructure" class="btn btn-<?= $category === 'infrastructure' ? 'primary' : 'outline-primary' ?>">Infrastructure</a>
                    <a href="?category=welfare" class="btn btn-<?= $category === 'welfare' ? 'primary' : 'outline-primary' ?>">Welfare</a>
                    <a href="?category=emergency" class="btn btn-<?= $category === 'emergency' ? 'primary' : 'outline-primary' ?>">Emergency</a>
                    <a href="?category=other" class="btn btn-<?= $category === 'other' ? 'primary' : 'outline-primary' ?>">Other</a>
                </div>
            </div>
        </div>
        
        <!-- Campaigns Grid -->
        <?php if (empty($campaigns)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <p class="text-muted">No active campaigns found</p>
                <a href="?category=all" class="btn btn-primary">View All Campaigns</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($campaigns as $campaign): ?>
                    <?php $percentage = calculatePercentage($campaign['total_raised'], $campaign['target_amount']); ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card campaign-card h-100">
                            <?php if ($campaign['banner_image']): ?>
                                <img src="../<?= htmlspecialchars($campaign['banner_image']) ?>" class="card-img-top campaign-image" alt="Campaign">
                            <?php else: ?>
                                <div class="campaign-image d-flex align-items-center justify-content-center text-white">
                                    <i class="fas fa-image fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary"><?= ucfirst($campaign['category']) ?></span>
                                    <?php if ($campaign['days_remaining'] <= 7): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i><?= $campaign['days_remaining'] ?> days left
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <h5 class="card-title"><?= htmlspecialchars($campaign['campaign_name']) ?></h5>
                                <p class="card-text text-muted small"><?= substr(htmlspecialchars($campaign['description']), 0, 100) ?>...</p>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="fw-bold text-success"><?= formatCurrency($campaign['total_raised']) ?></small>
                                        <small class="text-muted">of <?= formatCurrency($campaign['target_amount']) ?></small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted"><?= $percentage ?>% funded</small>
                                        <small class="text-muted"><?= $campaign['donation_count'] ?> donors</small>
                                    </div>
                                </div>
                                
                                <a href="campaign_view.php?id=<?= $campaign['campaign_id'] ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-hand-holding-heart me-2"></i>Donate Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

