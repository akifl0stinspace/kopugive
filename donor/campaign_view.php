<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

$db = (new Database())->getConnection();

$campaignId = $_GET['id'] ?? 0;

// Get campaign details
$stmt = $db->prepare("
    SELECT c.*, 
           u.full_name as created_by_name,
           COUNT(DISTINCT d.donation_id) as donation_count,
           COALESCE(SUM(CASE WHEN d.status = 'verified' THEN d.amount ELSE 0 END), 0) as total_raised
    FROM campaigns c
    LEFT JOIN users u ON c.created_by = u.user_id
    LEFT JOIN donations d ON c.campaign_id = d.campaign_id
    WHERE c.campaign_id = ?
    GROUP BY c.campaign_id
");
$stmt->execute([$campaignId]);
$campaign = $stmt->fetch();

if (!$campaign) {
    setFlashMessage('error', 'Campaign not found');
    redirect('campaigns.php');
}

// Get recent donations for this campaign
$stmt = $db->prepare("
    SELECT d.*, u.full_name as donor_name
    FROM donations d
    LEFT JOIN users u ON d.donor_id = u.user_id
    WHERE d.campaign_id = ? AND d.status = 'verified' AND d.is_anonymous = 0
    ORDER BY d.created_at DESC
    LIMIT 10
");
$stmt->execute([$campaignId]);
$recentDonors = $stmt->fetchAll();

// Get campaign updates
$stmt = $db->prepare("
    SELECT cu.*, u.full_name as posted_by_name
    FROM campaign_updates cu
    LEFT JOIN users u ON cu.posted_by = u.user_id
    WHERE cu.campaign_id = ?
    ORDER BY cu.posted_at DESC
    LIMIT 5
");
$stmt->execute([$campaignId]);
$updates = $stmt->fetchAll();

$percentage = calculatePercentage($campaign['total_raised'], $campaign['target_amount']);
$daysLeft = max(0, floor((strtotime($campaign['end_date']) - time()) / (60 * 60 * 24)));

// Handle donation submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate'])) {
    $amount = floatval($_POST['amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? 'online_banking';
    $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $message = sanitize($_POST['message'] ?? '');
    
    if ($amount <= 0) {
        $error = 'Please enter a valid amount';
    } else {
        try {
            // Handle receipt upload
            $receiptPath = null;
            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['receipt'], 'uploads/receipts/', ['jpg', 'jpeg', 'png', 'pdf']);
                if ($upload['success']) {
                    $receiptPath = $upload['path'];
                } else {
                    $error = $upload['message'];
                }
            }
            
            if (!$error) {
                $donorId = isLoggedIn() ? $_SESSION['user_id'] : null;
                $donorName = isLoggedIn() ? $_SESSION['full_name'] : ($_POST['donor_name'] ?? 'Anonymous');
                $donorEmail = isLoggedIn() ? $_SESSION['email'] : ($_POST['donor_email'] ?? '');
                $donorPhone = $_POST['donor_phone'] ?? '';
                
                $stmt = $db->prepare("
                    INSERT INTO donations 
                    (campaign_id, donor_id, donor_name, donor_email, donor_phone, amount, payment_method, 
                     receipt_path, donation_message, is_anonymous, status, payment_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
                ");
                $stmt->execute([
                    $campaignId, $donorId, $donorName, $donorEmail, $donorPhone,
                    $amount, $paymentMethod, $receiptPath, $message, $isAnonymous
                ]);
                
                $donationId = $db->lastInsertId();
                
                if ($donorId) {
                    logActivity($db, $donorId, 'Donation created', 'donation', $donationId);
                }
                
                // Redirect to Stripe checkout for online payments
                if (in_array($paymentMethod, ['online_banking', 'card', 'ewallet'])) {
                    redirect('../payment/stripe_checkout.php?donation_id=' . $donationId);
                } else {
                    // For cash/manual payments, keep old flow
                    setFlashMessage('success', 'Thank you for your donation! Your donation is pending verification.');
                    redirect('my_donations.php');
                }
            }
        } catch (Exception $e) {
            error_log("Donation error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($campaign['campaign_name']) ?> - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include '../includes/theme_styles.php'; ?>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="../index.php">
                <i class="fas fa-hand-holding-heart me-2"></i>KopuGive
            </a>
            <div class="ms-auto">
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn btn-outline-primary">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-outline-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Campaign Banner -->
    <?php if ($campaign['banner_image']): ?>
        <img src="../<?= htmlspecialchars($campaign['banner_image']) ?>" class="w-100 campaign-banner" alt="Campaign Banner">
    <?php else: ?>
        <div class="campaign-banner d-flex align-items-center justify-content-center text-white">
            <i class="fas fa-image fa-5x"></i>
        </div>
    <?php endif; ?>
    
    <div class="container my-5">
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <span class="badge bg-primary mb-2"><?= ucfirst($campaign['category']) ?></span>
                        <h2 class="fw-bold mb-3"><?= htmlspecialchars($campaign['campaign_name']) ?></h2>
                        
                        <!-- Progress -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <h4 class="text-success fw-bold"><?= formatCurrency($campaign['total_raised']) ?></h4>
                                <span class="text-muted">raised of <?= formatCurrency($campaign['target_amount']) ?> goal</span>
                            </div>
                            <div class="progress progress-large mb-2">
                                <div class="progress-bar bg-success" style="width: <?= $percentage ?>%">
                                    <?= $percentage ?>%
                                </div>
                            </div>
                            <div class="d-flex justify-content-between text-muted">
                                <span><i class="fas fa-users me-1"></i><?= $campaign['donation_count'] ?> donors</span>
                                <span><i class="fas fa-clock me-1"></i><?= $daysLeft ?> days left</span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Description -->
                        <h5 class="fw-bold mb-3">About this campaign</h5>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($campaign['description'])) ?></p>
                        
                        <!-- Campaign Details -->
                        <div class="mt-4">
                            <h6 class="fw-bold">Campaign Period</h6>
                            <p class="text-muted">
                                <i class="fas fa-calendar me-2"></i>
                                <?= formatDate($campaign['start_date'], 'd F Y') ?> - <?= formatDate($campaign['end_date'], 'd F Y') ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Donors -->
                <?php if (!empty($recentDonors)): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Recent Donors</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($recentDonors as $donor): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="ms-3">
                                        <div class="fw-bold"><?= htmlspecialchars($donor['donor_name'] ?? 'Anonymous') ?></div>
                                        <small class="text-muted">Donated <?= formatCurrency($donor['amount']) ?> • <?= timeAgo($donor['created_at']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Updates -->
                <?php if (!empty($updates)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Campaign Updates</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($updates as $update): ?>
                                <div class="mb-4">
                                    <h6 class="fw-bold"><?= htmlspecialchars($update['title']) ?></h6>
                                    <p class="text-muted mb-1"><?= htmlspecialchars($update['content']) ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i><?= timeAgo($update['posted_at']) ?>
                                    </small>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Donation Form -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="fas fa-hand-holding-heart me-2"></i>Make a Donation</h5>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Donation Amount (RM) *</label>
                                <input type="number" class="form-control form-control-lg" name="amount" 
                                       step="0.01" min="1" placeholder="0.00" required>
                            </div>
                            
                            <?php if (!isLoggedIn()): ?>
                                <div class="mb-3">
                                    <label class="form-label">Your Name *</label>
                                    <input type="text" class="form-control" name="donor_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="donor_email">
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="donor_phone" placeholder="0123456789">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method" id="payment_method">
                                    <option value="online_banking">Online Banking (FPX) - via Stripe</option>
                                    <option value="card">Credit/Debit Card - via Stripe</option>
                                    <option value="ewallet">E-Wallet (GrabPay) - via Stripe</option>
                                    <option value="cash">Cash (Manual Verification)</option>
                                </select>
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Secure payment powered by Stripe
                                </small>
                            </div>
                            
                            <div class="mb-3" id="receipt_upload_section">
                                <label class="form-label">Upload Receipt (Optional for Cash)</label>
                                <input type="file" class="form-control" name="receipt" accept="image/*,application/pdf">
                                <small class="text-muted">JPG, PNG, PDF (Max 5MB) - Only needed for cash donations</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Message (Optional)</label>
                                <textarea class="form-control" name="message" rows="3" 
                                          placeholder="Leave a supportive message..."></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_anonymous" name="is_anonymous">
                                <label class="form-check-label" for="is_anonymous">
                                    Make this donation anonymous
                                </label>
                            </div>
                            
                            <button type="submit" name="donate" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-heart me-2"></i>Donate Now
                            </button>
                            
                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Your donation will be verified by our MUAFAKAT committee before being added to the campaign total.
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide receipt upload based on payment method
        document.getElementById('payment_method')?.addEventListener('change', function() {
            const receiptSection = document.getElementById('receipt_upload_section');
            if (this.value === 'cash') {
                receiptSection.style.display = 'block';
            } else {
                receiptSection.style.display = 'none';
            }
        });
        
        // Hide receipt upload by default for online payments
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethod = document.getElementById('payment_method');
            if (paymentMethod && paymentMethod.value !== 'cash') {
                document.getElementById('receipt_upload_section').style.display = 'none';
            }
        });
    </script>
</body>
</html>

