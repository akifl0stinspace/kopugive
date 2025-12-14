<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Get filter and search
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';

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

if (!empty($search)) {
    $query .= " AND (c.campaign_name LIKE :search OR c.description LIKE :search)";
}

$query .= " GROUP BY c.campaign_id ORDER BY c.end_date ASC, c.created_at DESC";

$stmt = $db->prepare($query);
if ($category !== 'all') {
    $stmt->bindValue(':category', $category);
}
if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%');
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
    <?php include '../includes/theme_styles.php'; ?>
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
        
        <!-- Search Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Search campaigns by name or description..." 
                                   value="<?= htmlspecialchars($search) ?>"
                                   aria-label="Search campaigns">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                    <?php if (!empty($search) || $category !== 'all'): ?>
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">Active filters:</span>
                                <?php if (!empty($search)): ?>
                                    <span class="badge bg-secondary">
                                        Search: "<?= htmlspecialchars($search) ?>"
                                        <a href="?category=<?= $category ?>" class="text-white text-decoration-none ms-1">×</a>
                                    </span>
                                <?php endif; ?>
                                <?php if ($category !== 'all'): ?>
                                    <span class="badge bg-secondary">
                                        Category: <?= ucfirst($category) ?>
                                        <a href="?search=<?= urlencode($search) ?>" class="text-white text-decoration-none ms-1">×</a>
                                    </span>
                                <?php endif; ?>
                                <a href="campaigns.php" class="btn btn-sm btn-outline-secondary ms-2">Clear All</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- Hidden field to preserve category when searching -->
                    <?php if ($category !== 'all'): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-filter me-2"></i>Filter by Category</h6>
                <div class="btn-group flex-wrap" role="group">
                    <a href="?category=all<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-<?= $category === 'all' ? 'primary' : 'outline-primary' ?>">All</a>
                    <a href="?category=education<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-<?= $category === 'education' ? 'primary' : 'outline-primary' ?>">Education</a>
                    <a href="?category=infrastructure<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-<?= $category === 'infrastructure' ? 'primary' : 'outline-primary' ?>">Infrastructure</a>
                    <a href="?category=welfare<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-<?= $category === 'welfare' ? 'primary' : 'outline-primary' ?>">Welfare</a>
                    <a href="?category=emergency<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-<?= $category === 'emergency' ? 'primary' : 'outline-primary' ?>">Emergency</a>
                    <a href="?category=other<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="btn btn-<?= $category === 'other' ? 'primary' : 'outline-primary' ?>">Other</a>
                </div>
            </div>
        </div>
        
        <!-- Results Summary -->
        <?php if (!empty($search) || $category !== 'all'): ?>
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                Found <strong><?= count($campaigns) ?></strong> campaign(s)
                <?php if (!empty($search)): ?>
                    matching "<strong><?= htmlspecialchars($search) ?></strong>"
                <?php endif; ?>
                <?php if ($category !== 'all'): ?>
                    in <strong><?= ucfirst($category) ?></strong> category
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Campaigns Grid -->
        <?php if (empty($campaigns)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <?php if (!empty($search)): ?>
                    <p class="text-muted">No campaigns found matching your search</p>
                    <p class="text-muted small">Try different keywords or <a href="?category=<?= $category ?>">clear the search</a></p>
                <?php else: ?>
                    <p class="text-muted">No active campaigns found in this category</p>
                    <a href="?category=all" class="btn btn-primary">View All Campaigns</a>
                <?php endif; ?>
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

