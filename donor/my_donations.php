<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();
$userId = $_SESSION['user_id'];

// Get all donations by this user
$stmt = $db->prepare("
    SELECT d.*, c.campaign_name
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    WHERE d.donor_id = ?
    ORDER BY d.created_at DESC
");
$stmt->execute([$userId]);
$donations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-hand-holding-heart me-2"></i>KopuGive
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="campaigns.php">Browse Campaigns</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="my_donations.php">My Donations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-history me-2"></i>My Donation History</h2>
        
        <?php if (empty($donations)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <h5>No donations yet</h5>
                    <p class="text-muted">Start making a difference by donating to our campaigns</p>
                    <a href="campaigns.php" class="btn btn-primary">Browse Campaigns</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Campaign</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Receipt</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td><?= formatDate($donation['donation_date'], 'd M Y H:i') ?></td>
                                        <td>
                                            <a href="campaign_view.php?id=<?= $donation['campaign_id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($donation['campaign_name']) ?>
                                            </a>
                                        </td>
                                        <td><strong class="text-success"><?= formatCurrency($donation['amount']) ?></strong></td>
                                        <td><small><?= ucfirst(str_replace('_', ' ', $donation['payment_method'])) ?></small></td>
                                        <td>
                                            <?php if ($donation['receipt_path']): ?>
                                                <a href="../<?= htmlspecialchars($donation['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-image"></i> View
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No receipt</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badges = [
                                                'pending' => 'warning',
                                                'verified' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                            $badge = $badges[$donation['status']] ?? 'secondary';
                                            $icons = [
                                                'pending' => 'clock',
                                                'verified' => 'check-circle',
                                                'rejected' => 'times-circle'
                                            ];
                                            $icon = $icons[$donation['status']] ?? 'circle';
                                            ?>
                                            <span class="badge bg-<?= $badge ?>">
                                                <i class="fas fa-<?= $icon ?> me-1"></i><?= ucfirst($donation['status']) ?>
                                            </span>
                                            <?php if ($donation['status'] === 'pending'): ?>
                                                <br><small class="text-muted">Awaiting verification</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

