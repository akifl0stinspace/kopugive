<?php
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$db = (new Database())->getConnection();
$error = '';

// Get campaign ID
$campaignId = $_GET['id'] ?? null;

if (!$campaignId) {
    setFlashMessage('danger', 'Campaign not found');
    redirect('campaigns.php');
    exit();
}

// Fetch campaign
$stmt = $db->prepare("SELECT * FROM campaigns WHERE campaign_id = ?");
$stmt->execute([$campaignId]);
$campaign = $stmt->fetch();

if (!$campaign) {
    setFlashMessage('danger', 'Campaign not found');
    redirect('campaigns.php');
    exit();
}

// Handle document deletion
if (isset($_GET['delete_doc'])) {
    $docId = intval($_GET['delete_doc']);
    $stmt = $db->prepare("SELECT document_path FROM campaign_documents WHERE document_id = ? AND campaign_id = ?");
    $stmt->execute([$docId, $campaignId]);
    $doc = $stmt->fetch();
    
    if ($doc) {
        $projectRoot = dirname(__DIR__) . '/';
        if (file_exists($projectRoot . $doc['document_path'])) {
            unlink($projectRoot . $doc['document_path']);
        }
        $stmt = $db->prepare("DELETE FROM campaign_documents WHERE document_id = ?");
        $stmt->execute([$docId]);
        setFlashMessage('success', 'Document deleted successfully');
        redirect('campaign_edit.php?id=' . $campaignId);
    }
}

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
            $bannerImage = $campaign['banner_image'];
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['banner_image'], 'uploads/campaigns/', ['jpg', 'jpeg', 'png']);
                if ($upload['success']) {
                    // Delete old banner if exists
                    $projectRoot = dirname(__DIR__) . '/';
                    if ($campaign['banner_image'] && file_exists($projectRoot . $campaign['banner_image'])) {
                        unlink($projectRoot . $campaign['banner_image']);
                    }
                    $bannerImage = $upload['path'];
                } else {
                    $error = $upload['message'];
                }
            }
            
            if (!$error) {
                $stmt = $db->prepare("UPDATE campaigns SET campaign_name = ?, description = ?, target_amount = ?, start_date = ?, end_date = ?, category = ?, status = ?, banner_image = ? WHERE campaign_id = ?");
                $stmt->execute([$campaignName, $description, $targetAmount, $startDate, $endDate, $category, $status, $bannerImage, $campaignId]);
                
                // Handle new document uploads
                if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
                    $documentDescriptions = $_POST['document_descriptions'] ?? [];
                    
                    for ($i = 0; $i < count($_FILES['documents']['name']); $i++) {
                        if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['documents']['name'][$i],
                                'type' => $_FILES['documents']['type'][$i],
                                'tmp_name' => $_FILES['documents']['tmp_name'][$i],
                                'error' => $_FILES['documents']['error'][$i],
                                'size' => $_FILES['documents']['size'][$i]
                            ];
                            
                            $upload = uploadFile($file, 'uploads/documents/', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);
                            
                            if ($upload['success']) {
                                $description = sanitize($documentDescriptions[$i] ?? '');
                                $fileType = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION));
                                
                                $stmt = $db->prepare("INSERT INTO campaign_documents (campaign_id, document_name, document_path, document_type, file_size, description, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $campaignId,
                                    $file['name'],
                                    $upload['path'],
                                    $fileType,
                                    $file['size'],
                                    $description,
                                    $_SESSION['user_id']
                                ]);
                            }
                        }
                    }
                }
                
                logActivity($db, $_SESSION['user_id'], 'Campaign updated', 'campaign', $campaignId);
                
                setFlashMessage('success', 'Campaign updated successfully');
                redirect('campaign_view.php?id=' . $campaignId);
            }
        } catch (Exception $e) {
            error_log("Campaign update error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

// Fetch existing documents
$stmt = $db->prepare("SELECT * FROM campaign_documents WHERE campaign_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$campaignId]);
$existingDocuments = $stmt->fetchAll();

// Get flash message
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Campaign - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/admin_styles.php'; ?>
</head>
<body>
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Campaign</h2>
            <div>
                <a href="campaign_view.php?id=<?= $campaignId ?>" class="btn btn-outline-info">
                    <i class="fas fa-eye me-2"></i>View Campaign
                </a>
                <a href="campaigns.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Campaigns
                </a>
            </div>
        </div>
        
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show">
                <?= $flashMessage['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
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
                                       value="<?= htmlspecialchars($_POST['campaign_name'] ?? $campaign['campaign_name']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($_POST['description'] ?? $campaign['description']) ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="target_amount" class="form-label">Target Amount (RM) *</label>
                                    <input type="number" class="form-control" id="target_amount" name="target_amount" 
                                           step="0.01" min="1" value="<?= htmlspecialchars($_POST['target_amount'] ?? $campaign['target_amount']) ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="education" <?= ($campaign['category'] === 'education') ? 'selected' : '' ?>>Education</option>
                                        <option value="infrastructure" <?= ($campaign['category'] === 'infrastructure') ? 'selected' : '' ?>>Infrastructure</option>
                                        <option value="welfare" <?= ($campaign['category'] === 'welfare') ? 'selected' : '' ?>>Welfare</option>
                                        <option value="emergency" <?= ($campaign['category'] === 'emergency') ? 'selected' : '' ?>>Emergency</option>
                                        <option value="other" <?= ($campaign['category'] === 'other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date *</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="<?= htmlspecialchars($_POST['start_date'] ?? $campaign['start_date']) ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date *</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="<?= htmlspecialchars($_POST['end_date'] ?? $campaign['end_date']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="banner_image" class="form-label">Banner Image</label>
                                <?php if ($campaign['banner_image']): ?>
                                    <div class="mb-2">
                                        <img src="../<?= htmlspecialchars($campaign['banner_image']) ?>" alt="Current Banner" class="img-thumbnail" style="max-height: 200px;">
                                        <p class="text-muted small mt-1">Current banner (upload new image to replace)</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/*">
                                <small class="text-muted">Max 5MB. JPG, PNG format</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft" <?= ($campaign['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                                    <option value="active" <?= ($campaign['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="completed" <?= ($campaign['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                                    <option value="closed" <?= ($campaign['status'] === 'closed') ? 'selected' : '' ?>>Closed</option>
                                </select>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Set to Active to make the campaign visible to donors
                                </small>
                            </div>
                            
                            <hr>
                            
                            <!-- Existing Documents -->
                            <?php if (count($existingDocuments) > 0): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-file-alt me-2"></i>Existing Documents
                                    </label>
                                    <div class="list-group">
                                        <?php foreach ($existingDocuments as $doc): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <i class="fas fa-file-<?= strtolower($doc['document_type']) === 'pdf' ? 'pdf' : 'alt' ?> me-2 text-primary"></i>
                                                            <?= htmlspecialchars($doc['document_name']) ?>
                                                        </h6>
                                                        <?php if ($doc['description']): ?>
                                                            <p class="mb-1 small text-muted"><?= htmlspecialchars($doc['description']) ?></p>
                                                        <?php endif; ?>
                                                        <small class="text-muted">
                                                            <?= $doc['document_type'] ?> • 
                                                            <?= number_format($doc['file_size'] / 1024, 2) ?> KB • 
                                                            Uploaded <?= formatDate($doc['uploaded_at'], 'd M Y') ?>
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <a href="../<?= htmlspecialchars($doc['document_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <a href="?id=<?= $campaignId ?>&delete_doc=<?= $doc['document_id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Are you sure you want to delete this document?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Add New Documents -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-file-upload me-2"></i>Add New Documents
                                </label>
                                <div id="documentsContainer">
                                    <div class="document-upload-item mb-2">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="file" class="form-control" name="documents[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" name="document_descriptions[]" placeholder="Description (e.g., Updated Budget)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addDocumentField()">
                                    <i class="fas fa-plus me-1"></i>Add Another Document
                                </button>
                                <small class="text-muted d-block mt-1">Upload additional supporting documents. Supported: PDF, DOC, DOCX, XLS, XLSX, Images</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Campaign
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Campaign Info</h5>
                        <ul class="small list-unstyled">
                            <li class="mb-2"><strong>Created:</strong> <?= formatDate($campaign['created_at'], 'd M Y H:i') ?></li>
                            <li class="mb-2"><strong>Current Amount:</strong> <?= formatCurrency($campaign['current_amount']) ?></li>
                            <li class="mb-2"><strong>Status:</strong> <span class="badge bg-primary"><?= ucfirst($campaign['status']) ?></span></li>
                        </ul>
                    </div>
                </div>
                
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-lightbulb me-2"></i>Tips</h5>
                        <ul class="small">
                            <li>Update campaign details carefully</li>
                            <li>Changing dates may affect donor expectations</li>
                            <li>Set status to 'Completed' when target is reached</li>
                            <li>Set status to 'Closed' to stop accepting donations</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addDocumentField() {
            const container = document.getElementById('documentsContainer');
            const newField = document.createElement('div');
            newField.className = 'document-upload-item mb-2';
            newField.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <input type="file" class="form-control" name="documents[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="document_descriptions[]" placeholder="Description">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.document-upload-item').remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newField);
        }
    </script>
</body>
</html>

