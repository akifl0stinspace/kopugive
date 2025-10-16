<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Get report type
$reportType = $_GET['type'] ?? 'summary';

// Summary Statistics
$summaryStats = [];
$stmt = $db->query("SELECT COUNT(*) as total, status FROM campaigns GROUP BY status");
$summaryStats['campaigns'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $db->query("SELECT COUNT(*) as total, status FROM donations GROUP BY status");
$summaryStats['donations'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $db->query("SELECT SUM(amount) as total FROM donations WHERE status = 'verified'");
$summaryStats['total_raised'] = $stmt->fetch()['total'] ?? 0;

$stmt = $db->query("SELECT COUNT(DISTINCT donor_id) as total FROM donations WHERE donor_id IS NOT NULL");
$summaryStats['total_donors'] = $stmt->fetch()['total'];

// Monthly donations
$stmt = $db->query("
    SELECT DATE_FORMAT(donation_date, '%Y-%m') as month, 
           COUNT(*) as count, 
           SUM(amount) as total
    FROM donations 
    WHERE status = 'verified'
    GROUP BY DATE_FORMAT(donation_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
$monthlyData = $stmt->fetchAll();

// Top campaigns
$stmt = $db->query("
    SELECT c.campaign_name, 
           COUNT(d.donation_id) as donation_count,
           SUM(d.amount) as total_raised,
           c.target_amount
    FROM campaigns c
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id AND d.status = 'verified'
    GROUP BY c.campaign_id
    ORDER BY total_raised DESC
    LIMIT 10
");
$topCampaigns = $stmt->fetchAll();

// Top donors
$stmt = $db->query("
    SELECT u.full_name, u.email,
           COUNT(d.donation_id) as donation_count,
           SUM(d.amount) as total_donated
    FROM users u
    INNER JOIN donations d ON u.user_id = d.donor_id
    WHERE d.status = 'verified'
    GROUP BY u.user_id
    ORDER BY total_donated DESC
    LIMIT 10
");
$topDonors = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt me-2"></i>Reports & Analytics</h2>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Print Report
            </button>
        </div>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <h6 class="text-muted">Total Raised</h6>
                        <h3 class="text-success"><?= formatCurrency($summaryStats['total_raised']) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body">
                        <h6 class="text-muted">Verified Donations</h6>
                        <h3 class="text-info"><?= $summaryStats['donations']['verified'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <h6 class="text-muted">Pending</h6>
                        <h3 class="text-warning"><?= $summaryStats['donations']['pending'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body">
                        <h6 class="text-muted">Total Donors</h6>
                        <h3 class="text-primary"><?= $summaryStats['total_donors'] ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Donation Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="80"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Campaign Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tables -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Campaigns</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Campaign</th>
                                        <th>Raised</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topCampaigns as $campaign): ?>
                                        <?php $perc = calculatePercentage($campaign['total_raised'] ?? 0, $campaign['target_amount']); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($campaign['campaign_name']) ?></td>
                                            <td><?= formatCurrency($campaign['total_raised'] ?? 0) ?></td>
                                            <td>
                                                <div class="progress" style="height: 10px;">
                                                    <div class="progress-bar bg-success" style="width: <?= $perc ?>%"></div>
                                                </div>
                                                <small><?= $perc ?>%</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-medal me-2"></i>Top Donors</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Donor</th>
                                        <th>Donations</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topDonors as $donor): ?>
                                        <tr>
                                            <td>
                                                <div><?= htmlspecialchars($donor['full_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($donor['email']) ?></small>
                                            </td>
                                            <td><?= $donor['donation_count'] ?></td>
                                            <td><strong class="text-success"><?= formatCurrency($donor['total_donated']) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Monthly Donations Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_reverse(array_column($monthlyData, 'month'))) ?>,
                datasets: [{
                    label: 'Total Raised (RM)',
                    data: <?= json_encode(array_reverse(array_column($monthlyData, 'total'))) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
        
        // Campaign Status Pie Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Draft', 'Completed', 'Closed'],
                datasets: [{
                    data: [
                        <?= $summaryStats['campaigns']['active'] ?? 0 ?>,
                        <?= $summaryStats['campaigns']['draft'] ?? 0 ?>,
                        <?= $summaryStats['campaigns']['completed'] ?? 0 ?>,
                        <?= $summaryStats['campaigns']['closed'] ?? 0 ?>
                    ],
                    backgroundColor: ['#28a745', '#6c757d', '#17a2b8', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
    </script>
</body>
</html>

