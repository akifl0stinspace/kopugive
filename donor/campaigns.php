<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

$db = (new Database())->getConnection();

// Filters
$search = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';

// Build query
$where = ["c.status = 'active'"];
$params = [];
if ($search !== '') {
    $where[] = '(c.campaign_name LIKE ? OR c.description LIKE ?)';
    $like = "%$search%";
    $params[] = $like; $params[] = $like;
}
if ($category !== '') {
    $where[] = 'c.category = ?';
    $params[] = $category;
}

$sql = "
    SELECT c.*, 
           COALESCE(SUM(CASE WHEN d.status = 'verified' THEN d.amount ELSE 0 END), 0) as total_raised,
           COUNT(DISTINCT d.donation_id) as donation_count
    FROM campaigns c
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id
    WHERE " . implode(' AND ', $where) . "
    GROUP BY c.campaign_id
    ORDER BY c.created_at DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$campaigns = $stmt->fetchAll();

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
        .campaign-card { transition: transform .2s ease, box-shadow .2s ease; }
        .campaign-card:hover { transform: translateY(-4px); box-shadow: 0 1rem 2rem rgba(0,0,0,.15); }
        .banner { height: 160px; object-fit: cover; background: #eef2ff; }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="../index.php">
                <i class="fas fa-hand-holding-heart me-2"></i>KopuGive
            </a>
            <div class="ms-auto">
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-outline-primary"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-outline-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <h2 class="mb-0">Browse Campaigns</h2>
        </div>

        <!-- Filters -->
        <form class="row g-2 mb-4" method="get">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search campaigns..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach (['education','infrastructure','welfare','emergency','other'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-grid d-md-block">
                <button class="btn btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
                <a href="campaigns.php" class="btn btn-outline-secondary ms-md-2">Reset</a>
            </div>
        </form>

        <?php if (empty($campaigns)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-folder-open fa-2x mb-2"></i>
                <p class="mb-0">No campaigns found.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($campaigns as $campaign): ?>
                    <?php $percentage = calculatePercentage($campaign['total_raised'] ?? 0, $campaign['target_amount']); ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card campaign-card h-100 border-0 shadow-sm">
                            <?php if (!empty($campaign['banner_image'])): ?>
                                <img class="banner w-100" src="../<?= htmlspecialchars($campaign['banner_image']) ?>" alt="Banner">
                            <?php else: ?>
                                <div class="banner w-100 d-flex align-items-center justify-content-center text-muted">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <span class="badge bg-primary mb-2"><?= ucfirst($campaign['category']) ?></span>
                                <h5 class="card-title"><?= htmlspecialchars($campaign['campaign_name']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars(mb_strimwidth($campaign['description'] ?? '', 0, 120, '…')) ?></p>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span><?= formatCurrency($campaign['total_raised'] ?? 0) ?></span>
                                        <span>of <?= formatCurrency($campaign['target_amount']) ?></span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                </div>
                                <small class="text-muted"><i class="fas fa-users me-1"></i><?= (int)$campaign['donation_count'] ?> donors</small>
                            </div>
                            <div class="card-footer bg-white">
                                <a class="btn btn-primary w-100" href="campaign_view.php?id=<?= (int)$campaign['campaign_id'] ?>">
                                    <i class="fas fa-heart me-2"></i>Donate / View
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


