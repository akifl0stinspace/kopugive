<?php
/**
 * Payment Gateway Integration Placeholder
 * KopuGive - MRSM Kota Putra Donation System
 * 
 * This is a placeholder for FPX/Payment Gateway integration
 * In production, integrate with actual payment providers like:
 * - FPX (Financial Process Exchange)
 * - Stripe
 * - PayPal
 * - iPay88
 * - eGHL
 */

session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if donation ID is provided
$donationId = $_GET['donation_id'] ?? 0;

if (!$donationId) {
    setFlashMessage('error', 'Invalid donation request');
    redirect('../index.php');
}

$db = (new Database())->getConnection();

// Get donation details
$stmt = $db->prepare("
    SELECT d.*, c.campaign_name
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    WHERE d.donation_id = ?
");
$stmt->execute([$donationId]);
$donation = $stmt->fetch();

if (!$donation) {
    setFlashMessage('error', 'Donation not found');
    redirect('../index.php');
}

// Simulate payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    // In production, this would:
    // 1. Send request to payment gateway API
    // 2. Generate payment form/redirect to payment page
    // 3. Handle callback from payment gateway
    // 4. Verify payment signature
    // 5. Update donation status
    
    // For now, simulate successful payment
    $transactionId = 'TXN' . strtoupper(generateRandomString(12));
    
    $stmt = $db->prepare("
        UPDATE donations 
        SET transaction_id = ?, status = 'pending'
        WHERE donation_id = ?
    ");
    $stmt->execute([$transactionId, $donationId]);
    
    setFlashMessage('success', 'Payment processed successfully! Your donation is pending verification.');
    redirect('../donor/my_donations.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Processing - KopuGive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .payment-card {
            max-width: 600px;
            margin: 0 auto;
        }
        .bank-logo {
            width: 80px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="../index.php">
                <i class="fas fa-hand-holding-heart me-2"></i>KopuGive
            </a>
        </div>
    </nav>
    
    <div class="container my-5">
        <div class="payment-card">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Gateway</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Payment Notice -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Demo Mode:</strong> This is a payment gateway placeholder. 
                        In production, this would integrate with FPX, Stripe, or other payment providers.
                    </div>
                    
                    <!-- Donation Summary -->
                    <h6 class="fw-bold mb-3">Donation Summary</h6>
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Campaign:</td>
                            <td class="fw-bold"><?= htmlspecialchars($donation['campaign_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Amount:</td>
                            <td class="fw-bold text-success"><?= formatCurrency($donation['amount']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Payment Method:</td>
                            <td><?= ucfirst(str_replace('_', ' ', $donation['payment_method'])) ?></td>
                        </tr>
                    </table>
                    
                    <hr>
                    
                    <!-- Simulated Bank Selection -->
                    <h6 class="fw-bold mb-3">Select Your Bank (FPX Demo)</h6>
                    <div class="row g-2 mb-4">
                        <div class="col-3">
                            <div class="bank-logo bg-light">
                                <small>Maybank</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bank-logo bg-light">
                                <small>CIMB</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bank-logo bg-light">
                                <small>PBB</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bank-logo bg-light">
                                <small>RHB</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Form -->
                    <form method="POST">
                        <div class="d-grid gap-2">
                            <button type="submit" name="process_payment" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>Proceed to Payment
                            </button>
                            <a href="../donor/campaign_view.php?id=<?= $donation['campaign_id'] ?>" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                    
                    <!-- Security Notice -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Secure Payment:</strong> All transactions are encrypted and secure. 
                            Your payment information is protected with industry-standard SSL encryption.
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Integration Notes Card -->
            <div class="card border-warning mt-3">
                <div class="card-header bg-warning bg-opacity-10">
                    <h6 class="mb-0"><i class="fas fa-code me-2"></i>Developer Notes</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>To integrate actual payment gateway:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Register with FPX/Stripe/PayPal merchant account</li>
                            <li>Obtain API credentials (Merchant ID, Exchange ID, API Key)</li>
                            <li>Install payment SDK/library</li>
                            <li>Implement payment request and callback handlers</li>
                            <li>Set up webhook endpoints for payment notifications</li>
                            <li>Test in sandbox mode before going live</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

