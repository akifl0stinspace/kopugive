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
$reportType = $_GET['type'] ?? 'summary'; // 'summary' or 'donations'

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

// Get summary statistics
$query = "SELECT SUM(d.amount) as total FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE d.status = 'verified'" . $campaignCondition . $dateCondition;
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$totalRaised = $stmt->fetch()['total'] ?? 0;

$query = "SELECT COUNT(*) as total FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE d.status = 'verified'" . $campaignCondition . $dateCondition;
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$totalDonations = $stmt->fetch()['total'];

$query = "SELECT COUNT(*) as total FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE d.status = 'pending'" . $campaignCondition . $dateCondition;
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$pendingDonations = $stmt->fetch()['total'];

$query = "SELECT COUNT(DISTINCT d.donor_id) as total FROM donations d 
          LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id 
          WHERE d.donor_id IS NOT NULL" . $campaignCondition . $dateCondition;
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$totalDonors = $stmt->fetch()['total'];

// Get campaign details if specific campaign
$campaignDetails = null;
if ($campaignFilter !== 'all') {
    $stmt = $db->prepare("SELECT * FROM campaigns WHERE campaign_id = ?");
    $stmt->execute([$campaignFilter]);
    $campaignDetails = $stmt->fetch();
}

// Top campaigns
$query = "SELECT c.campaign_name, 
           COUNT(d.donation_id) as donation_count,
           SUM(d.amount) as total_raised,
           c.target_amount
    FROM campaigns c
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id AND d.status = 'verified'
    WHERE 1=1" . $campaignCondition . $dateCondition . "
    GROUP BY c.campaign_id ORDER BY total_raised DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$topCampaigns = $stmt->fetchAll();

// Top donors
$query = "SELECT u.full_name, u.email,
           COUNT(d.donation_id) as donation_count,
           SUM(d.amount) as total_donated
    FROM users u
    INNER JOIN donations d ON u.user_id = d.donor_id
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    WHERE d.status = 'verified'" . $campaignCondition . $dateCondition . "
    GROUP BY u.user_id
    ORDER BY total_donated DESC
    LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute(array_merge($campaignParams, $dateParams));
$topDonors = $stmt->fetchAll();

// Get detailed donations list if type is 'donations'
$donationsList = [];
if ($reportType === 'donations') {
    $query = "SELECT d.*, c.campaign_name, u.full_name as donor_name_full
        FROM donations d
        LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
        LEFT JOIN users u ON d.donor_id = u.user_id
        WHERE d.status = 'verified'" . $campaignCondition . $dateCondition . "
        ORDER BY d.donation_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute(array_merge($campaignParams, $dateParams));
    $donationsList = $stmt->fetchAll();
}

// Period label
$periodLabel = 'All Time';
if ($periodFilter !== 'all') {
    $periodLabels = [
        'today' => 'Today',
        'week' => 'Last 7 Days',
        'month' => 'Last 30 Days',
        'quarter' => 'Last 90 Days',
        'year' => 'Last Year',
        'custom' => 'Custom Range'
    ];
    $periodLabel = $periodLabels[$periodFilter] ?? 'All Time';
    if ($periodFilter === 'custom' && $startDate && $endDate) {
        $periodLabel .= ' (' . date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)) . ')';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KopuGive - Donation Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .container { max-width: 100%; }
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .report-container {
            background: white;
            max-width: 900px;
            margin: 20px auto;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .report-header {
            text-align: center;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .report-header h1 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .report-meta {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .stat-box h3 {
            font-size: 2rem;
            margin: 10px 0;
            font-weight: bold;
        }
        
        .stat-box p {
            margin: 0;
            opacity: 0.9;
        }
        
        .section-title {
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin: 30px 0 20px 0;
            font-weight: bold;
        }
        
        .table {
            margin-bottom: 30px;
        }
        
        .table thead {
            background: #667eea;
            color: white;
        }
        
        .footer-note {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 60px;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 20px;">
        <button onclick="window.print()" class="btn btn-primary btn-lg">
            <i class="fas fa-print me-2"></i>Print / Save as PDF
        </button>
        <a href="reports.php?campaign_id=<?= $campaignFilter ?>&period=<?= $periodFilter ?><?= $startDate ? '&start_date='.$startDate : '' ?><?= $endDate ? '&end_date='.$endDate : '' ?>" class="btn btn-secondary btn-lg">
            <i class="fas fa-arrow-left me-2"></i>Back to Reports
        </a>
    </div>

    <div class="report-container">
        <!-- Header -->
        <div class="report-header">
            <h1>📊 KOPUGIVE DONATION REPORT</h1>
            <p class="text-muted">MRSM Kota Putra Donation Management System</p>
        </div>
        
        <!-- Report Metadata -->
        <div class="report-meta">
            <div class="row">
                <div class="col-6">
                    <strong>Report Type:</strong> <?= $reportType === 'donations' ? 'Donation List Report' : ($campaignFilter !== 'all' ? 'Campaign Summary' : 'General Summary') ?>
                </div>
                <div class="col-6">
                    <strong>Period:</strong> <?= $periodLabel ?>
                </div>
                <div class="col-6 mt-2">
                    <strong>Generated:</strong> <?= date('F d, Y H:i:s') ?>
                </div>
                <div class="col-6 mt-2">
                    <strong>Generated By:</strong> <?= htmlspecialchars($_SESSION['user_name']) ?>
                </div>
                <?php if ($campaignDetails): ?>
                <div class="col-12 mt-2">
                    <strong>Campaign:</strong> <?= htmlspecialchars($campaignDetails['campaign_name']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($reportType === 'donations'): ?>
        <!-- Donation List View -->
        <h4 class="section-title">Donation Records</h4>
        
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Donor Name</th>
                    <th>Campaign</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($donationsList) > 0): ?>
                    <?php $no = 1; foreach ($donationsList as $donation): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($donation['donation_date'])) ?></td>
                            <td><?= htmlspecialchars($donation['donor_name_full'] ?? $donation['donor_name']) ?></td>
                            <td><?= htmlspecialchars($donation['campaign_name']) ?></td>
                            <td><?= formatCurrency($donation['amount']) ?></td>
                            <td><?= ucwords(str_replace('_', ' ', $donation['payment_method'])) ?></td>
                            <td><?= htmlspecialchars($donation['transaction_id'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light">
                        <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                        <td colspan="3"><strong><?= formatCurrency($totalRaised) ?></strong></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No donations found for selected criteria</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php else: ?>
        <!-- Summary Statistics -->
        <h4 class="section-title">Executive Summary</h4>
        
        <div class="row">
            <div class="col-md-6">
                <div class="stat-box">
                    <p>Total Amount Raised</p>
                    <h3><?= formatCurrency($totalRaised) ?></h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <p>Total Donors</p>
                    <h3><?= $totalDonors ?></h3>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Verified Donations</h6>
                        <h2 class="text-success"><?= $totalDonations ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Pending Donations</h6>
                        <h2 class="text-warning"><?= $pendingDonations ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Campaigns -->
        <?php if (count($topCampaigns) > 0): ?>
        <h4 class="section-title">Top Performing Campaigns</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Campaign Name</th>
                    <th>Donations</th>
                    <th>Amount Raised</th>
                    <th>Target</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($topCampaigns as $campaign): ?>
                    <?php $progress = calculatePercentage($campaign['total_raised'] ?? 0, $campaign['target_amount']); ?>
                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= htmlspecialchars($campaign['campaign_name']) ?></td>
                        <td><?= $campaign['donation_count'] ?></td>
                        <td><?= formatCurrency($campaign['total_raised'] ?? 0) ?></td>
                        <td><?= formatCurrency($campaign['target_amount']) ?></td>
                        <td><?= $progress ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <!-- Top Donors -->
        <?php if (count($topDonors) > 0): ?>
        <h4 class="section-title">Top Contributors</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Donor Name</th>
                    <th>Email</th>
                    <th>Donations</th>
                    <th>Total Contributed</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach ($topDonors as $donor): ?>
                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= htmlspecialchars($donor['full_name']) ?></td>
                        <td><?= htmlspecialchars($donor['email']) ?></td>
                        <td><?= $donor['donation_count'] ?></td>
                        <td><?= formatCurrency($donor['total_donated']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <?php endif; ?>
        
        <!-- Signatures -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    <strong>Prepared By</strong><br>
                    <small><?= htmlspecialchars($_SESSION['user_name']) ?></small>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <strong>Approved By</strong><br>
                    <small>Administrator</small>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer-note">
            <p><strong>KopuGive - MRSM Kota Putra Donation System</strong></p>
            <p>This is a computer-generated report. No signature is required.</p>
            <p>Report ID: RPT-<?= date('YmdHis') ?>-<?= substr(md5($campaignFilter.$periodFilter), 0, 8) ?></p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>

