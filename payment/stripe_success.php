<?php
/**
 * Stripe Payment Success Handler
 * KopuGive - MRSM Kota Putra Donation System
 * 
 * Handles successful payment redirects from Stripe
 */

session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

// Initialize Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$sessionId = $_GET['session_id'] ?? '';

if (!$sessionId) {
    setFlashMessage('error', 'Invalid session');
    redirect('../index.php');
}

$db = (new Database())->getConnection();

try {
    // Retrieve the Checkout Session from Stripe
    $checkout_session = \Stripe\Checkout\Session::retrieve($sessionId);

    if ($checkout_session->payment_status === 'paid') {
        $donationId = $checkout_session->metadata->donation_id;
        $paymentIntentId = $checkout_session->payment_intent;

        // Update donation record
        $stmt = $db->prepare("
            UPDATE donations 
            SET stripe_payment_intent_id = ?,
                payment_status = 'paid',
                status = 'verified',
                transaction_id = ?,
                verified_at = NOW(),
                updated_at = NOW()
            WHERE donation_id = ? AND stripe_checkout_session_id = ?
        ");
        $stmt->execute([
            $paymentIntentId,
            'STRIPE_' . substr($paymentIntentId, -12),
            $donationId,
            $sessionId
        ]);

        // Get donation details
        $stmt = $db->prepare("SELECT * FROM donations WHERE donation_id = ?");
        $stmt->execute([$donationId]);
        $donation = $stmt->fetch();

        if ($donation) {
            // Update campaign total
            $stmt = $db->prepare("
                UPDATE campaigns 
                SET current_amount = current_amount + ?
                WHERE campaign_id = ?
            ");
            $stmt->execute([$donation['amount'], $donation['campaign_id']]);

            // Log activity
            if ($donation['donor_id']) {
                logActivity($db, $donation['donor_id'], 'Donation payment completed via Stripe', 'donation', $donationId);
            }
        }

        setFlashMessage('success', 'Thank you! Your donation has been processed successfully.');
    } else {
        setFlashMessage('warning', 'Payment is being processed. Please check your donation status later.');
    }

} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe API Error: " . $e->getMessage());
    setFlashMessage('error', 'Unable to verify payment. Please contact support.');
} catch (Exception $e) {
    error_log("Success Handler Error: " . $e->getMessage());
    setFlashMessage('error', 'An error occurred. Please contact support.');
}

// Redirect based on login status
if (isLoggedIn()) {
    redirect('../donor/my_donations.php');
} else {
    redirect('../index.php');
}
?>

