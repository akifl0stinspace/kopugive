<?php
/**
 * Stripe Transactions Dashboard
 * KopuGive - MRSM Kota Putra Donation System
 * 
 * View and manage Stripe payments
 */

session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check authentication and admin role (same pattern as other admin pages)
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT d.*, c.campaign_name, u.full_name as donor_full_name
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    LEFT JOIN users u ON d.donor_id = u.user_id
    WHERE d.payment_method IN ('online_banking', 'card', 'ewallet')
";

$params = [];

if ($status !== 'all') {
    $query .= " AND d.payment_status = ?";
    $params[] = $status;
}

if ($search) {
    $query .= " AND (d.donor_name LIKE ? OR d.transaction_id LIKE ? OR c.campaign_name LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= " ORDER BY d.created_at DESC LIMIT 100";

$stmt = $db->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM donations WHERE payment_method IN ('online_banking', 'card', 'ewallet')")->fetchColumn(),
    'paid' => $db->query("SELECT COUNT(*) FROM donations WHERE payment_method IN ('online_banking', 'card', 'ewallet') AND payment_status = 'paid'")->fetchColumn(),
    'pending' => $db->query("SELECT COUNT(*) FROM donations WHERE payment_method IN ('online_banking', 'card', 'ewallet') AND payment_status = 'pending'")->fetchColumn(),
    'failed' => $db->query("SELECT COUNT(*) FROM donations WHERE payment_method IN ('online_banking', 'card', 'ewallet') AND payment_status = 'failed'")->fetchColumn(),
    'total_amount' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_method IN ('online_banking', 'card', 'ewallet') AND payment_status = 'paid'")->fetchColumn(),
];

$pageTitle = 'Stripe Transactions';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - KopuGive Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <div class="flex-grow-1">
            <div class="container-fluid p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">
                            <i class="fas fa-credit-card text-primary me-2"></i>Stripe Transactions
                        </h2>
                        <p class="text-muted mb-0">Monitor and manage Stripe payments</p>
                    </div>
                    <a href="https://dashboard.stripe.com/payments" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-2"></i>Open Stripe Dashboard
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Total Transactions</p>
                                        <h3 class="fw-bold mb-0"><?= number_format($stats['total']) ?></h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                                        <i class="fas fa-receipt fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Successful</p>
                                        <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['paid']) ?></h3>
                                    </div>
                                    <div class="bg-success bg-opacity-10 p-3 rounded">
                                        <i class="fas fa-check-circle fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Pending</p>
                                        <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['pending']) ?></h3>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                                        <i class="fas fa-clock fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Total Amount</p>
                                        <h3 class="fw-bold mb-0 text-success"><?= formatCurrency($stats['total_amount']) ?></h3>
                                    </div>
                                    <div class="bg-success bg-opacity-10 p-3 rounded">
                                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Status</option>
                                    <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    <option value="refunded" <?= $status === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search by donor, transaction ID, or campaign..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Recent Transactions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Donor</th>
                                        <th>Campaign</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Transaction ID</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($transactions)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                No transactions found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $txn): ?>
                                            <tr>
                                                <td><?= formatDate($txn['created_at'], 'd M Y H:i') ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($txn['donor_name']) ?></strong>
                                                    <?php if ($txn['donor_email']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($txn['donor_email']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($txn['campaign_name']) ?></td>
                                                <td class="fw-bold"><?= formatCurrency($txn['amount']) ?></td>
                                                <td>
                                                    <?php
                                                    $methodIcons = [
                                                        'online_banking' => 'fa-university',
                                                        'card' => 'fa-credit-card',
                                                        'ewallet' => 'fa-wallet'
                                                    ];
                                                    $icon = $methodIcons[$txn['payment_method']] ?? 'fa-money-bill';
                                                    ?>
                                                    <i class="fas <?= $icon ?> me-1"></i>
                                                    <?= ucfirst(str_replace('_', ' ', $txn['payment_method'])) ?>
                                                </td>
                                                <td>
                                                    <small class="font-monospace"><?= htmlspecialchars($txn['transaction_id'] ?? 'N/A') ?></small>
                                                    <?php if ($txn['stripe_payment_intent_id']): ?>
                                                        <br><a href="https://dashboard.stripe.com/payments/<?= htmlspecialchars($txn['stripe_payment_intent_id']) ?>" target="_blank" class="text-decoration-none">
                                                            <small><i class="fas fa-external-link-alt"></i> View in Stripe</small>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    // Simplify status display to Successful or Unsuccessful
                                                    if ($txn['payment_status'] === 'paid') {
                                                        $displayStatus = 'Successful';
                                                        $color = 'success';
                                                    } else {
                                                        $displayStatus = 'Unsuccessful';
                                                        $color = 'danger';
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?= $color ?>">
                                                        <?= $displayStatus ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../donor/my_donations.php?id=<?= $txn['donation_id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> This page shows only Stripe-processed transactions. For manual donations (cash), check the regular Donations page.
                    View detailed transaction information in your <a href="https://dashboard.stripe.com" target="_blank">Stripe Dashboard</a>.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

