<?php
/**
 * Test Receipt Generation
 * KopuGive - MRSM Kota Putra Donation System
 * 
 * This script tests the receipt generation functionality
 */

require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/receipt_functions.php';

echo "<h1>Receipt Generation Test</h1>";
echo "<hr>";

// Get database connection
$db = (new Database())->getConnection();

// Find a recent donation to test with
$stmt = $db->query("
    SELECT d.*, c.campaign_name, c.campaign_description
    FROM donations d
    LEFT JOIN campaigns c ON d.campaign_id = c.campaign_id
    WHERE d.status = 'verified'
    ORDER BY d.donation_id DESC
    LIMIT 1
");
$donation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$donation) {
    echo "<p style='color: red;'>No verified donations found in database. Please create a test donation first.</p>";
    exit;
}

echo "<h2>Testing with Donation #" . $donation['donation_id'] . "</h2>";
echo "<p><strong>Donor:</strong> " . htmlspecialchars($donation['donor_name']) . "</p>";
echo "<p><strong>Amount:</strong> RM " . number_format($donation['amount'], 2) . "</p>";
echo "<p><strong>Campaign:</strong> " . htmlspecialchars($donation['campaign_name']) . "</p>";
echo "<hr>";

// Test receipt generation
echo "<h3>1. Testing Receipt Generation...</h3>";
$campaign = [
    'campaign_name' => $donation['campaign_name'],
    'campaign_description' => $donation['campaign_description']
];

$receiptResult = generateReceipt($donation, $campaign);

if ($receiptResult['success']) {
    echo "<p style='color: green;'>✓ Receipt generated successfully!</p>";
    echo "<p><strong>Path:</strong> " . htmlspecialchars($receiptResult['path']) . "</p>";
    echo "<p><a href='" . htmlspecialchars($receiptResult['path']) . "' target='_blank' class='btn'>View Receipt PDF</a></p>";
} else {
    echo "<p style='color: red;'>✗ Receipt generation failed: " . htmlspecialchars($receiptResult['message']) . "</p>";
}

echo "<hr>";

// Test email sending (only if email is configured)
echo "<h3>2. Testing Email Notification...</h3>";

if (empty($donation['donor_email'])) {
    echo "<p style='color: orange;'>⚠ No email address for this donation. Skipping email test.</p>";
} else {
    echo "<p><strong>Recipient:</strong> " . htmlspecialchars($donation['donor_email']) . "</p>";
    
    // Check if SMTP is configured
    if (SMTP_USERNAME === 'noreply@kopugive.com' || SMTP_PASSWORD === 'your_password_here') {
        echo "<p style='color: orange;'>⚠ SMTP not configured. Please update config.php with your email settings.</p>";
        echo "<p>To enable email notifications:</p>";
        echo "<ol>";
        echo "<li>Open <code>config/config.php</code></li>";
        echo "<li>Update SMTP_HOST, SMTP_USERNAME, SMTP_PASSWORD</li>";
        echo "<li>For Gmail: Use App Password (not regular password)</li>";
        echo "</ol>";
    } else {
        if ($receiptResult['success']) {
            $emailResult = sendReceiptEmail($donation, $receiptResult['filepath']);
            
            if ($emailResult['success']) {
                echo "<p style='color: green;'>✓ Email sent successfully!</p>";
            } else {
                echo "<p style='color: red;'>✗ Email failed: " . htmlspecialchars($emailResult['message']) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Cannot test email - receipt generation failed.</p>";
        }
    }
}

echo "<hr>";

// Test the complete process
echo "<h3>3. Testing Complete Process...</h3>";
$processResult = processReceiptForDonation($donation['donation_id'], $db);

if ($processResult['success']) {
    echo "<p style='color: green;'>✓ Complete process successful!</p>";
    echo "<p><strong>Receipt Path:</strong> " . htmlspecialchars($processResult['receipt_path']) . "</p>";
    echo "<p><strong>Email Sent:</strong> " . ($processResult['email_sent'] ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p style='color: red;'>✗ Process failed: " . htmlspecialchars($processResult['message']) . "</p>";
}

echo "<hr>";
echo "<h2>Test Summary</h2>";
echo "<ul>";
echo "<li>Receipt Generation: " . ($receiptResult['success'] ? '✓ PASS' : '✗ FAIL') . "</li>";
echo "<li>Email Notification: " . (empty($donation['donor_email']) ? 'SKIPPED' : (isset($emailResult) && $emailResult['success'] ? '✓ PASS' : '✗ FAIL')) . "</li>";
echo "<li>Complete Process: " . ($processResult['success'] ? '✓ PASS' : '✗ FAIL') . "</li>";
echo "</ul>";

?>
<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h1, h2, h3 {
        color: #800000;
    }
    .btn {
        display: inline-block;
        padding: 10px 20px;
        background: #800000;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px 0;
    }
    .btn:hover {
        background: #600000;
    }
    code {
        background: #e0e0e0;
        padding: 2px 5px;
        border-radius: 3px;
    }
</style>

