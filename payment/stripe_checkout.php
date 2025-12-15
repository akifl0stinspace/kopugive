<?php
/**
 * Stripe Checkout Session Creator
 * KopuGive - MRSM Kota Putra Donation System
 * 
 * Creates a Stripe Checkout session for secure payment processing
 */

session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

// Initialize Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Check if donation ID is provided
$donationId = $_GET['donation_id'] ?? 0;

if (!$donationId) {
    setFlashMessage('error', 'Invalid donation request');
    redirect('../index.php');
}

$db = (new Database())->getConnection();

// Get donation details
$stmt = $db->prepare("
    SELECT d.*, c.campaign_name, c.campaign_id
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    WHERE d.donation_id = ? AND d.status = 'pending'
");
$stmt->execute([$donationId]);
$donation = $stmt->fetch();

if (!$donation) {
    setFlashMessage('error', 'Donation not found or already processed');
    redirect('../index.php');
}

try {
    // Create Stripe Checkout Session
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card', 'fpx', 'grabpay'], // Malaysian payment methods
        'line_items' => [[
            'price_data' => [
                'currency' => STRIPE_CURRENCY,
                'product_data' => [
                    'name' => $donation['campaign_name'],
                    'description' => 'Donation to ' . $donation['campaign_name'],
                    'images' => [APP_URL . '/assets/logo.png'], // Optional: Add your logo
                ],
                'unit_amount' => (int)($donation['amount'] * 100), // Convert to cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => APP_URL . '/payment/stripe_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => APP_URL . '/payment/stripe_cancel.php?donation_id=' . $donationId,
        'customer_email' => $donation['donor_email'] ?: null,
        'metadata' => [
            'donation_id' => $donationId,
            'campaign_id' => $donation['campaign_id'],
            'donor_name' => $donation['donor_name'],
        ],
        'payment_intent_data' => [
            'metadata' => [
                'donation_id' => $donationId,
                'campaign_id' => $donation['campaign_id'],
            ],
        ],
    ]);

    // Save checkout session ID to database
    $stmt = $db->prepare("
        UPDATE donations 
        SET stripe_checkout_session_id = ?, 
            payment_status = 'checkout_created',
            updated_at = NOW()
        WHERE donation_id = ?
    ");
    $stmt->execute([$checkout_session->id, $donationId]);

    // Log activity
    if ($donation['donor_id']) {
        logActivity($db, $donation['donor_id'], 'Stripe checkout initiated', 'donation', $donationId);
    }

    // Redirect to Stripe Checkout
    header('Location: ' . $checkout_session->url);
    exit;

} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe API Error: " . $e->getMessage());
    setFlashMessage('error', 'Payment system error. Please try again later.');
    redirect('../donor/campaign_view.php?id=' . $donation['campaign_id']);
} catch (Exception $e) {
    error_log("Checkout Error: " . $e->getMessage());
    setFlashMessage('error', 'An error occurred. Please try again.');
    redirect('../donor/campaign_view.php?id=' . $donation['campaign_id']);
}
?>

