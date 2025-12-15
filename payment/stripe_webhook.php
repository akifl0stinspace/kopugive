<?php
/**
 * Stripe Webhook Handler
 * KopuGive - MRSM Kota Putra Donation System
 * 
 * Handles webhook events from Stripe for real-time payment updates
 * This ensures payment status is updated even if user closes browser
 */

require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

// Initialize Stripe
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Get the webhook secret
$endpoint_secret = STRIPE_WEBHOOK_SECRET;

// Get the raw POST body
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    // Verify webhook signature
    $event = \Stripe\Webhook::constructEvent(
        $payload, 
        $sig_header, 
        $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    error_log('Stripe Webhook: Invalid payload');
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    error_log('Stripe Webhook: Invalid signature');
    http_response_code(400);
    exit();
}

// Get database connection
$db = (new Database())->getConnection();

// Handle the event
switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        
        // Payment is successful and the subscription is created.
        if ($session->payment_status === 'paid') {
            $donationId = $session->metadata->donation_id ?? null;
            $paymentIntentId = $session->payment_intent;
            
            if ($donationId) {
                try {
                    // Update donation status
                    $stmt = $db->prepare("
                        UPDATE donations 
                        SET stripe_payment_intent_id = ?,
                            payment_status = 'paid',
                            status = 'verified',
                            transaction_id = ?,
                            verified_at = NOW(),
                            updated_at = NOW()
                        WHERE donation_id = ?
                    ");
                    $stmt->execute([
                        $paymentIntentId,
                        'STRIPE_' . substr($paymentIntentId, -12),
                        $donationId
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
                            logActivity($db, $donation['donor_id'], 'Donation verified via Stripe webhook', 'donation', $donationId);
                        }
                        
                        error_log("Stripe Webhook: Donation #{$donationId} verified successfully");
                    }
                } catch (Exception $e) {
                    error_log("Stripe Webhook Error: " . $e->getMessage());
                }
            }
        }
        break;
        
    case 'payment_intent.succeeded':
        $paymentIntent = $event->data->object;
        $donationId = $paymentIntent->metadata->donation_id ?? null;
        
        if ($donationId) {
            try {
                // Update payment status
                $stmt = $db->prepare("
                    UPDATE donations 
                    SET stripe_payment_intent_id = ?,
                        payment_status = 'succeeded',
                        updated_at = NOW()
                    WHERE donation_id = ?
                ");
                $stmt->execute([$paymentIntent->id, $donationId]);
                
                error_log("Stripe Webhook: Payment intent succeeded for donation #{$donationId}");
            } catch (Exception $e) {
                error_log("Stripe Webhook Error: " . $e->getMessage());
            }
        }
        break;
        
    case 'payment_intent.payment_failed':
        $paymentIntent = $event->data->object;
        $donationId = $paymentIntent->metadata->donation_id ?? null;
        
        if ($donationId) {
            try {
                // Update payment status
                $stmt = $db->prepare("
                    UPDATE donations 
                    SET payment_status = 'failed',
                        status = 'rejected',
                        updated_at = NOW()
                    WHERE donation_id = ?
                ");
                $stmt->execute([$donationId]);
                
                error_log("Stripe Webhook: Payment failed for donation #{$donationId}");
            } catch (Exception $e) {
                error_log("Stripe Webhook Error: " . $e->getMessage());
            }
        }
        break;
        
    case 'charge.refunded':
        $charge = $event->data->object;
        $paymentIntentId = $charge->payment_intent;
        
        if ($paymentIntentId) {
            try {
                // Update donation status
                $stmt = $db->prepare("
                    UPDATE donations 
                    SET payment_status = 'refunded',
                        status = 'rejected',
                        updated_at = NOW()
                    WHERE stripe_payment_intent_id = ?
                ");
                $stmt->execute([$paymentIntentId]);
                
                error_log("Stripe Webhook: Payment refunded for payment intent {$paymentIntentId}");
            } catch (Exception $e) {
                error_log("Stripe Webhook Error: " . $e->getMessage());
            }
        }
        break;
        
    default:
        // Unexpected event type
        error_log('Stripe Webhook: Received unknown event type ' . $event->type);
}

// Return a 200 response to acknowledge receipt of the event
http_response_code(200);
?>

