<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Handle campaign actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && isset($_POST['campaign_id'], $_POST['status'])) {
        $stmt = $db->prepare("UPDATE campaigns SET status = ? WHERE campaign_id = ?");
        $stmt->execute([$_POST['status'], $_POST['campaign_id']]);
        logActivity($db, $_SESSION['user_id'], 'Campaign status updated', 'campaign', $_POST['campaign_id']);
        setFlashMessage('success', 'Campaign status updated successfully');
        redirect('campaigns.php');
    }
}

// Fetch campaigns
$stmt = $db->query("
    SELECT c.*, 
           u.full_name as created_by_name,
           COUNT(DISTINCT d.donation_id) as donation_count,
           COALESCE(SUM(CASE WHEN d.status = 'verified' THEN d.amount ELSE 0 END), 0) as total_raised
    FROM campaigns c
    LEFT JOIN users u ON c.created_by = u.user_id
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id
    GROUP BY c.campaign_id
    ORDER BY c.created_at DESC
");
$campaigns = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaigns - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-bullhorn me-2"></i>Campaigns</h2>
            <a href="campaign_add.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Campaign
            </a>
        </div>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Target</th>
                                <th>Raised</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Period</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $campaign): ?>
                                <?php $percentage = calculatePercentage($campaign['total_raised'], $campaign['target_amount']); ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($campaign['campaign_name']) ?></div>
                                        <small class="text-muted"><?= $campaign['donation_count'] ?> donations</small>
                                    </td>
                                    <td><?= formatCurrency($campaign['target_amount']) ?></td>
                                    <td><strong class="text-success"><?= formatCurrency($campaign['total_raised']) ?></strong></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: <?= $percentage ?>%">
                                                <?= $percentage ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'draft' => 'secondary',
                                            'active' => 'success',
                                            'completed' => 'info',
                                            'closed' => 'danger'
                                        ];
                                        $badge = $statusBadges[$campaign['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($campaign['status']) ?></span>
                                    </td>
                                    <td>
                                        <small><?= formatDate($campaign['start_date'], 'd M') ?> - <?= formatDate($campaign['end_date'], 'd M Y') ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="campaign_view.php?id=<?= $campaign['campaign_id'] ?>" class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="campaign_edit.php?id=<?= $campaign['campaign_id'] ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

