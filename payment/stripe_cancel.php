<?php
/**
 * Stripe Payment Cancellation Handler
 * KopuGive - MRSM Kota Putra Donation System
 * 
 * Handles cancelled payment redirects from Stripe
 */

session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

$donationId = $_GET['donation_id'] ?? 0;

if ($donationId) {
    $db = (new Database())->getConnection();
    
    // Get donation details
    $stmt = $db->prepare("SELECT campaign_id, donor_id FROM donations WHERE donation_id = ?");
    $stmt->execute([$donationId]);
    $donation = $stmt->fetch();
    
    if ($donation) {
        // Update payment status
        $stmt = $db->prepare("
            UPDATE donations 
            SET payment_status = 'cancelled',
                updated_at = NOW()
            WHERE donation_id = ?
        ");
        $stmt->execute([$donationId]);
        
        // Log activity
        if ($donation['donor_id']) {
            logActivity($db, $donation['donor_id'], 'Payment cancelled', 'donation', $donationId);
        }
        
        setFlashMessage('info', 'Payment was cancelled. You can try again anytime.');
        redirect('../donor/campaign_view.php?id=' . $donation['campaign_id']);
    }
}

setFlashMessage('error', 'Invalid request');
redirect('../index.php');
?>

