<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Get filter parameters
$campaignFilter = $_GET['campaign_id'] ?? 'all';
$periodFilter = $_GET['period'] ?? 'all';
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Calculate date range based on period
$dateCondition = "";
$dateParams = [];

if ($periodFilter !== 'all' && $periodFilter !== 'custom') {
    $now = new DateTime();
    switch ($periodFilter) {
        case 'today':
            $startDate = $now->format('Y-m-d');
            $endDate = $now->format('Y-m-d');
            break;
        case 'week':
            $startDate = $now->modify('-7 days')->format('Y-m-d');
            $endDate = (new DateTime())->format('Y-m-d');
            break;
        case 'month':
            $startDate = $now->modify('-30 days')->format('Y-m-d');
            $endDate = (new DateTime())->format('Y-m-d');
            break;
        case 'quarter':
            $startDate = $now->modify('-90 days')->format('Y-m-d');
            $endDate = (new DateTime())->format('Y-m-d');
            break;
        case 'year':
            $startDate = $now->modify('-365 days')->format('Y-m-d');
            $endDate = (new DateTime())->format('Y-m-d');
            break;
    }
}

if ($startDate && $endDate) {
    $dateCondition = " AND DATE(d.donation_date) BETWEEN :start_date AND :end_date";
    $dateParams = [':start_date' => $startDate, ':end_date' => $endDate];
}

// Campaign filter condition
$campaignCondition = "";
$campaignParams = [];
if ($campaignFilter !== 'all') {
    $campaignCondition = " AND c.campaign_id = :campaign_id";
    $campaignParams = [':campaign_id' => $campaignFilter];
}

// Get all campaigns for dropdown
$stmt = $db->query("SELECT campaign_id, campaign_name FROM campaigns ORDER BY campaign_name");
$allCampaigns = $stmt->fetchAll();

// Summary Statistics
$summaryStats = [];

// Campaign stats with filters
$query = "SELECT COUNT(*) as total, status FROM campaigns WHERE 1=1";
if ($campaignFilter !== 'all') {
    $query .= " AND campaign_id = :campaign_id";
}
$query .= " GROUP BY status";
$stmt = $db->prepare($query);
if ($campaignFilter !== 'all') {
    $stmt->execute([':campaign_id' => $campaignFilter]);
} else {
    $stmt->execute();
}
$summaryStats['campaigns'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Donation stats with filters
$query = "SELECT COUNT(*) as total, d.status FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE 1=1" . $campaignCondition . $dateCondition . " GROUP BY d.status";
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$summaryStats['donations'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Total raised with filters
$query = "SELECT SUM(d.amount) as total FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE d.status = 'verified'" . $campaignCondition . $dateCondition;
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$summaryStats['total_raised'] = $stmt->fetch()['total'] ?? 0;

// Total donors with filters
$query = "SELECT COUNT(DISTINCT d.donor_id) as total FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE d.donor_id IS NOT NULL" . $campaignCondition . $dateCondition;
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$summaryStats['total_donors'] = $stmt->fetch()['total'];

// Average donation
$query = "SELECT AVG(d.amount) as average FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE d.status = 'verified'" . $campaignCondition . $dateCondition;
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$summaryStats['avg_donation'] = $stmt->fetch()['average'] ?? 0;

// Monthly donations with filters
$query = "SELECT DATE_FORMAT(d.donation_date, '%Y-%m') as month, 
           COUNT(*) as count, 
           SUM(d.amount) as total
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    WHERE d.status = 'verified'" . $campaignCondition . $dateCondition . "
    GROUP BY DATE_FORMAT(d.donation_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12";
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$monthlyData = $stmt->fetchAll();

// Top campaigns with filters
$query = "SELECT c.campaign_name, 
           c.campaign_id,
           COUNT(d.donation_id) as donation_count,
           SUM(d.amount) as total_raised,
           c.target_amount,
           c.status as campaign_status
    FROM campaigns c
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id AND d.status = 'verified'
    WHERE 1=1" . $campaignCondition . $dateCondition . "
    GROUP BY c.campaign_id ORDER BY total_raised DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$topCampaigns = $stmt->fetchAll();

// Top donors with filters
$query = "SELECT u.full_name, u.email,
           COUNT(d.donation_id) as donation_count,
           SUM(d.amount) as total_donated
    FROM users u
    INNER JOIN donations d ON u.user_id = d.donor_id
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    WHERE d.status = 'verified'" . $campaignCondition . $dateCondition . "
    GROUP BY u.user_id
    ORDER BY total_donated DESC
    LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$topDonors = $stmt->fetchAll();

// Individual campaign details (if specific campaign selected)
$campaignDetails = null;
if ($campaignFilter !== 'all') {
    $stmt = $db->prepare("
        SELECT c.*, 
               COUNT(DISTINCT d.donation_id) as total_donations,
               COUNT(DISTINCT d.donor_id) as unique_donors,
               SUM(CASE WHEN d.status = 'verified' THEN d.amount ELSE 0 END) as total_raised,
               SUM(CASE WHEN d.status = 'pending' THEN d.amount ELSE 0 END) as pending_amount,
               AVG(CASE WHEN d.status = 'verified' THEN d.amount ELSE NULL END) as avg_donation
        FROM campaigns c
        LEFT JOIN donations d ON c.campaign_id = d.campaign_id
        WHERE c.campaign_id = :campaign_id
        GROUP BY c.campaign_id
    ");
    $stmt->execute([':campaign_id' => $campaignFilter]);
    $campaignDetails = $stmt->fetch();
    
    // Get donation timeline for the campaign
    $stmt = $db->prepare("
        SELECT DATE(donation_date) as date,
               COUNT(*) as count,
               SUM(amount) as total
        FROM donations
        WHERE campaign_id = :campaign_id AND status = 'verified'
        GROUP BY DATE(donation_date)
        ORDER BY date DESC
        LIMIT 30
    ");
    $stmt->execute([':campaign_id' => $campaignFilter]);
    $campaignTimeline = $stmt->fetchAll();
}

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
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="col-md-10 ms-sm-auto px-md-4 py-4" style="margin-left: 16.666667%;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt me-2"></i>Reports & Analytics</h2>
            <div>
                <button onclick="exportReport('csv')" class="btn btn-success me-2">
                    <i class="fas fa-file-csv me-2"></i>Export CSV
                </button>
                <a href="generate_report.php?campaign_id=<?= $campaignFilter ?>&period=<?= $periodFilter ?><?= $startDate ? '&start_date='.$startDate : '' ?><?= $endDate ? '&end_date='.$endDate : '' ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-file-alt me-2"></i>Generate Report
                </a>
            </div>
        </div>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Filter Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Report Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Campaign</label>
                            <select name="campaign_id" class="form-select" id="campaignSelect">
                                <option value="all" <?= $campaignFilter === 'all' ? 'selected' : '' ?>>All Campaigns</option>
                                <?php foreach ($allCampaigns as $camp): ?>
                                    <option value="<?= $camp['campaign_id'] ?>" <?= $campaignFilter == $camp['campaign_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($camp['campaign_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Time Period</label>
                            <select name="period" class="form-select" id="periodSelect">
                                <option value="all" <?= $periodFilter === 'all' ? 'selected' : '' ?>>All Time</option>
                                <option value="today" <?= $periodFilter === 'today' ? 'selected' : '' ?>>Today</option>
                                <option value="week" <?= $periodFilter === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                                <option value="month" <?= $periodFilter === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                                <option value="quarter" <?= $periodFilter === 'quarter' ? 'selected' : '' ?>>Last 90 Days</option>
                                <option value="year" <?= $periodFilter === 'year' ? 'selected' : '' ?>>Last Year</option>
                                <option value="custom" <?= $periodFilter === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2" id="startDateDiv" style="display: <?= $periodFilter === 'custom' ? 'block' : 'none' ?>;">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-select" value="<?= htmlspecialchars($startDate ?? '') ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-2" id="endDateDiv" style="display: <?= $periodFilter === 'custom' ? 'block' : 'none' ?>;">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-select" value="<?= htmlspecialchars($endDate ?? '') ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
                
                <?php if ($campaignFilter !== 'all' || $periodFilter !== 'all'): ?>
                    <div class="mt-3">
                        <span class="badge bg-info me-2">
                            <i class="fas fa-filter me-1"></i>Filters Active
                        </span>
                        <?php if ($campaignFilter !== 'all'): ?>
                            <span class="badge bg-secondary me-2">
                                Campaign: <?= htmlspecialchars($campaignDetails['campaign_name'] ?? 'Selected') ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($periodFilter !== 'all'): ?>
                            <span class="badge bg-secondary me-2">
                                Period: <?= ucfirst($periodFilter) ?>
                                <?php if ($startDate && $endDate): ?>
                                    (<?= date('M d, Y', strtotime($startDate)) ?> - <?= date('M d, Y', strtotime($endDate)) ?>)
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                        <a href="reports.php" class="badge bg-danger text-decoration-none">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($campaignFilter !== 'all' && $campaignDetails): ?>
        <!-- Individual Campaign Report -->
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Campaign Details: <?= htmlspecialchars($campaignDetails['campaign_name']) ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-2"><strong>Description:</strong> <?= htmlspecialchars($campaignDetails['description']) ?></p>
                        <p class="mb-2"><strong>Created:</strong> <?= date('M d, Y', strtotime($campaignDetails['created_at'])) ?></p>
                        <p class="mb-2"><strong>Target Amount:</strong> <?= formatCurrency($campaignDetails['target_amount']) ?></p>
                        <p class="mb-2"><strong>Status:</strong> 
                            <?php
                            $status = $campaignDetails['campaign_status'];
                            $statusDisplay = [
                                'active' => ['text' => 'Active', 'color' => 'success'],
                                'completed' => ['text' => 'Ended', 'color' => 'info'],
                                'closed' => ['text' => 'Closed', 'color' => 'danger'],
                                'draft' => ['text' => 'Draft', 'color' => 'secondary']
                            ];
                            $display = $statusDisplay[$status] ?? ['text' => ucfirst($status), 'color' => 'secondary'];
                            ?>
                            <span class="badge bg-<?= $display['color'] ?>"><?= $display['text'] ?></span>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <?php if ($campaignDetails['image_url']): ?>
                                <img src="../<?= htmlspecialchars($campaignDetails['image_url']) ?>" 
                                     alt="Campaign" class="img-fluid rounded" style="max-height: 150px;">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-primary shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Raised</h6>
                                <h3 class="text-success mb-0"><?= formatCurrency($summaryStats['total_raised']) ?></h3>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-hand-holding-usd fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Pending</h6>
                                <h3 class="text-warning mb-0"><?= $summaryStats['donations']['pending'] ?? 0 ?></h3>
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Donors</h6>
                                <h3 class="text-primary mb-0"><?= $summaryStats['total_donors'] ?></h3>
                            </div>
                            <div class="text-primary">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($campaignFilter !== 'all' && $campaignDetails): ?>
            <div class="col-md-3">
                <div class="card border-secondary shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Avg Donation</h6>
                                <h3 class="text-secondary mb-0"><?= formatCurrency($summaryStats['avg_donation']) ?></h3>
                            </div>
                            <div class="text-secondary">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Progress</h6>
                                <h3 class="text-danger mb-0"><?= calculatePercentage($campaignDetails['total_raised'], $campaignDetails['target_amount']) ?>%</h3>
                            </div>
                            <div class="text-danger">
                                <i class="fas fa-percentage fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Pending Amount</h6>
                                <h3 class="text-warning mb-0"><?= formatCurrency($campaignDetails['pending_amount']) ?></h3>
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-hourglass-half fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        
        <!-- Tables -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>
                            <?= $campaignFilter !== 'all' ? 'Campaign Performance' : 'Top Campaigns' ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Campaign</th>
                                        <th>Donations</th>
                                        <th>Raised</th>
                                        <th>Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($topCampaigns) > 0): ?>
                                        <?php foreach ($topCampaigns as $campaign): ?>
                                            <?php $perc = calculatePercentage($campaign['total_raised'] ?? 0, $campaign['target_amount']); ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($campaign['campaign_name']) ?></div>
                                                    <small class="text-muted">Target: <?= formatCurrency($campaign['target_amount']) ?></small>
                                                </td>
                                                <td><span class="badge bg-info"><?= $campaign['donation_count'] ?></span></td>
                                                <td class="text-success fw-bold"><?= formatCurrency($campaign['total_raised'] ?? 0) ?></td>
                                                <td style="min-width: 120px;">
                                                    <div class="progress" style="height: 12px;">
                                                        <div class="progress-bar bg-success" style="width: <?= min($perc, 100) ?>%"></div>
                                                    </div>
                                                    <small class="fw-bold"><?= $perc ?>%</small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">No data available for selected filters</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-medal me-2"></i>Top Donors</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Donor</th>
                                        <th>Donations</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($topDonors) > 0): ?>
                                        <?php $rank = 1; foreach ($topDonors as $donor): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($rank <= 3): ?>
                                                        <span class="badge bg-<?= $rank === 1 ? 'warning' : ($rank === 2 ? 'secondary' : 'danger') ?>">
                                                            <?= $rank ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <?= $rank ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($donor['full_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($donor['email']) ?></small>
                                                </td>
                                                <td><span class="badge bg-primary"><?= $donor['donation_count'] ?></span></td>
                                                <td><strong class="text-success"><?= formatCurrency($donor['total_donated']) ?></strong></td>
                                            </tr>
                                        <?php $rank++; endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">No donors found for selected filters</td>
                                        </tr>
                                    <?php endif; ?>
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
        // Period select handler
        document.getElementById('periodSelect').addEventListener('change', function() {
            const customDivs = ['startDateDiv', 'endDateDiv'];
            if (this.value === 'custom') {
                customDivs.forEach(id => document.getElementById(id).style.display = 'block');
            } else {
                customDivs.forEach(id => document.getElementById(id).style.display = 'none');
            }
        });
        
        // Export function
        function exportReport(format) {
            const params = new URLSearchParams(window.location.search);
            params.set('export', format);
            
            if (format === 'csv') {
                // Create CSV content
                let csv = 'KopuGive Report - Generated on ' + new Date().toLocaleString() + '\n\n';
                
                // Summary Stats
                csv += 'Summary Statistics\n';
                csv += 'Total Raised,<?= $summaryStats['total_raised'] ?>\n';
                csv += 'Verified Donations,<?= $summaryStats['donations']['verified'] ?? 0 ?>\n';
                csv += 'Pending Donations,<?= $summaryStats['donations']['pending'] ?? 0 ?>\n';
                csv += 'Total Donors,<?= $summaryStats['total_donors'] ?>\n\n';
                
                // Top Campaigns
                csv += 'Top Campaigns\n';
                csv += 'Campaign Name,Donations,Amount Raised,Target Amount,Progress %\n';
                <?php foreach ($topCampaigns as $campaign): ?>
                csv += '<?= addslashes($campaign['campaign_name']) ?>,<?= $campaign['donation_count'] ?>,<?= $campaign['total_raised'] ?? 0 ?>,<?= $campaign['target_amount'] ?>,<?= calculatePercentage($campaign['total_raised'] ?? 0, $campaign['target_amount']) ?>\n';
                <?php endforeach; ?>
                
                csv += '\nTop Donors\n';
                csv += 'Donor Name,Email,Donation Count,Total Donated\n';
                <?php foreach ($topDonors as $donor): ?>
                csv += '<?= addslashes($donor['full_name']) ?>,<?= addslashes($donor['email']) ?>,<?= $donor['donation_count'] ?>,<?= $donor['total_donated'] ?>\n';
                <?php endforeach; ?>
                
                // Download CSV
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'kopugive_report_' + new Date().toISOString().split('T')[0] + '.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }
        }
    </script>
</body>
</html>

