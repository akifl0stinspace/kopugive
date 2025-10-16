<?php
session_start();
require_once 'config/config.php';
require_once 'includes/functions.php';

$db = (new Database())->getConnection();

// Get active campaigns
$stmt = $db->query("
    SELECT c.*, 
           COUNT(DISTINCT d.donation_id) as donation_count,
           COALESCE(SUM(CASE WHEN d.status = 'verified' THEN d.amount ELSE 0 END), 0) as total_raised
    FROM campaigns c
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id
    WHERE c.status = 'active' AND c.end_date >= CURDATE()
    GROUP BY c.campaign_id
    ORDER BY c.created_at DESC
    LIMIT 6
");
$campaigns = $stmt->fetchAll();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM campaigns WHERE status = 'active'");
$totalCampaigns = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM donations WHERE status = 'verified'");
$totalDonations = $stmt->fetch()['total'];

$stmt = $db->query("SELECT SUM(amount) as total FROM donations WHERE status = 'verified'");
$totalRaised = $stmt->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KopuGive - MRSM Kota Putra Donation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5rem 0;
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
        .stat-box {
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand text-primary" href="index.php">
                <i class="fas fa-hand-holding-heart me-2"></i>KopuGive
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#campaigns">Campaigns</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= isAdmin() ? 'admin/dashboard.php' : 'donor/dashboard.php' ?>">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary text-white ms-2" href="auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">Welcome to KopuGive</h1>
            <p class="lead mb-4">MRSM Kota Putra's Official Donation Management System</p>
            <p class="mb-4">Connecting hearts, building futures together through transparent and convenient donations</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#campaigns" class="btn btn-light btn-lg">
                    <i class="fas fa-search me-2"></i>Browse Campaigns
                </a>
                <?php if (!isLoggedIn()): ?>
                    <a href="auth/register.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Join Now
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Statistics -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-box text-center">
                        <i class="fas fa-bullhorn fa-3x text-primary mb-3"></i>
                        <h2 class="fw-bold"><?= $totalCampaigns ?></h2>
                        <p class="text-muted mb-0">Active Campaigns</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box text-center">
                        <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                        <h2 class="fw-bold"><?= $totalDonations ?></h2>
                        <p class="text-muted mb-0">Donations Made</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box text-center">
                        <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                        <h2 class="fw-bold"><?= formatCurrency($totalRaised) ?></h2>
                        <p class="text-muted mb-0">Total Raised</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Active Campaigns -->
    <section id="campaigns" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Active Campaigns</h2>
                <p class="text-muted">Support our ongoing initiatives for MRSM Kota Putra</p>
            </div>
            
            <?php if (empty($campaigns)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No active campaigns at the moment</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($campaigns as $campaign): ?>
                        <?php $percentage = calculatePercentage($campaign['total_raised'], $campaign['target_amount']); ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card campaign-card h-100">
                                <?php if ($campaign['banner_image']): ?>
                                    <img src="<?= htmlspecialchars($campaign['banner_image']) ?>" class="card-img-top campaign-image" alt="Campaign">
                                <?php else: ?>
                                    <div class="campaign-image d-flex align-items-center justify-content-center text-white">
                                        <i class="fas fa-image fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <span class="badge bg-primary mb-2"><?= ucfirst($campaign['category']) ?></span>
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
                                    
                                    <a href="donor/campaign_view.php?id=<?= $campaign['campaign_id'] ?>" class="btn btn-primary w-100">
                                        <i class="fas fa-hand-holding-heart me-2"></i>Donate Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="fw-bold mb-4">About KopuGive</h2>
                    <p>KopuGive is MRSM Kota Putra's official donation management platform, managed by the MUAFAKAT committee.</p>
                    <p>Our mission is to provide a transparent, convenient, and secure way for our community to contribute to various school initiatives and support our students' education.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>100% Transparent Tracking</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Secure Payment Processing</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Real-time Campaign Updates</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Digital Receipt Management</li>
                    </ul>
                </div>
                <div class="col-md-6 text-center">
                    <i class="fas fa-school fa-10x text-primary opacity-25"></i>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-2"><i class="fas fa-hand-holding-heart me-2"></i><strong>KopuGive</strong></p>
            <p class="small mb-2">MRSM Kota Putra Donation Management System</p>
            <p class="small text-muted">&copy; <?= date('Y') ?> MRSM Kota Putra. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

