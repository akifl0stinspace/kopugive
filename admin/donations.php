<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Handle donation verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'verify' && isset($_POST['donation_id'])) {
        $donationId = $_POST['donation_id'];
        
        // Get donation details
        $stmt = $db->prepare("SELECT * FROM donations WHERE donation_id = ?");
        $stmt->execute([$donationId]);
        $donation = $stmt->fetch();
        
        if ($donation) {
            // Update donation status
            $stmt = $db->prepare("UPDATE donations SET status = 'verified', verified_by = ?, verified_at = NOW() WHERE donation_id = ?");
            $stmt->execute([$_SESSION['user_id'], $donationId]);
            
            // Update campaign total
            $stmt = $db->prepare("UPDATE campaigns SET current_amount = current_amount + ? WHERE campaign_id = ?");
            $stmt->execute([$donation['amount'], $donation['campaign_id']]);
            
            logActivity($db, $_SESSION['user_id'], 'Donation verified', 'donation', $donationId);
            setFlashMessage('success', 'Donation verified successfully');
        }
        redirect('donations.php');
    }
    
    if ($_POST['action'] === 'reject' && isset($_POST['donation_id'])) {
        $stmt = $db->prepare("UPDATE donations SET status = 'rejected', verified_by = ?, verified_at = NOW() WHERE donation_id = ?");
        $stmt->execute([$_SESSION['user_id'], $_POST['donation_id']]);
        logActivity($db, $_SESSION['user_id'], 'Donation rejected', 'donation', $_POST['donation_id']);
        setFlashMessage('warning', 'Donation rejected');
        redirect('donations.php');
    }
}

// Fetch donations with filter
$status = $_GET['status'] ?? 'all';
$query = "
    SELECT d.*, c.campaign_name, u.full_name as donor_full_name, v.full_name as verifier_name
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    LEFT JOIN users u ON d.donor_id = u.user_id
    LEFT JOIN users v ON d.verified_by = v.user_id
";

if ($status !== 'all') {
    $query .= " WHERE d.status = :status";
}

$query .= " ORDER BY d.created_at DESC";

$stmt = $db->prepare($query);
if ($status !== 'all') {
    $stmt->bindValue(':status', $status);
}
$stmt->execute();
$donations = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-hand-holding-usd me-2"></i>Donations</h2>
        </div>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="?status=all" class="btn btn-<?= $status === 'all' ? 'primary' : 'outline-primary' ?>">All</a>
                    <a href="?status=pending" class="btn btn-<?= $status === 'pending' ? 'warning' : 'outline-warning' ?>">Pending</a>
                    <a href="?status=verified" class="btn btn-<?= $status === 'verified' ? 'success' : 'outline-success' ?>">Verified</a>
                    <a href="?status=rejected" class="btn btn-<?= $status === 'rejected' ? 'danger' : 'outline-danger' ?>">Rejected</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Donor</th>
                                <th>Campaign</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Receipt</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td>#<?= $donation['donation_id'] ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($donation['donor_full_name'] ?? $donation['donor_name'] ?? 'Anonymous') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($donation['donor_email'] ?? '') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($donation['campaign_name']) ?></td>
                                    <td><strong class="text-success"><?= formatCurrency($donation['amount']) ?></strong></td>
                                    <td>
                                        <small><?= ucfirst(str_replace('_', ' ', $donation['payment_method'])) ?></small><br>
                                        <small class="text-muted"><?= htmlspecialchars($donation['transaction_id'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <?php if ($donation['receipt_path']): ?>
                                            <a href="../<?= htmlspecialchars($donation['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-image"></i>
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
                                        ?>
                                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($donation['status']) ?></span>
                                        <?php if ($donation['verified_at']): ?>
                                            <br><small class="text-muted">by <?= htmlspecialchars($donation['verifier_name'] ?? 'System') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formatDate($donation['donation_date'], 'd M Y H:i') ?></td>
                                    <td>
                                        <?php if ($donation['status'] === 'pending'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="verify">
                                                <input type="hidden" name="donation_id" value="<?= $donation['donation_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Verify this donation?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="donation_id" value="<?= $donation['donation_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this donation?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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

