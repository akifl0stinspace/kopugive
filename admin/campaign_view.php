<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();

// Get campaign ID
$campaignId = $_GET['id'] ?? null;

if (!$campaignId) {
    setFlashMessage('danger', 'Campaign not found');
    redirect('campaigns.php');
    exit();
}

// Handle campaign approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_campaign'])) {
        try {
            $stmt = $db->prepare("UPDATE campaigns SET status = 'active', approved_by = ?, approved_at = NOW(), rejection_reason = NULL WHERE campaign_id = ?");
            $stmt->execute([$_SESSION['user_id'], $campaignId]);
            
            logActivity($db, $_SESSION['user_id'], 'Campaign approved', 'campaign', $campaignId);
            setFlashMessage('success', 'Campaign approved and activated successfully!');
            redirect('campaign_view.php?id=' . $campaignId);
        } catch (Exception $e) {
            setFlashMessage('danger', 'Error approving campaign: ' . $e->getMessage());
        }
    } elseif (isset($_POST['reject_campaign'])) {
        $rejectionReason = sanitize($_POST['rejection_reason'] ?? '');
        if (empty($rejectionReason)) {
            setFlashMessage('danger', 'Please provide a rejection reason');
        } else {
            try {
                $stmt = $db->prepare("UPDATE campaigns SET status = 'rejected', rejection_reason = ?, approved_by = NULL, approved_at = NULL WHERE campaign_id = ?");
                $stmt->execute([$rejectionReason, $campaignId]);
                
                logActivity($db, $_SESSION['user_id'], 'Campaign rejected', 'campaign', $campaignId);
                setFlashMessage('warning', 'Campaign rejected. Creator will be notified.');
                redirect('campaign_view.php?id=' . $campaignId);
            } catch (Exception $e) {
                setFlashMessage('danger', 'Error rejecting campaign: ' . $e->getMessage());
            }
        }
    }
}

// Fetch campaign details
$stmt = $db->prepare("
    SELECT c.*, 
           u.full_name as created_by_name,
           approver.full_name as approved_by_name
    FROM campaigns c
    LEFT JOIN users u ON c.created_by = u.user_id
    LEFT JOIN users approver ON c.approved_by = approver.user_id
    WHERE c.campaign_id = ?
");
$stmt->execute([$campaignId]);
$campaign = $stmt->fetch();

if ($campaign) {
    // Calculate donation stats separately
    $stmt = $db->prepare("
        SELECT 
            COUNT(DISTINCT donation_id) as donation_count,
            COALESCE(SUM(CASE WHEN status = 'verified' THEN amount ELSE 0 END), 0) as total_raised
        FROM donations
        WHERE campaign_id = ?
    ");
    $stmt->execute([$campaignId]);
    $stats = $stmt->fetch();
    
    $campaign['donation_count'] = $stats['donation_count'];
    $campaign['total_raised'] = $stats['total_raised'];
    
    // Calculate days remaining
    $endDate = new DateTime($campaign['end_date']);
    $today = new DateTime();
    $interval = $today->diff($endDate);
    $campaign['days_remaining'] = $interval->invert ? -$interval->days : $interval->days;
}

if (!$campaign) {
    setFlashMessage('danger', 'Campaign not found');
    redirect('campaigns.php');
    exit();
}

// Fetch campaign donations
$stmt = $db->prepare("
    SELECT d.*, u.full_name as donor_name, u.email as donor_email
    FROM donations d
    LEFT JOIN users u ON d.donor_id = u.user_id
    WHERE d.campaign_id = ?
    ORDER BY d.created_at DESC
");
$stmt->execute([$campaignId]);
$donations = $stmt->fetchAll();

// Fetch campaign documents
$stmt = $db->prepare("
    SELECT cd.*, u.full_name as uploader_name
    FROM campaign_documents cd
    LEFT JOIN users u ON cd.uploaded_by = u.user_id
    WHERE cd.campaign_id = ?
    ORDER BY cd.uploaded_at DESC
");
$stmt->execute([$campaignId]);
$documents = $stmt->fetchAll();

$percentage = calculatePercentage($campaign['total_raised'], $campaign['target_amount']);
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
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-bullhorn me-2"></i>Campaign Details</h2>
            <div>
                <a href="campaign_edit.php?id=<?= $campaign['campaign_id'] ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Edit Campaign
                </a>
                <a href="campaigns.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Campaigns
                </a>
            </div>
        </div>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show" role="alert">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Campaign Overview -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <?php if ($campaign['banner_image']): ?>
                        <img src="../<?= htmlspecialchars($campaign['banner_image']) ?>" class="card-img-top" style="max-height: 400px; object-fit: cover;" alt="Campaign Banner">
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary mb-2"><?= ucfirst($campaign['category']) ?></span>
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
                            </div>
                            <?php if ($campaign['days_remaining'] !== null): ?>
                                <div class="text-end">
                                    <?php if ($campaign['days_remaining'] > 0): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i><?= $campaign['days_remaining'] ?> days left
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Ended</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="mb-3"><?= htmlspecialchars($campaign['campaign_name']) ?></h3>
                        
                        <div class="mb-4">
                            <h5 class="text-success"><?= formatCurrency($campaign['total_raised']) ?></h5>
                            <p class="text-muted mb-2">raised of <?= formatCurrency($campaign['target_amount']) ?> goal</p>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar bg-success" style="width: <?= $percentage ?>%">
                                    <?= $percentage ?>%
                                </div>
                            </div>
                            <p class="text-muted small"><?= $campaign['donation_count'] ?> donations</p>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Description</h5>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($campaign['description'])) ?></p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Start Date:</strong> <?= formatDate($campaign['start_date'], 'd M Y') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>End Date:</strong> <?= formatDate($campaign['end_date'], 'd M Y') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Created By:</strong> <?= htmlspecialchars($campaign['created_by_name']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Created At:</strong> <?= formatDate($campaign['created_at'], 'd M Y H:i') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Quick Stats -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Campaign Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Total Donations</small>
                            <h4><?= $campaign['donation_count'] ?></h4>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Total Raised</small>
                            <h4 class="text-success"><?= formatCurrency($campaign['total_raised']) ?></h4>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Target Amount</small>
                            <h4><?= formatCurrency($campaign['target_amount']) ?></h4>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Completion</small>
                            <h4><?= $percentage ?>%</h4>
                        </div>
                        <div>
                            <small class="text-muted">Remaining</small>
                            <h4><?= formatCurrency($campaign['target_amount'] - $campaign['total_raised']) ?></h4>
                        </div>
                    </div>
                </div>
                
                <!-- Campaign Approval -->
                <?php if ($campaign['status'] === 'pending_approval'): ?>
                <div class="card mb-3 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Pending Approval</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">This campaign is waiting for admin approval. Review the documents and details before approving.</p>
                        
                        <form method="POST" class="mb-2">
                            <button type="submit" name="approve_campaign" class="btn btn-success w-100 mb-2" onclick="return confirm('Approve this campaign and make it active?')">
                                <i class="fas fa-check-circle me-2"></i>Approve Campaign
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times-circle me-2"></i>Reject Campaign
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($campaign['status'] === 'rejected'): ?>
                <div class="card mb-3 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fas fa-times-circle me-2"></i>Rejected</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Rejection Reason:</strong></p>
                        <p class="text-muted"><?= htmlspecialchars($campaign['rejection_reason'] ?? 'No reason provided') ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($campaign['status'] === 'active' && $campaign['approved_by']): ?>
                <div class="card mb-3 border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Approved</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Approved by:</strong> <?= htmlspecialchars($campaign['approved_by_name']) ?></p>
                        <p class="mb-0"><strong>Approved at:</strong> <?= formatDate($campaign['approved_at'], 'd M Y H:i') ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="campaigns.php">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="campaign_id" value="<?= $campaign['campaign_id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Change Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="draft" <?= $campaign['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="pending_approval" <?= $campaign['status'] === 'pending_approval' ? 'selected' : '' ?>>Pending Approval</option>
                                    <option value="active" <?= $campaign['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="completed" <?= $campaign['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="closed" <?= $campaign['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                    <option value="rejected" <?= $campaign['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Supporting Documents -->
        <?php if (count($documents) > 0): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Supporting Documents</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    These documents have been uploaded by the campaign creator for verification and transparency.
                </p>
                <div class="row">
                    <?php foreach ($documents as $doc): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-file-<?= strtolower($doc['document_type']) === 'pdf' ? 'pdf text-danger' : 'alt text-primary' ?> fa-3x"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1"><?= htmlspecialchars($doc['document_name']) ?></h6>
                                            <?php if ($doc['description']): ?>
                                                <p class="text-muted small mb-2"><?= htmlspecialchars($doc['description']) ?></p>
                                            <?php endif; ?>
                                            <div class="small text-muted mb-2">
                                                <span class="badge bg-secondary"><?= $doc['document_type'] ?></span>
                                                <span class="ms-2"><?= number_format($doc['file_size'] / 1024, 2) ?> KB</span>
                                            </div>
                                            <div class="small text-muted mb-2">
                                                Uploaded by: <strong><?= htmlspecialchars($doc['uploader_name'] ?? 'Unknown') ?></strong><br>
                                                Date: <?= formatDate($doc['uploaded_at'], 'd M Y H:i') ?>
                                            </div>
                                            <a href="../<?= htmlspecialchars($doc['document_path']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Donations List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Campaign Donations</h5>
            </div>
            <div class="card-body">
                <?php if (empty($donations)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No donations yet for this campaign</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Donor</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rowNumber = 1;
                                foreach ($donations as $donation): 
                                ?>
                                    <tr>
                                        <td>#<?= $rowNumber++ ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($donation['donor_name'] ?? $donation['donor_name'] ?? 'Anonymous') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($donation['donor_email'] ?? '') ?></small>
                                        </td>
                                        <td><strong class="text-success"><?= formatCurrency($donation['amount']) ?></strong></td>
                                        <td><small><?= ucfirst(str_replace('_', ' ', $donation['payment_method'])) ?></small></td>
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
                                        </td>
                                        <td><?= formatDate($donation['donation_date'], 'd M Y H:i') ?></td>
                                        <td>
                                            <?php if ($donation['receipt_path']): ?>
                                                <a href="../<?= htmlspecialchars($donation['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-image"></i> View
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No receipt</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Campaign</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> The campaign creator will be notified of this rejection.
                        </div>
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Rejection Reason *</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Please provide a clear reason for rejection (e.g., missing documents, insufficient information, etc.)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="reject_campaign" class="btn btn-danger">
                            <i class="fas fa-times-circle me-2"></i>Reject Campaign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

