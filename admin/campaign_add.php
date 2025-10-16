<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campaignName = sanitize($_POST['campaign_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $targetAmount = floatval($_POST['target_amount'] ?? 0);
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $category = $_POST['category'] ?? 'other';
    $status = $_POST['status'] ?? 'draft';
    
    if (empty($campaignName) || empty($targetAmount) || empty($startDate) || empty($endDate)) {
        $error = 'Please fill in all required fields';
    } elseif ($targetAmount <= 0) {
        $error = 'Target amount must be greater than 0';
    } elseif (strtotime($endDate) < strtotime($startDate)) {
        $error = 'End date must be after start date';
    } else {
        try {
            // Handle banner upload
            $bannerImage = null;
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                // Save to project-root uploads directory (not under admin/)
                $upload = uploadFile($_FILES['banner_image'], '../uploads/campaigns/', ['jpg', 'jpeg', 'png']);
                if ($upload['success']) {
                    // Normalize stored path to be web-root relative
                    $bannerImage = str_replace('../', '', $upload['path']);
                } else {
                    $error = $upload['message'];
                }
            }
            
            if (!$error) {
                $stmt = $db->prepare("INSERT INTO campaigns (campaign_name, description, target_amount, start_date, end_date, category, status, banner_image, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$campaignName, $description, $targetAmount, $startDate, $endDate, $category, $status, $bannerImage, $_SESSION['user_id']]);
                
                $campaignId = $db->lastInsertId();
                logActivity($db, $_SESSION['user_id'], 'Campaign created', 'campaign', $campaignId);
                
                setFlashMessage('success', 'Campaign created successfully');
                redirect('campaigns.php');
            }
        } catch (Exception $e) {
            error_log("Campaign creation error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Campaign - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-plus-circle me-2"></i>Create New Campaign</h2>
            <a href="campaigns.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Campaigns
            </a>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="campaign_name" class="form-label">Campaign Name *</label>
                                <input type="text" class="form-control" id="campaign_name" name="campaign_name" 
                                       value="<?= htmlspecialchars($_POST['campaign_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="target_amount" class="form-label">Target Amount (RM) *</label>
                                    <input type="number" class="form-control" id="target_amount" name="target_amount" 
                                           step="0.01" min="1" value="<?= htmlspecialchars($_POST['target_amount'] ?? '') ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="education">Education</option>
                                        <option value="infrastructure">Infrastructure</option>
                                        <option value="welfare">Welfare</option>
                                        <option value="emergency">Emergency</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date *</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date *</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="banner_image" class="form-label">Banner Image</label>
                                <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/*">
                                <small class="text-muted">Max 5MB. JPG, PNG format</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Campaign
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Tips</h5>
                        <ul class="small">
                            <li>Write a clear and compelling campaign name</li>
                            <li>Provide detailed description of the campaign goals</li>
                            <li>Set realistic target amounts</li>
                            <li>Upload an attractive banner image</li>
                            <li>Save as Draft to review before making it Active</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

